<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsMarketplaceListing;
use Illuminate\Database\Seeder;

class AscendListingSeeder extends Seeder
{
    use SeedsMarketplaceListing;

    public function run(): void
    {
        $this->seedMarketplaceListing(
            [
                'slug'        => 'ascend-saas-landing',
                'title'       => 'Ascend — SaaS Marketing Landing',
                'tagline'     => 'A high-converting SaaS landing, fully wired',
                'description' => "A polished, conversion-focused SaaS marketing landing page.\n\n".
                                 "Gradient hero with animated stat counters, feature grid, a live ".
                                 "monthly/yearly pricing toggle, FAQ accordion, and trust logos. ".
                                 "Pure HTML/CSS/JS, no build step, swap the copy and launch.",
                'category'    => 'landing',
                'tags'        => ['landing', 'saas', 'pricing', 'marketing'],
                'rating'      => 4.9,
                'reviews_count' => 54,
                'sales_count' => 54,
            ],
            [
                ['tier' => 'prompt', 'title' => 'Ascend — Prompt Plan', 'price' => 5,  'type' => 'prompt',   'desc' => 'Prompts to generate the full landing with Claude Code.'],
                ['tier' => 'design', 'title' => 'Ascend — Full Design', 'price' => 10, 'type' => 'template', 'desc' => 'The complete landing page — the exact demo above.'],
                ['tier' => 'bundle', 'title' => 'Ascend — Bundle',      'price' => 13, 'type' => 'template', 'desc' => 'Prompts + full design, discounted.'],
            ]
        );
    }
}
