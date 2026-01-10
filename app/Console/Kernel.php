<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // === ENGAGEMENT FEATURES SCHEDULING ===

        // Daily analytics generation (run at midnight)
        $schedule->call(function () {
            $users = \App\Models\User::where('is_creator', true)->get();
            $analyticsService = app(\App\Services\CreatorAnalyticsService::class);

            foreach ($users as $user) {
                $analyticsService->generateDailyAnalytics($user);
            }
        })
            ->daily()
            ->at('00:30')
            ->withoutOverlapping()
            ->onOneServer();

        // Publish overdue scheduled posts (every hour)
        $schedule->call(function () {
            $calendarService = app(\App\Services\ContentCalendarService::class);
            $overduePosts = \App\Models\ScheduledPost::where('status', 'scheduled')
                ->where('scheduled_at', '<=', now())
                ->get();

            foreach ($overduePosts as $post) {
                $calendarService->publishScheduledPost($post);
            }
        })
            ->hourly()
            ->withoutOverlapping();

        // Send streak notifications for at-risk streaks (twice daily)
        $schedule->call(function () {
            $streakService = app(\App\Services\StreakService::class);
            $notificationService = app(\App\Services\NotificationService::class);

            // Find streaks that are about to break (24 hours since last activity)
            $atRiskStreaks = \App\Models\Streak::where('current_streak', '>', 0)
                ->where('last_activity_date', '<', now()->subHours(23))
                ->get();

            foreach ($atRiskStreaks as $streak) {
                $notificationService->sendStreakAtRiskNotification($streak->user, $streak);
            }
        })
            ->twiceDaily(9, 18)
            ->withoutOverlapping();

        // Weekly digest emails (every Sunday at 8 AM)
        $schedule->call(function () {
            $notificationService = app(\App\Services\NotificationService::class);
            $users = \App\Models\User::where('email_notifications', true)->get();

            foreach ($users as $user) {
                $notificationService->sendWeeklyDigest($user);
            }
        })
            ->weekly()
            ->sundays()
            ->at('08:00')
            ->withoutOverlapping()
            ->onOneServer();

        // Check trending posts and notify creators (every 6 hours)
        $schedule->call(function () {
            $leaderboardService = app(\App\Services\LeaderboardService::class);
            $notificationService = app(\App\Services\NotificationService::class);

            $trendingPosts = $leaderboardService->getTrendingPosts(50, '24hours');

            foreach ($trendingPosts as $post) {
                // Notify creator only if post is trending and not previously notified today
                if ($post->views > 100) {
                    $notificationService->sendPostTrendingNotification($post->user, $post);
                }
            }
        })
            ->everyHours(6)
            ->withoutOverlapping();

        // Check milestone achievements (daily)
        $schedule->call(function () {
            $streakService = app(\App\Services\StreakService::class);
            $notificationService = app(\App\Services\NotificationService::class);

            // Check for streaks reaching 7, 30, 100, 365 day milestones
            $streaks = \App\Models\Streak::all();

            foreach ($streaks as $streak) {
                $milestones = [7, 30, 100, 365];
                if (in_array($streak->current_streak, $milestones)) {
                    $notificationService->sendStreakMilestoneNotification($streak->user, $streak);
                }
            }
        })
            ->daily()
            ->at('12:00')
            ->withoutOverlapping();

        // === ORIGINAL TUTORIAL GENERATION SCHEDULING ===

        // Generate tutorials weekly (every Monday at 9 AM)
        $schedule->command('tutorials:scheduled')
            ->weeklyOn(1, '9:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        // Optional: Publish pending tutorials automatically after 24 hours
        $schedule->call(function () {
            \App\Models\Post::where('status', 'draft')
                ->where('series_title', '!=', null)
                ->where('created_at', '<', now()->subHours(24))
                ->update(['status' => 'published', 'published_at' => now()]);
        })
            ->daily()
            ->at('18:00');

        // Optional: Generate analytics and clean up old cache
        $schedule->command('cache:prune-stale-tags')
            ->hourly();

        // Clean up old analytics data (keep last 90 days)
        $schedule->call(function () {
            \App\Models\CreatorAnalytic::where('date', '<', now()->subDays(90))
                ->delete();
        })
            ->monthly()
            ->onOneServer();

        // === CONTENT CURATION PIPELINE SCHEDULING ===

        // 1. Scrape all sources (6 AM daily)
        $schedule->command('content:scrape-all --async')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        // 2. Find duplicates and group content (8 AM daily)
        $schedule->command('content:deduplicate --hours=24 --async')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        // 3. Paraphrase aggregations (10 AM daily - 3 posts with diverse topics)
        $schedule->command('content:paraphrase-pending --limit=3')
            ->dailyAt('10:00')
            ->withoutOverlapping()
            ->runInBackground();

        // 4. Translate curated posts (11 AM daily - 3 posts × 4 languages = 12 translations)
        $schedule->command('content:translate-pending --limit=5 --languages=es,fr,de,zh')
            ->dailyAt('11:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        // 5. Prepare review notifications (12 PM daily)
        $schedule->command('content:prepare-review --limit=10')
            ->dailyAt('12:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        // 6. Auto-publish approved posts (2 PM daily - 30% premium, 70% free for monetization)
        $schedule->command('content:publish-approved --limit=10 --premium-percent=30')
            ->dailyAt('14:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
