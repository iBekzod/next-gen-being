<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsMarketplaceListing;
use Illuminate\Database\Seeder;

class FitTrackListingSeeder extends Seeder
{
    use SeedsMarketplaceListing;

    public function run(): void
    {
        $this->seedMarketplaceListing(
            [
                'slug'        => 'fittrack-workout-saas',
                'title'       => 'FitTrack — Workout & Habit SaaS',
                'tagline'     => 'Ship a fitness SaaS in a day, not a month',
                'description' => "A production-ready fitness & habit-tracking SaaS.\n\n".
                                 "Auth, subscription billing, and a polished dashboard already wired together. ".
                                 "Clean Laravel 12 + Alpine code, documented, deploy guide included.",
                'category'    => 'saas',
                'tags'        => ['laravel', 'alpine', 'saas', 'fitness'],
                'rating'      => 4.9,
                'reviews_count' => 23,
                'sales_count' => 23,
            ],
            [
                ['tier' => 'prompt', 'title' => 'FitTrack — Prompt Plan',  'price' => 5,  'type' => 'prompt',       'desc' => 'The full Claude Code build strategy & prompts.'],
                ['tier' => 'design', 'title' => 'FitTrack — Design',       'price' => 7,  'type' => 'template',     'desc' => 'Complete HTML/CSS design — the exact demo UI.'],
                ['tier' => 'code',   'title' => 'FitTrack — Full Project', 'price' => 49, 'type' => 'code_example', 'desc' => 'Working code, end to end. Clone, deploy, ship.'],
                ['tier' => 'bundle', 'title' => 'FitTrack — Bundle',       'price' => 59, 'type' => 'code_example', 'desc' => 'Prompt + design + full project, discounted.'],
            ]
        );
    }
}
