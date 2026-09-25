<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ========================================
// AI CONTENT GENERATION (Strategic Plan-Based)
// ========================================
// Generates posts from the monthly content plan (80% free, 20% premium)
// Posts automatically follow the conversion funnel strategy

// Content cron - runs the check daily but only generates ONE post every 5 days.
// Cadence is enforced against the DB (last published standalone post), so it
// survives cache flushes and counts posts from any source toward the schedule.
// Preference order: if the local blog-bot (D:/projects/MyProjects/blog-bot,
// Claude Code CLI on the Max plan) is alive (heartbeat < 25h) it handles
// generation and the server skips; otherwise the server generates via API.
Schedule::call(function () {
    // Master switch. Set BLOG_AUTO_PUBLISH=true in .env to enable automated
    // publishing (was disabled during the AdSense de-AI-ify of the corpus).
    // Read through config('content.auto_publish'), NOT env(): production runs
    // `php artisan config:cache` on every deploy, after which .env is not read
    // and env() would always return the false default - the entire content
    // engine would stay off no matter what .env says. Same class of bug as the
    // one already fixed for CONTENT_ALERT_EMAIL.
    if (! filter_var(config('content.auto_publish'), FILTER_VALIDATE_BOOLEAN)) {
        \Illuminate\Support\Facades\Log::info('Content cron: skipped (BLOG_AUTO_PUBLISH disabled)');
        return;
    }

    // If the local bot is alive, let it handle generation (it runs its own cadence).
    $lastSeen = \Illuminate\Support\Facades\Cache::get('bot:last_seen');
    if ($lastSeen) {
        try {
            $age = \Carbon\Carbon::parse($lastSeen)->diffInHours(now());
            if ($age < 25) {
                \Illuminate\Support\Facades\Log::info('Content cron: skipping (bot heartbeat fresh)', ['last_seen' => $lastSeen, 'hours_ago' => $age]);
                return;
            }
        } catch (\Throwable $e) {
            // fall through to API
        }
    }

    // Throttle to one standalone post every 5 days (tutorials are excluded so the
    // weekly tutorial schedule does not delay regular posts).
    $lastPostAt = \App\Models\Post::whereNull('series_title')
        ->whereNotNull('published_at')
        ->where('status', 'published')
        ->latest('published_at')
        ->value('published_at');

    if ($lastPostAt && abs(\Carbon\Carbon::parse($lastPostAt)->diffInDays(now())) < 5) {
        return; // not yet time for the next post
    }

    \Illuminate\Support\Facades\Log::info('Content cron: generating one post via API (5-day cadence)');
    \Illuminate\Support\Facades\Artisan::call('ai:generate-post', ['--count' => 1]);
})
    ->name('content-post-every-5-days')
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping(60);

// ========================================
// CONTENT PLANNING (Monthly)
// ========================================
// Generate strategic content plan for next month (runs on 25th of each month)
Schedule::command('content:plan')
    ->monthlyOn(25, '00:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Monthly content plan generated successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Monthly content plan generation failed');
    });

// ========================================
// NEWSLETTER AUTOMATION
// ========================================
Schedule::command('newsletter:send-weekly')
    ->weeklyOn(1, '9:00')
    ->timezone(config('app.timezone'));

// Kunlik digest — haftalik flagman chiqarish orasidagi kunlarni to'ldiradi (spec D2).
Schedule::command('newsletter:send-daily')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();

// Welcome/onboarding drip — deliver the next due onboarding email to newly
// confirmed subscribers (step 1 fires immediately on verification; this sweeps
// the day-2/4/6 follow-ups).
Schedule::command('newsletter:send-onboarding')
    ->dailyAt('08:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();

Schedule::command('newsletter:cleanup')
    ->monthly();

// ========================================
// SEO & SEARCH ENGINE OPTIMIZATION
// ========================================
// Update sitemap after new posts are published
Schedule::command('sitemap:generate')
    ->dailyAt('23:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Sitemap generated successfully');
    });

// Update RSS feed after new posts
Schedule::command('rss:generate')
    ->dailyAt('23:15')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('RSS feed generated successfully');
    });

// Ping search engines about sitemap updates (weekly)
Schedule::command('seo:ping-search-engines')
    ->weeklyOn(1, '10:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Search engines pinged about sitemap updates');
    });

