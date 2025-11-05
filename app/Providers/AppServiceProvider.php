<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use LemonSqueezy\Laravel\Events\WebhookHandled;
use App\Listeners\HandleLemonSqueezyWebhook;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register LemonSqueezy webhook listener for AI subscriptions
        Event::listen(
            WebhookHandled::class,
            HandleLemonSqueezyWebhook::class
        );
    }
}
