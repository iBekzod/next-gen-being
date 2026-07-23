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
    if (! filter_var(env('BLOG_AUTO_PUBLISH', false), FILTER_VALIDATE_BOOLEAN)) {
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
// Generate multi-part tutorial series every Monday at 9 AM.
// Gated by BLOG_AUTO_PUBLISH: when the local blog-bot is the active engine this
// stays off (the bot generates tutorials for free via Claude CLI). Flip
// BLOG_AUTO_PUBLISH=true only when the server-side Anthropic API has credits.
Schedule::command('tutorials:scheduled')
    ->weeklyOn(1, '9:00')
    ->timezone(config('app.timezone'))
    ->when(fn () => filter_var(env('BLOG_AUTO_PUBLISH', false), FILTER_VALIDATE_BOOLEAN))
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Weekly tutorial generation completed');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Weekly tutorial generation failed');
    });

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

// Cache pre-warm: hit top URLs every 6 hours to keep DB/view caches hot
Schedule::command('cache:prewarm', ['--limit=10'])
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();