// ========================================
// MAINTENANCE & CLEANUP
// ========================================
// Clean orphaned media files (weekly)
Schedule::command('media-library:clean', ['--delete-orphaned'])
    ->weeklyOn(7, '03:00')
    ->timezone(config('app.timezone'));

// Prune old failed jobs (weekly)
Schedule::command('queue:prune-failed', ['--hours=168'])
    ->weekly()
    ->sundays()
    ->at('02:00');

// Clear expired password reset tokens (daily)
Schedule::command('auth:clear-resets')
    ->daily();

// Prune expired Sanctum tokens (if using API tokens)
Schedule::command('sanctum:prune-expired', ['--hours=24'])
    ->daily();

// ========================================
// BACKUPS (IMPORTANT!)
// ========================================
// Daily database backup
Schedule::command('backup:run', ['--only-db'])
    ->dailyAt('01:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Database backup completed');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Database backup failed');
    });

// Weekly full backup (database + files)
Schedule::command('backup:run')
    ->weeklyOn(1, '02:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Full backup completed');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Full backup failed');
    });

// Monitor backup health (daily)
Schedule::command('backup:monitor')
    ->dailyAt('04:00')
    ->timezone(config('app.timezone'));

// Clean old backups (monthly)
Schedule::command('backup:clean')
    ->monthly();

// ========================================
// VIDEO GENERATION & SOCIAL MEDIA PUBLISHING
// ========================================
// Process scheduled videos (every 15 minutes)
Schedule::command('videos:process-scheduled')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Scheduled videos processed successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Scheduled video processing failed');
    });

// Process urgent priority videos more frequently (every 5 minutes)
Schedule::command('videos:process-scheduled', ['--priority=urgent'])
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Urgent videos processed');
    });

// Auto-publish approved videos to social media (hourly)
Schedule::command('social:auto-publish')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Social media auto-publish completed');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Social media auto-publish failed');
    });

// Update engagement metrics from social media platforms (daily at 2 AM)
Schedule::command('social:update-engagement')
    ->dailyAt('02:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Social media engagement metrics updated');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Social media engagement update failed');
    });


// Monitor video generation quota usage (weekly)
Schedule::call(function () {
    // Check YouTube API quota usage
    $quotaUsage = \App\Models\VideoGeneration::where('created_at', '>=', now()->subWeek())
        ->where('status', 'completed')
        ->count();

    if ($quotaUsage > 40) {
        \Illuminate\Support\Facades\Log::warning("High video generation usage this week: {$quotaUsage} videos");
    }
})->weeklyOn(1, '10:00')->name('monitor-video-quota');

// ========================================
// TUTORIAL GENERATION (Weekly)
// ========================================
// `tutorials:scheduled` ATAYLAB rejalashtirilmagan. Buyruq o'zi joyida qoldi
// (qo'lda ishga tushirish uchun), lekin har dushanba avtomatik ishlamaydi.
//
// Sabab: u `AITutorialGenerationService` orqali TO'LOVLI Anthropic API'ga
// boradi, akkauntda esa kredit yo'q. 2026-09-14 va 2026-09-21 dagi
// ishga tushishlar 8 qism × 3 urinish = 24 ta HTTP 400 "Your credit balance
// is too low" bilan tugadi va nol post yaratdi. Ilgari BLOG_AUTO_PUBLISH
// darvozasi buni to'sib turardi; 2026-09-22 da u `true` ga o'tkazilgach,
// haftalik behuda urinish boshlandi.
//
// Tutoriallarni ENDI blog-bot yozadi (blog-bot/main.py,
// `_generate_and_post_tutorial`, har TUTORIAL_EVERY_DAYS kunda) — u Claude
// CLI orqali Max obunasidan foydalanadi, ya'ni bepul, va natijani
// `/api/bot/post` ga `series_title` bilan yuboradi. Postlar ham shu yo'ldan
// keladi. Server tomonida to'lovli API'ga parallel ikkinchi yo'l saqlash —
// bu faqat kredit tugaganda jimgina nol qaytaradigan ortiqcha yo'l.
//
// Anthropic krediti to'ldirilsa va server tomonidan generatsiya kerak bo'lsa,
// shu blokni qaytarish yetarli.

