<?php

namespace Database\Seeders;

use App\Models\DigitalProduct;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Services\DemoStorageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FitTrackListingSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()->orderBy('id')->first();
        if (! $seller) {
            return; // needs a user; UserSeeder runs first in DatabaseSeeder
        }

        $slug = 'fittrack-workout-saas';

        $listing = MarketplaceListing::firstOrCreate(
            ['slug' => $slug],
            [
                'seller_id'   => $seller->id,
                'title'       => 'FitTrack — Workout & Habit SaaS',
                'tagline'     => 'Ship a fitness SaaS in a day, not a month',
                'description' => "A production-ready fitness & habit-tracking SaaS.\n\n".
                                 "Auth, subscription billing, and a polished dashboard already wired together. ".
                                 "Clean Laravel 12 + Alpine code, documented, deploy guide included.",
                'category'    => 'saas',
                'tags'        => ['laravel', 'alpine', 'saas', 'fitness'],
                'demo_type'   => 'static',
                'demo_path'   => "demos/{$slug}/index.html",
                'status'      => 'published',
                'plagiarism_status' => 'checked',
                'rating'      => 4.9,
                'reviews_count' => 23,
                'sales_count' => 23,
                'published_at'=> now(),
            ]
        );

        // Store the live demo (self-contained HTML). Replace this string with the
        // full approved FitTrack mockup markup at execution time (Task 8).
        app(DemoStorageService::class)->storeIndexHtml($slug,
            '<!doctype html><html><head><meta charset="utf-8"><title>FitTrack demo</title>'.
            '<style>body{margin:0;font-family:system-ui;background:#0f1420;color:#e8ecf6;display:grid;place-items:center;height:100vh}'.
            'h1{font-weight:800}</style></head><body><div><h1>FitTrack — live demo</h1>'.
            '<p style="color:#8593b5">Interactive demo mockup goes here.</p></div></body></html>'
        );

        $tiers = [
            ['tier' => 'prompt', 'title' => 'FitTrack — Prompt Plan',  'price' => 5,  'type' => 'prompt',       'desc' => 'The full Claude Code build strategy & prompts.'],
            ['tier' => 'design', 'title' => 'FitTrack — Design',       'price' => 7,  'type' => 'template',     'desc' => 'Complete HTML/CSS design — the exact demo UI.'],
            ['tier' => 'code',   'title' => 'FitTrack — Full Project', 'price' => 49, 'type' => 'code_example', 'desc' => 'Working code, end to end. Clone, deploy, ship.'],
            ['tier' => 'bundle', 'title' => 'FitTrack — Bundle',       'price' => 59, 'type' => 'code_example', 'desc' => 'Prompt + design + full project, discounted.'],
        ];

        foreach ($tiers as $t) {
            DigitalProduct::firstOrCreate(
                ['listing_id' => $listing->id, 'tier' => $t['tier']],
                [
                    'creator_id'  => $seller->id,
                    'title'       => $t['title'],
                    'slug'        => Str::slug($t['title']),
                    'short_description' => $t['desc'],
                    'description' => $t['desc'],
                    'type'        => $t['type'],
                    'price'       => $t['price'],
                    'is_free'     => false,
                    'status'      => 'published',
                    'published_at'=> now(),
                    'revenue_share_percentage' => DigitalProduct::MARKETPLACE_REVENUE_SHARE,
                    // lemonsqueezy_variant_id must be set to a real LS variant before going live.
                ]
            );
        }
    }
}
