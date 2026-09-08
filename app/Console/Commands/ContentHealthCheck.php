<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Content\PublishGate;
use App\Services\Content\TopicQueueService;
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

    public function __construct(
        private readonly PublishGate $gate,
        private readonly TopicQueueService $topicQueue,
    ) {
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

        // Postlar va tutoriallar ALOHIDA sanaladi. Ilgari ular bitta hovuzga
        // qo'shilardi, ya'ni uchta yaxshi post tutorial ochligini yashirardi —
        // holbuki ular alohida kadensiyada, alohida navbatdan nashr qilinadi.
        $publishablePosts = 0;
        $publishableTutorials = 0;
        $pendingOnly = 0;

        Post::where('status', 'draft')->select('id', 'content', 'moderation_status', 'series_title')
            ->chunk(200, function ($drafts) use (&$publishablePosts, &$publishableTutorials, &$pendingOnly) {
                foreach ($drafts as $draft) {
                    $failures = $this->gate->failures($draft);

                    if ($failures === []) {
                        if ($draft->series_title === null) {
                            $publishablePosts++;
                        } else {
                            $publishableTutorials++;
                        }
                    } elseif ($failures === ['moderation_pending']) {
                        $pendingOnly++;
                    }
                }
            });

        if ($publishablePosts < self::MIN_PUBLISHABLE_BACKLOG) {
            $problems['empty_publishable_post_backlog'] = "Darvozadan o'tadigan post draftlari: {$publishablePosts}";
        }

        if ($publishableTutorials < self::MIN_PUBLISHABLE_BACKLOG) {
            $problems['empty_publishable_tutorial_backlog'] = "Darvozadan o'tadigan tutorial draftlari: {$publishableTutorials}";
        }

        if ($pendingOnly > 0) {
            $problems['unattended_moderation'] = "Faqat moderatsiya kutayotgan draftlar: {$pendingOnly}";
        }

        // Scraping to'xtaganini aniqlash. Faol manba bo'lsa-yu, hech biri
        // 24 soat ichida yig'ilmagan bo'lsa — quvurning kirish uchi qurigan.
        //
        // Faol manba SONI 0 bo'lsa, bu ALOHIDA, aniqroq kalit bilan
        // xabar qilinadi (`no_active_sources`, `stale_scraping` emas):
        // ikkisi turli xil nosozlik — biri ILGARI ishlab turgan
        // scraper'ning jimgina to'xtashi (regressiya), ikkinchisi esa
        // tizim hech qachon sozlanmaganligi (masalan `content:init-sources`
        // hech qachon ishga tushirilmagan). Ikkalasini bitta kalit ostida
        // qo'shib yuborish operatorni chalg'itadi va "stale" so'zi
        // "hech qachon sozlanmagan" degani emasligini yashiradi.
        $activeSources = \App\Models\ContentSource::active()->count();

        if ($activeSources === 0) {
            $problems['no_active_sources'] = "Hech qanday kontent manbasi yoqilmagan, mavzular navbati hech qachon to'lmaydi. "
                . "Davolash: `content:init-sources` buyrug'ini ishga tushiring.";
        } else {
            $freshlyScraped = \App\Models\ContentSource::active()
                ->where('last_scraped_at', '>=', now()->subDay())
                ->count();

            if ($freshlyScraped === 0) {
                $problems['stale_scraping'] = "So'nggi 24 soatda birorta manba yig'ilmadi ({$activeSources} ta faol manba)";
            } elseif ($this->topicQueue->topCandidates()->isEmpty()) {
                // Uchinchi, ALOHIDA nosozlik turi. `no_active_sources` —
                // hech narsa sozlanmagan; `stale_scraping` — sozlangan, lekin
                // yig'ish to'xtagan. Bu esa: yig'ish AYNI PAYTDA ishlayapti,
                // maqolalar kelayotir, ammo hech bir mavzu MUSTAQIL MANBALAR
                // bilan tasdiqlanmadi, ya'ni klasterlash bosqichi bo'sh
                // chiqmoqda va trend navbati hech qachon to'lmayapti.
                //
                // Bu jimgina buziladigan holat: `content:rank-topics` bo'sh
                // navbatni SUCCESS bilan chiqaradi, GenerateAiPost esa eski
                // kalit-so'z evristikasiga qaytib, har doim biror natija
                // beradi — shuning uchun tashqaridan hamma narsa sog'lom
                // ko'rinadi. Aynan shu sababli buni health-check aytishi shart.
                $problems['empty_topic_queue'] = "Manbalar yig'ilmoqda ({$activeSources} ta faol manba, yig'ish yangi), "
                    . "lekin trend navbati bo'sh: birorta mavzu mustaqil manbalar bilan tasdiqlanmadi. "
                    . "Kirish uchi ishlayapti, klasterlash bosqichi hech narsa bermayapti — "
                    . "generatsiya jimgina eski kalit-so'z evristikasiga qaytadi.";
            }
        }

        if ($problems === []) {
            $this->info(
                "✅ Kontent quvuri sog'lom. Nashrga tayyor draftlar: "
                . "{$publishablePosts} post, {$publishableTutorials} tutorial"
            );

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
