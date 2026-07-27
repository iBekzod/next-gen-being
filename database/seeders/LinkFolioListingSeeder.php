<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsMarketplaceListing;
use Illuminate\Database\Seeder;

class LinkFolioListingSeeder extends Seeder
{
    use SeedsMarketplaceListing;

    public function run(): void
    {
        $this->seedMarketplaceListing(
            [
                'slug'        => 'linkfolio-dev-portfolio',
                'title'       => 'LinkFolio — Developer Portfolio',
                'tagline'     => 'A striking one-page portfolio, ready in minutes',
                'description' => "A polished, single-page developer portfolio landing.\n\n".
                                 "Animated hero, filterable project grid, and a clean contact section. ".
                                 "Pure HTML/CSS/JS, zero build step, drop in your details and deploy anywhere.",
                'category'    => 'landing',
                'tags'        => ['landing', 'portfolio', 'html', 'css'],
                'rating'      => 4.8,
                'reviews_count' => 41,
                'sales_count' => 41,
            ],
            [
                ['tier' => 'prompt', 'title' => 'LinkFolio — Prompt Plan', 'price' => 4,  'type' => 'prompt',   'desc' => 'The Claude Code prompts to generate this portfolio.'],
                ['tier' => 'design', 'title' => 'LinkFolio — Full Design', 'price' => 9,  'type' => 'template', 'desc' => 'The complete HTML/CSS/JS — the exact demo above.'],
                ['tier' => 'bundle', 'title' => 'LinkFolio — Bundle',      'price' => 12, 'type' => 'template', 'desc' => 'Prompts + full design, discounted.'],
            ]
        );
    }
}
