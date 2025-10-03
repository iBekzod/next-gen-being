<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'key' => 'plans.basic',
                'value' => [
                    'name' => 'Basic',
                    'price' => 9.99,
                    'interval' => 'monthly',
                    'features' => [
                        'Premium issues',
                        'Ad-free reading',
                        'Foundational templates',
                    ],
                ],
            ],
            [
                'key' => 'plans.pro',
                'value' => [
                    'name' => 'Pro',
                    'price' => 19.99,
                    'interval' => 'monthly',
                    'features' => [
                        'Everything in Basic',
                        'Early access drops',
                        'Exclusive webinars',
                        'Downloadable assets',
                    ],
                ],
            ],
            [
                'key' => 'plans.enterprise',
                'value' => [
                    'name' => 'Enterprise',
                    'price' => 49.99,
                    'interval' => 'monthly',
                    'features' => [
                        'Up to 10 seats',
                        'API access',
                        'Dedicated success manager',
                        'Custom analytics and onboarding',
                    ],
                ],
            ],
        ];

        foreach ($plans as $row) {
            Setting::updateOrCreate(
                ['key' => $row['key']],
                [
                    'value' => json_encode($row['value']),
                    'type' => 'json',
                    'description' => 'Preset plan configuration for pricing page',
                    'group' => 'subscription',
                    'is_public' => true,
                ]
            );
        }
    }
}