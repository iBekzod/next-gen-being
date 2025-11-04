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
