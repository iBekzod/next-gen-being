<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Generate one AI post every day at 9:00 AM
Schedule::command('ai:generate-post')
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Daily AI post generated successfully');
    })
    ->onFailure(function () {
        \Illuminate\Support\facades\Log::error('Daily AI post generation failed');
    });
