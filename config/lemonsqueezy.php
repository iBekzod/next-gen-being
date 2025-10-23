<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy API Key
    |--------------------------------------------------------------------------
    |
    | Your Lemon Squeezy API key. You can create one in your Lemon Squeezy
    | dashboard under Settings > API.
    |
    */
    'api_key' => env('LEMONSQUEEZY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy Store ID
    |--------------------------------------------------------------------------
    |
    | Your Lemon Squeezy store ID. You can find this in your store settings.
    |
    */
    'store_id' => env('LEMONSQUEEZY_STORE_ID'),

    /*
    |--------------------------------------------------------------------------
    | Lemon Squeezy Signing Secret
    |--------------------------------------------------------------------------
    |
    | The signing secret for webhook verification. You can find this in your
    | webhook settings in the Lemon Squeezy dashboard.
    |
    */
    'signing_secret' => env('LEMONSQUEEZY_SIGNING_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, Lemon Squeezy will operate in test mode. This is useful
    | for development and testing purposes.
    |
    */
    'test_mode' => env('LEMONSQUEEZY_TEST_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Product Variant IDs
    |--------------------------------------------------------------------------
    |
    | Your Lemon Squeezy product variant IDs for different subscription plans.
    |
    */
    'variants' => [
        'basic' => env('LEMONSQUEEZY_BASIC_VARIANT_ID'),
        'pro' => env('LEMONSQUEEZY_PRO_VARIANT_ID'),
        'team' => env('LEMONSQUEEZY_TEAM_VARIANT_ID'),
    ],
];