// Nashrdan YARIM SOAT OLDIN: moderator ishlamagani uchun kutishda qolgan
// draftlarni qayta o'tkazadi.
//
// Sabab: moderatsiya modeli (Groq) o'chirilganda har draft `pending` bo'lib
// yozildi va shu holatda QOLDI — modelni tuzatish ularni o'zi qutqarmaydi,
// chunki `moderation_status` bir marta yoziladi. Shuning uchun 2026-08-04 dan
// 2026-09-26 gacha 15 ta draft (ularning ikkitasi darvozadan to'liq o'tadigan,
// 4000 so'zlik tutoriallar) abadiy kutishda turdi va `content:drip` har kuni
// nol post nashr qildi.
//
// Sog'lom holatda bu buyruq BEKORCHI: nomzod topilmasa bitta ham API
// chaqiruvi qilmaydi. Ya'ni narxi nol, foydasi esa — xizmat qaytib kelganda
// orqada qolgan draftlar o'zi qo'shilib ketadi.
Schedule::command('content:remoderate')
    ->dailyAt('17:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground();

// Controlled publishing cadence — 1 regular post / 5 days + 1 tutorial / 7 days,
// quality-gated (see App\Console\Commands\ContentDripPublish). Replaces the old
// daily "publish everything recent" job: keeps a steady publishing rhythm feeding
// SEO, drains the draft backlog slowly (never a slop-dump), and the gates keep
// short/truncated AI drafts out of the public corpus (Google HCU / AdSense).
Schedule::command('content:drip')
    ->dailyAt('18:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('content:drip completed');
    });

// ========================================
// TREND MAVZULAR (C2)
// ========================================
// Kontent manbalarini yig'ish. Bu quyi tizim 2026-01 dan beri kodda bor edi,
// lekin hech qachon jadvalga qo'yilmagan — ya'ni bir marta ham ishlamagan.
// --async: har bir manba alohida queue job'ga tushadi, shuning uchun bitta
// sekin sayt qolganlarini to'xtatib qo'ymaydi.
Schedule::command('content:scrape-all', ['--async', '--limit=40'])
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('content:scrape-all failed');
    });

// Dublikatlarni aniqlash — `duplicate_of` ustunini shu yerda to'ldiradi, bu esa
// mavzu klasterlashning yagona manbasi (TopicQueueService shu ustunga qarab
// guruhlaydi). Ranking'dan (08:30) OLDIN tugashi SHART, lekin bu ikki alohida
// cron yozuvi — `withoutOverlapping()` har biri faqat o'zining oldingi
// nusxasidan himoyalaydi, ikkinchi buyruqdan emas. Shuning uchun 06:00 va
// 08:30 orasidagi 2.5 soatlik farq KAFOLAT emas, faqat zaxira vaqt: agar
// dedup shu oraliqda tugamasa, ranking baribir ishga tushib, yarim
// deduplikatsiya qilingan (ya'ni klaster o'lchamlari kamroq ko'rsatilgan)
// suratni ballaydi.
//
// --hours=72 (standart 24 emas): pairwise solishtiruv narxi va ko'p kunlik
// mos kelish (corroboration) o'rtasidagi ataylab tanlangan murosa.
// findAllDuplicates() qatorlarni `created_at >= now()->subHours($hours)`
// bilan cheklaydi va hech narsa markAsProcessed() chaqirmagani uchun har bir
// qator taqqoslash to'plamida abadiy qoladi — ya'ni bu O(n^2). 24 soatda
// ~1600 qator/kun ~1.3M taqqoslash beradi; to'liq 7 kunlik (168 soat) oyna
// ~11000 qator va ~60M taqqoslash bo'lar edi — PHP'da bu daqiqalar emas.
// 72 soat kross-kunlik mos kelishning ko'pini ushlaydi va hisob narxini bir
// tartib pastroq saqlaydi. Haqiqiy hajm ma'lum bo'lgach qayta o'lchash kerak;
// asl tuzilmaviy tuzatish (kategoriya/kalit so'z bo'yicha nomzodlarni oldindan
// bloklash, taqqoslash to'plami o'sishini to'xtatish uchun) — alohida ish.
Schedule::command('content:deduplicate', ['--hours=72'])
    ->dailyAt('06:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();

// Mavzularni ballash — generatsiya slotidan (09:00) oldin ishlaydi.
Schedule::command('content:rank-topics')
    ->dailyAt('08:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();

// Cache pre-warm: hit top URLs every 6 hours to keep DB/view caches hot
Schedule::command('cache:prewarm', ['--limit=10'])
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();

// Kontent quvuri salomatligi — 2026-08 dagi to'rt haftalik jimlik takrorlanmasligi uchun.
Schedule::command('content:health-check')
    ->dailyAt('19:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
