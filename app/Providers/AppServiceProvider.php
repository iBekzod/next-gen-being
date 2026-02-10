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
        // Register OAuth services
        $this->app->singleton(\App\Services\OAuthCredentialService::class);
        $this->app->singleton(\App\Services\OAuthProviderService::class);

        // === REGISTER ENGAGEMENT & MONETIZATION SERVICES ===

        // Tipping system service
        $this->app->singleton(\App\Services\TipService::class, function ($app) {
            return new \App\Services\TipService();
        });

        // Streak tracking service
        $this->app->singleton(\App\Services\StreakService::class, function ($app) {
            return new \App\Services\StreakService();
        });

        // Leaderboard service
        $this->app->singleton(\App\Services\LeaderboardService::class, function ($app) {
            return new \App\Services\LeaderboardService();
        });

        // Challenge service
        $this->app->singleton(\App\Services\ChallengeService::class, function ($app) {
            return new \App\Services\ChallengeService();
        });

        // Personalized feed service
        $this->app->singleton(\App\Services\PersonalizedFeedService::class, function ($app) {
            return new \App\Services\PersonalizedFeedService();
        });

        // Notification service
        $this->app->singleton(\App\Services\NotificationService::class, function ($app) {
            return new \App\Services\NotificationService();
        });

        // Creator analytics service
        $this->app->singleton(\App\Services\CreatorAnalyticsService::class, function ($app) {
            return new \App\Services\CreatorAnalyticsService();
        });

        // Affiliate service
        $this->app->singleton(\App\Services\AffiliateService::class, function ($app) {
            return new \App\Services\AffiliateService();
        });

        // Collection service
        $this->app->singleton(\App\Services\CollectionService::class, function ($app) {
            return new \App\Services\CollectionService();
        });

        // Content calendar service
        $this->app->singleton(\App\Services\ContentCalendarService::class, function ($app) {
            return new \App\Services\ContentCalendarService();
        });

        // Reader preference service
        $this->app->singleton(\App\Services\ReaderPreferenceService::class, function ($app) {
            return new \App\Services\ReaderPreferenceService();
        });

        // Creator tools service
        $this->app->singleton(\App\Services\CreatorToolsService::class, function ($app) {
            return new \App\Services\CreatorToolsService();
        });

        // Writing assistant service
        $this->app->singleton(\App\Services\WritingAssistantService::class, function ($app) {
            return new \App\Services\WritingAssistantService();
        });

        // Reader tracking service
        $this->app->singleton(\App\Services\ReaderTrackingService::class, function ($app) {
            return new \App\Services\ReaderTrackingService();
        });
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

        // === REGISTER MODEL OBSERVERS ===
        // Enable automatic audit logging and event handling

        \App\Models\Tip::observe(\App\Observers\TipObserver::class);
        \App\Models\Streak::observe(\App\Observers\StreakObserver::class);
        \App\Models\Challenge::observe(\App\Observers\ChallengeObserver::class);
        \App\Models\AffiliateLink::observe(\App\Observers\AffiliateLinkObserver::class);
    }
}
