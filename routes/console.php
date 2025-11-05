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

// Morning post (9 AM) - From content plan (auto FREE/PREMIUM based on plan)
Schedule::command('ai:generate-post')
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Morning AI post generated from content plan');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Morning AI post generation failed');
    });

// Note: Generate 1 post per day = 30 posts per month
// This follows the strategic plan: 24 FREE (80%) + 6 PREMIUM (20%)
// If you want MORE posts per day, uncomment additional schedules below:

// Afternoon post (2 PM) - OPTIONAL: Enable for 2 posts/day
Schedule::command('ai:generate-post')
    ->dailyAt('14:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Afternoon AI post generated from content plan');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Afternoon AI post generation failed');
    });

// Evening post (7 PM) - OPTIONAL: Enable for 3 posts/day
Schedule::command('ai:generate-post')
    ->dailyAt('19:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Evening AI post generated from content plan');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Evening AI post generation failed');
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

// Clean up temporary video files (daily at 3 AM)
Schedule::command('app:cleanup-temp-files')
    ->dailyAt('03:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Temporary video files cleaned up');
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
