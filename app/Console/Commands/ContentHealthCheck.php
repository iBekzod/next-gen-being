<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Content\PublishGate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Kontent quvurining kunlik salomatlik tekshiruvi.
 *
 * Mavjud bo'lish sababi: 2026-08-04 dan beri hech narsa nashr qilinmadi
 * va hech kim xabar topmadi (spec §1.5). Bu buyruq shu jimlikni ushlaydi.
 *
 * MUHIM: bo'sh zaxira XOM draft soni bilan emas, DARVOZADAN O'TADIGAN
 * draftlar soni bilan o'lchanadi. Production'da 255 ta draft va 0 ta
 * nashrga yaroqli post bor edi — xom sanoq butun uzilish davomida
 * "sog'lom" deb ko'rsatgan bo'lardi (spec §5).
 */
class ContentHealthCheck extends Command
{
    protected $signature = 'content:health-check {--dry-run : Faqat hisobot, xat yuborilmaydi}';
    protected $description = 'Kontent quvuri sog\'ligini tekshiradi va jimlikni aniqlaydi';

    private const MIN_PUBLISHABLE_BACKLOG = 3;

    public function __construct(private readonly PublishGate $gate)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $problems = [];

        $lastPost = Post::where('status', 'published')->whereNull('series_title')->max('published_at');
        if (! $lastPost || Carbon::parse($lastPost)->lt(now()->subDays(PublishGate::POST_INTERVAL_DAYS + 2))) {
            $problems['stale_posts'] = 'Oxirgi post: ' . ($lastPost ?: 'hech qachon');
        }

        $lastTutorial = Post::where('status', 'published')->whereNotNull('series_title')->max('published_at');
        if (! $lastTutorial || Carbon::parse($lastTutorial)->lt(now()->subDays(PublishGate::TUTORIAL_INTERVAL_DAYS + 3))) {
            $problems['stale_tutorials'] = 'Oxirgi tutorial: ' . ($lastTutorial ?: 'hech qachon');
        }

        $publishable = 0;
        $pendingOnly = 0;

        Post::where('status', 'draft')->select('id', 'content', 'moderation_status')
            ->chunk(200, function ($drafts) use (&$publishable, &$pendingOnly) {
                foreach ($drafts as $draft) {
                    $failures = $this->gate->failures($draft);

                    if ($failures === []) {
                        $publishable++;
                    } elseif ($failures === ['moderation_pending']) {
                        $pendingOnly++;
                    }
                }
            });

        if ($publishable < self::MIN_PUBLISHABLE_BACKLOG) {
            $problems['empty_publishable_backlog'] = "Darvozadan o'tadigan draftlar: {$publishable}";
        }

        if ($pendingOnly > 0) {
            $problems['unattended_moderation'] = "Faqat moderatsiya kutayotgan draftlar: {$pendingOnly}";
        }

        if ($problems === []) {
            $this->info("✅ Kontent quvuri sog'lom. Nashrga tayyor draftlar: {$publishable}");

            return self::SUCCESS;
        }

        foreach ($problems as $key => $detail) {
            $this->error("{$key}: {$detail}");
        }

        Log::error('Content pipeline degraded', $problems);

        if (! $this->option('dry-run')) {
            $this->notify($problems);
        }

        return self::FAILURE;
    }

    private function notify(array $problems): void
    {
        // DIQQAT: config('backup.notifications.mail.to') ISHLATILMAYDI —
        // config/backup.php:214 da u qattiq yozilgan 'your@example.com'
        // placeholder qiymati, env'dan olinmaydi. Ogohlantirish shu manzilga
        // jimgina ketib qolishi mumkin edi. Shuning uchun alohida kalit.
        //
        // env() emas, config('services.content_alert.email') o'qiladi:
        // production'da har bir deploy yo'li `config:cache` ishga tushiradi,
        // shundan keyin .env o'qilmaydi va env() bu yerda har doim null
        // qaytaradi — ogohlantirish jim-jimgina o'chib qoladi. config/
        // qiymatlari config:cache ichida saqlanadi, shuning uchun xavfsiz.
        $to = config('services.content_alert.email') ?: config('mail.from.address');

        if (! $to || str_contains((string) $to, 'example.com')) {
            Log::warning('Health check alert not sent: CONTENT_ALERT_EMAIL is not configured');

            return;
        }

        $body = "Kontent quvurida muammo:\n\n";
        foreach ($problems as $key => $detail) {
            $body .= "- {$key}: {$detail}\n";
        }

        try {
            Mail::raw($body, fn ($message) => $message->to($to)->subject('NextGenBeing: kontent quvuri to\'xtadi'));
        } catch (\Throwable $e) {
            Log::warning('Health check mail failed', ['error' => $e->getMessage()]);
        }
    }
}
