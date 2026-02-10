<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'lemonsqueezy' => [
        // API credentials (read from official package config)
        'api_key' => env('LEMON_SQUEEZY_API_KEY'),
        'store_id' => env('LEMON_SQUEEZY_STORE'),
        'store_domain' => env('LEMONSQUEEZY_STORE_DOMAIN', 'store.nextgenbeing.com'),
        'signing_secret' => env('LEMON_SQUEEZY_SIGNING_SECRET'),

        // Platform subscription variant IDs (for platform access)
        'basic_variant_id' => env('LEMONSQUEEZY_BASIC_VARIANT_ID'),
        'pro_variant_id' => env('LEMONSQUEEZY_PRO_VARIANT_ID'),
        'team_variant_id' => env('LEMONSQUEEZY_TEAM_VARIANT_ID'),

        // AI subscription variant IDs (for AI content generation)
        'ai_basic_variant_id' => env('LEMONSQUEEZY_AI_BASIC_VARIANT_ID'),
        'ai_premium_variant_id' => env('LEMONSQUEEZY_AI_PREMIUM_VARIANT_ID'),
        'ai_enterprise_variant_id' => env('LEMONSQUEEZY_AI_ENTERPRISE_VARIANT_ID'),

        'test_mode' => env('LEMONSQUEEZY_TEST_MODE', false), // Production default is false
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),

        // Google AdSense configuration for monetization
        'adsense' => [
            'enabled' => env('GOOGLE_ADSENSE_ENABLED', false),
            'client_id' => env('GOOGLE_ADSENSE_CLIENT_ID'), // ca-pub-xxxxxxxxxxxxxxxx
            'slot_display' => env('GOOGLE_ADSENSE_SLOT_DISPLAY'), // Main display ad
            'slot_sidebar' => env('GOOGLE_ADSENSE_SLOT_SIDEBAR'), // Sidebar ad
            'slot_intext' => env('GOOGLE_ADSENSE_SLOT_INTEXT'), // In-text ads (optional)
        ],
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT_URI'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'model' => env('OPENAI_MODEL', 'gpt-4'),
        // Text-to-Speech settings
        'tts_model' => env('OPENAI_TTS_MODEL', 'tts-1-hd'),
        'tts_voice' => env('OPENAI_TTS_VOICE', 'onyx'), // alloy, echo, fable, onyx, nova, shimmer
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'base_url' => 'https://api.groq.com/openai/v1',
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'groq'), // groq, openai, or anthropic
    ],

    'stability' => [
        'api_key' => env('STABILITY_API_KEY'),
    ],

    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY'),
    ],

    // Video Generation Services
    'pexels' => [
        'api_key' => env('PEXELS_API_KEY'),
    ],

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM'), // Default voice
        'model' => env('ELEVENLABS_MODEL', 'eleven_multilingual_v2'),
    ],

    // Social Media OAuth
    'youtube' => [
        'client_id' => env('YOUTUBE_CLIENT_ID'),
        'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
        'redirect' => env('YOUTUBE_REDIRECT_URI'),
    ],

    'instagram' => [
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect' => env('INSTAGRAM_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'channel_id' => env('TELEGRAM_CHANNEL_ID'),
    ],

    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('DISCORD_OAUTH_REDIRECT', '/auth/oauth/discord/callback'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
    ],

];
