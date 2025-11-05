<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $socialite = $this->app->make(SocialiteFactory::class);

        // Configure YouTube (via Google OAuth)
        $socialite->extend('google', function ($app) use ($socialite) {
            $config = $app['config']['services.youtube'];
            return $socialite->buildProvider(
                \Laravel\Socialite\Two\GoogleProvider::class,
                $config
            );
        });

        // LinkedIn OpenID Connect
        $socialite->extend('linkedin-openid', function ($app) use ($socialite) {
            $config = $app['config']['services.linkedin'];
            return $socialite->buildProvider(
                \SocialiteProviders\LinkedInOpenId\Provider::class,
                $config
            );
        });
    }
}
