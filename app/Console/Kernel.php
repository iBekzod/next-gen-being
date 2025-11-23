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
        // Generate tutorials weekly (every Monday at 9 AM)
        $schedule->command('tutorials:scheduled')
            ->weeklyOn(1, '9:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground();

        // Alternative: Generate tutorials daily at different times
        // $schedule->command('tutorials:scheduled')
        //     ->dailyAt('09:00')
        //     ->withoutOverlapping()
        //     ->runInBackground();

        // Alternative: Generate tutorials every 3 days
        // $schedule->command('tutorials:scheduled')
        //     ->everyThreeHours()
        //     ->withoutOverlapping()
        //     ->runInBackground();

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
