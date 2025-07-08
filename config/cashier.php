<?php
return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],
    'model' => App\Models\User::class,
    'currency' => env('CASHIER_CURRENCY', 'usd'),
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'en'),
    'price_ids' => [
        'basic' => env('STRIPE_BASIC_PRICE_ID'),
        'pro' => env('STRIPE_PRO_PRICE_ID'),
        'enterprise' => env('STRIPE_ENTERPRISE_PRICE_ID'),
    ],
];
