<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ContentModerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Moderator ISHGA TUSHMAGANI uchun `pending` bo'lib qolgan draftlarni
 * qayta o'tkazadi.
 *
 * Mavjud bo'lish sababi: Groq `llama-3.3-70b-versatile` modelini o'chirdi,
 * `ContentModerationService` esa uni koddan qattiq o'qirdi. Har chaqiruv
 * `model_not_found` qaytardi va xizmat uni "50 ball, qo'lda ko'rilsin" deb
 * yozdi. 50 < 75 — demak HAR draft `pending` bo'ldi. Sayt 2026-08-04 dan
 * 2026-09-26 gacha hech narsa nashr qilmadi.
 *
 * Modelni tuzatish o'zi yetarli emas: `moderation_status` bir marta yozilgan
 * va shu yerda qolgan. Yangi draftlar to'g'ri baholanadi, lekin orqada qolgan
 * 15 tasi (ular orasida 4287 va 3722 so'zli, darvozadan to'liq o'tadigan
 * tutoriallar) abadiy `pending` bo'lib turardi. Shu buyruq ularni qutqaradi.
 *
 * QASDDAN CHEKLANGAN: faqat `moderation_unavailable` / `ai_check_failed`
 * bayrog'i bor draftlar qayta ko'riladi. Moderator ISHLAB, past ball
 * bergan draftlar tegilmaydi — ularni qayta so'rash darvozani bekor qilish
 * bo'lardi ("yoqmaguncha qayta urin").
 */
class RemoderateCommand extends Command
{
    protected $signature = 'content:remoderate
                            {--limit=50 : Ko\'pi bilan shuncha draft ko\'riladi}
                            {--dry-run : Hech narsa yozilmaydi, faqat hisobot}';

    protected $description = 'Moderator ishlamagani uchun pending qolgan draftlarni qayta o\'tkazadi';

    /**
     * Moderator ishga TUSHMAGANINI ko'rsatadigan bayroqlar.
     *
     * `ai_check_failed` — eski (2026-09-26 gacha yozilgan) yozuvlar uchun:
     * o'sha paytda sozlama nosozligi va kontent hukmi bitta bayroq ostida
     * edi, shuning uchun tarixiy qatorlar faqat shu nom bilan belgilangan.
     */
    private const UNAVAILABLE_FLAGS = ['moderation_unavailable', 'ai_check_failed'];

    public function handle(ContentModerationService $moderation): int
    {
        $candidates = Post::where('status', 'draft')
            ->where('moderation_status', 'pending')
            ->latest('created_at')
            ->limit((int) $this->option('limit'))
            ->get()
            ->filter(fn (Post $post) => $this->wasModeratorUnavailable($post));

        if ($candidates->isEmpty()) {
            $this->info('Moderator ishlamagani uchun qolgan draft topilmadi.');

            return self::SUCCESS;
        }

        $this->info("Qayta ko'rilayotgan draftlar: {$candidates->count()}");

        $approved = 0;
        $rejected = 0;
        $stillUnavailable = 0;

        foreach ($candidates as $post) {
            $result = $moderation->moderateContent(
                (string) $post->title,
                (string) $post->content,
                (string) $post->excerpt
            );

            if ($this->flagsSay($result['flags'] ?? [])) {
                $stillUnavailable++;
                $this->warn("  ⚠ {$post->id}: moderator hamon ishlamayapti — " . ($result['reason'] ?? ''));
                continue;
            }

            $status = ($result['passed'] ?? false) && ($result['score'] ?? 0) >= self::approvalScore()
                ? 'approved'
                : 'pending';

            $status === 'approved' ? $approved++ : $rejected++;

            $this->line(sprintf(
                '  %s %d: %d ball — %s',
                $status === 'approved' ? '✓' : '·',
                $post->id,
                $result['score'] ?? 0,
                substr((string) $post->title, 0, 50)
            ));

            if ($this->option('dry-run')) {
                continue;
            }

            $post->update([
                'moderation_status' => $status,
                'moderated_at' => $status === 'approved' ? now() : null,
                'ai_moderation_check' => $result,
                'moderation_notes' => 'Re-moderated by content:remoderate after the moderation model was fixed',
            ]);
        }

        $this->newLine();
        $this->info("Tasdiqlandi: {$approved} · Kutishda qoldi: {$rejected} · Moderator ishlamadi: {$stillUnavailable}");

        // Bitta ham chaqiruv sozlama nosozligi bilan qaytsa, bu MUVAFFAQIYAT
        // emas. Aks holda "0 ta tuzatildi" muvaffaqiyat deb ko'rinadi va
        // sozlama hamon buzuqligi jimgina yashirinadi — aynan shu jimlik
        // butun uzilishga sabab bo'lgan edi.
        if ($stillUnavailable > 0) {
            Log::error('content:remoderate: moderation service still unavailable', [
                'affected_drafts' => $stillUnavailable,
                'model' => config('services.groq.model'),
            ]);

            $this->error(
                "Moderatsiya xizmati hamon ishlamayapti (model: " . config('services.groq.model') . "). "
                . "Avval sozlamani tuzating, keyin bu buyruqni qayta ishga tushiring."
            );

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Shu draft moderator ISHLAMAGANI uchun kutishda turganmi?
     *
     * Yozuvi umuman yo'q draftlar ham shu toifaga kiradi: ular moderatsiyadan
     * o'tgani haqida hech qanday dalil yo'q, ya'ni hukm chiqarilmagan.
     */
    private function wasModeratorUnavailable(Post $post): bool
    {
        $check = $post->ai_moderation_check;

        if (empty($check)) {
            return true;
        }

        return $this->flagsSay($check['flags'] ?? []);
    }

    /** @param array<int, string>|mixed $flags */
    private function flagsSay(mixed $flags): bool
    {
        return is_array($flags) && array_intersect(self::UNAVAILABLE_FLAGS, $flags) !== [];
    }

    private static function approvalScore(): int
    {
        // BotPostController bilan bir xil chegara: qayta o'tkazish YANGI
        // qoida joriy qilmasligi kerak, aks holda bitta draft qaysi yo'ldan
        // kelganiga qarab turlicha baholanadi.
        return 75;
    }
}
