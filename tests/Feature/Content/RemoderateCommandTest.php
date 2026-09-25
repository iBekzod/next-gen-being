<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\MakesGateContent;
use Tests\TestCase;

/**
 * Moderator tuzatilgandan keyin orqada qolgan draftlarni qutqarish.
 *
 * Mavjud bo'lish sababi: moderatsiya modeli o'chirilganda 15 ta draft
 * `pending` bo'lib qoldi. Modelni tuzatish ularni O'ZI qutqarmaydi —
 * `moderation_status` bir marta yozilib, o'sha yerda qoladi va PublishGate
 * ularni abadiy rad etadi. Ya'ni sozlama tuzatilgandan keyin ham sayt jim
 * turaverardi. Shu buyruq yo'q bo'lsa, tuzatish yarim tuzatish bo'ladi.
 */
class RemoderateCommandTest extends TestCase
{
    use RefreshDatabase;
    use MakesGateContent;

    private function fakeApproval(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [['message' => ['content' => '{"passed":true,"score":92,"flags":[],"recommendations":[],"reason":"Yaxshi"}']]],
            ]),
        ]);
    }

    public function test_moderator_ishlamaganda_qolgan_draft_qayta_otkaziladi(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        $this->fakeApproval();

        $post = Post::factory()->create([
            'status' => 'draft',
            'moderation_status' => 'pending',
            'content' => $this->cleanContent(),
            'ai_moderation_check' => ['passed' => false, 'score' => 50, 'flags' => ['moderation_unavailable']],
        ]);

        $this->artisan('content:remoderate')->assertExitCode(0);

        $post->refresh();
        $this->assertSame('approved', $post->moderation_status);
        $this->assertNotNull($post->moderated_at);
    }

    public function test_haqiqiy_sifat_hukmi_tegilmaydi(): void
    {
        config(['services.groq.api_key' => 'test-key']);
        $this->fakeApproval();

        // Bu draft moderatordan O'TDI, lekin past ball oldi — bu kontent
        // hukmi. Uni qayta o'tkazish "yoqmaguncha qayta so'rash" bo'lardi,
        // ya'ni darvozani bekor qilish. Shuning uchun faqat moderator ISHGA
        // TUSHMAGAN draftlar qayta ko'riladi.
        $post = Post::factory()->create([
            'status' => 'draft',
            'moderation_status' => 'pending',
            'content' => $this->cleanContent(),
            'ai_moderation_check' => ['passed' => false, 'score' => 40, 'flags' => ['low_quality']],
        ]);

        $this->artisan('content:remoderate')->assertExitCode(0);

        $this->assertSame('pending', $post->refresh()->moderation_status);
        Http::assertNothingSent();
    }

    public function test_moderator_hali_ishlamasa_holat_ozgarmaydi(): void
    {
        config(['services.groq.api_key' => 'test-key']);

        Http::preventStrayRequests();
        Http::fake([
            'api.groq.com/*' => Http::response(['error' => ['code' => 'model_not_found']], 404),
        ]);

        $post = Post::factory()->create([
            'status' => 'draft',
            'moderation_status' => 'pending',
            'content' => $this->cleanContent(),
            'ai_moderation_check' => ['passed' => false, 'score' => 50, 'flags' => ['moderation_unavailable']],
        ]);

        // Muhim: buyruq muvaffaqiyatsiz TUGAYDI. Aks holda "0 ta tuzatildi"
        // muvaffaqiyat deb ko'rinadi va sozlama hamon buzuqligi yashirinadi.
        $this->artisan('content:remoderate')->assertExitCode(1);

        $this->assertSame('pending', $post->refresh()->moderation_status);
    }
}
