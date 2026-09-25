<?php

namespace Tests\Unit\Content;

use App\Services\ContentModerationService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Moderatsiya xizmatining sozlamaga bog'liqligi.
 *
 * Mavjud bo'lish sababi: 2026-08-04 dan 2026-09-26 gacha sayt hech narsa
 * nashr qilmadi. Sabab kontentda emas edi — Groq `llama-3.3-70b-versatile`
 * modelini o'chirib tashladi, xizmat esa uni KODDA qattiq yozib qo'ygan edi
 * (`config('services.groq.model')` kaliti mavjud va `.env` da to'g'ri
 * qiymat ham bor edi, lekin hech kim uni o'qimasdi). Har chaqiruv
 * `model_not_found` qaytardi, xizmat esa uni "moderatsiya bo'lmadi, 50 ball"
 * deb tarjima qildi. 50 < 75, demak har draft `pending` bo'lib qoldi va
 * PublishGate hammasini `moderation_pending` bilan rad etdi. Ya'ni bitta
 * o'chirilgan model butun nashr quvurini yetti hafta muzlatdi.
 *
 * Shuning uchun bu yerda ikki narsa qulflanadi: model SOZLAMADAN olinadi va
 * sozlama nosozligi kontent hukmi bilan ARALASHTIRILMAYDI.
 */
class ContentModerationServiceTest extends TestCase
{
    /** Darvozadan o'tadigan, moderatsiya AI chaqiruviga yetib boradigan matn. */
    private function longContent(): string
    {
        return str_repeat('Laravel konteynerlashtirish bosqichlari haqida batafsil izoh. ', 400) . 'Tugadi.';
    }

    public function test_sozlamadagi_model_ishlatiladi(): void
    {
        config(['services.groq.api_key' => 'test-key', 'services.groq.model' => 'openai/gpt-oss-120b']);

        Http::preventStrayRequests();
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [['message' => ['content' => '{"passed":true,"score":90,"flags":[],"recommendations":[],"reason":"ok"}']]],
            ]),
        ]);

        (new ContentModerationService())->moderateContent('Sarlavha', $this->longContent(), 'Qisqa izoh');

        Http::assertSent(fn ($request) => $request['model'] === 'openai/gpt-oss-120b');
    }

    public function test_ochirilgan_model_sozlama_nosozligi_deb_belgilanadi(): void
    {
        config(['services.groq.api_key' => 'test-key', 'services.groq.model' => 'llama-3.3-70b-versatile']);

        Http::preventStrayRequests();
        Http::fake([
            'api.groq.com/*' => Http::response([
                'error' => [
                    'message' => 'The model `llama-3.3-70b-versatile` does not exist or you do not have access to it.',
                    'code' => 'model_not_found',
                ],
            ], 404),
        ]);

        $result = (new ContentModerationService())->moderateContent('Sarlavha', $this->longContent(), 'Qisqa izoh');

        // `ai_check_failed` o'zi yetarli emas edi: u "matn shubhali" degandek
        // o'qilardi. Alohida kalit bo'lmasa, operator 15 ta `pending` draftni
        // ko'rib "AI ularni yoqtirmadi" deb xulosa qiladi — aslida AI umuman
        // ishga tushmagan.
        $this->assertContains('moderation_unavailable', $result['flags']);
        $this->assertFalse($result['passed']);
    }

    public function test_kalit_yoq_bolsa_ham_sozlama_nosozligi(): void
    {
        config(['services.groq.api_key' => null]);

        Http::preventStrayRequests();

        $result = (new ContentModerationService())->moderateContent('Sarlavha', $this->longContent(), 'Qisqa izoh');

        $this->assertContains('moderation_unavailable', $result['flags']);
    }

    public function test_qisqa_matn_AI_chaqirmasdan_rad_etiladi(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        // Arzon, deterministik tekshiruv AI dan OLDIN ishlaydi — shuning uchun
        // bitta ham HTTP so'rov ketmasligi kerak.
        Http::preventStrayRequests();

        $result = (new ContentModerationService())->moderateContent('Sarlavha', 'Juda qisqa matn.', 'Izoh');

        $this->assertSame(['too_short'], $result['flags']);
        Http::assertNothingSent();
    }
}
