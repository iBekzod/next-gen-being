<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsMarketplaceListing;
use Illuminate\Database\Seeder;

class NebulaListingSeeder extends Seeder
{
    use SeedsMarketplaceListing;

    public function run(): void
    {
        $this->seedMarketplaceListing(
            [
                'slug'        => 'nebula-analytics-saas',
                'title'       => 'Nebula — Revenue Analytics SaaS',
                'tagline'     => 'A premium analytics dashboard, wired and animated',
                'description' => "A polished revenue-analytics dashboard for any SaaS.\n\n".
                                 "Animated area charts, live KPI cards with sparklines, a donut breakdown, ".
                                 "and a top-products table, all driven by clean, dependency-free code. Dark, ".
                                 "data-dense, and genuinely fast. Drop in your metrics and ship.",
                'category'    => 'saas',
                'tags'        => ['saas', 'dashboard', 'analytics', 'charts'],
                'rating'      => 5.0,
                'reviews_count' => 17,
                'sales_count' => 17,
            ],
            [
                ['tier' => 'prompt', 'title' => 'Nebula — Prompt Plan',   'price' => 6,  'type' => 'prompt',       'desc' => 'The Claude Code build plan + prompts for the whole dashboard.'],
                ['tier' => 'design', 'title' => 'Nebula — Design',        'price' => 12, 'type' => 'template',     'desc' => 'The complete HTML/CSS/JS — the exact animated demo above.'],
                ['tier' => 'code',   'title' => 'Nebula — Full Project',  'price' => 69, 'type' => 'code_example', 'desc' => 'Production dashboard wired to a Laravel API. Clone & deploy.'],
                ['tier' => 'bundle', 'title' => 'Nebula — Bundle',        'price' => 79, 'type' => 'code_example', 'desc' => 'Prompt + design + full project, discounted.'],
            ]
        );
    }
}
