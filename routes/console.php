<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Generate multiple AI posts throughout the day
// Morning post (9 AM) - Single advanced article
Schedule::command('ai:generate-post --premium')
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Morning AI post generated successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Morning AI post generation failed');
    });

// Afternoon post (2 PM) - Tutorial series (generates 3 parts)
Schedule::command('ai:generate-post --series=3 --premium')
    ->dailyAt('14:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Afternoon tutorial series generated successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Afternoon tutorial series generation failed');
    });

// Evening post (7 PM) - Single article (mix of premium and free)
Schedule::command('ai:generate-post')
    ->dailyAt('19:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Evening AI post generated successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Evening AI post generation failed');
    });

// Newsletter Automation
Schedule::command('newsletter:send-weekly')
    ->weeklyOn(1, '9:00')
    ->timezone(config('app.timezone'));

Schedule::command('newsletter:cleanup')
    ->monthly();
