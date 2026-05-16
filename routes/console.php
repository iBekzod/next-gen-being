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

// Daily AI post (9 AM) - From content plan (auto FREE/PREMIUM based on plan)
Schedule::command('ai:generate-post')
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'))
    ->runInBackground()
    ->withoutOverlapping(60)
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Daily AI post generated from content plan');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Daily AI post generation failed');
    });

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
// Generate multi-part tutorial series every Monday at 9 AM
Schedule::command('tutorials:scheduled')
    ->weeklyOn(1, '9:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Weekly tutorial generation completed');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Weekly tutorial generation failed');
    });

// Auto-publish draft tutorials older than 24 hours - WITH quality gates
Schedule::call(function () {
    \App\Models\Post::where('status', 'draft')
        ->whereNotNull('series_title')
        ->where('created_at', '<', now()->subHours(24))
        ->chunk(50, function ($posts) {
            foreach ($posts as $post) {
                // Gate 1: word count >= 1500
                $wordCount = str_word_count(strip_tags($post->content));
                if ($wordCount < 1500) {
                    \Illuminate\Support\Facades\Log::info("Auto-publish skipped: post {$post->id} too short ({$wordCount} words)");
                    continue;
                }
                // Gate 2: ends with proper sentence terminator (not truncated)
                if (!preg_match('/[.!?]\s*$/', trim($post->content))) {
                    \Illuminate\Support\Facades\Log::info("Auto-publish skipped: post {$post->id} appears truncated");
                    continue;
                }
                // Gate 3: balanced code fences
                if (substr_count($post->content, '```') % 2 !== 0) {
                    \Illuminate\Support\Facades\Log::info("Auto-publish skipped: post {$post->id} has unclosed code blocks");
                    continue;
                }
                // Gate 4: respect moderation_status if it was set to 'pending' explicitly
                if ($post->moderation_status === 'pending') {
                    \Illuminate\Support\Facades\Log::info("Auto-publish skipped: post {$post->id} is moderation_status=pending");
                    continue;
                }
                $post->update(['status' => 'published', 'published_at' => now()]);
            }
        });
})
    ->dailyAt('18:00')
    ->timezone(config('app.timezone'))
    ->name('auto-publish-pending-tutorials');

// Cache pre-warm: hit top URLs every 6 hours to keep DB/view caches hot
Schedule::command('cache:prewarm', ['--limit=10'])
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();
