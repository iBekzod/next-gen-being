<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsMarketplaceListing;
use Illuminate\Database\Seeder;

/**
 * Seeds the agent-generated premium WebGL landing-page products.
 * Each product's demo lives in seeders/demos/{slug}.html and its build-prompt
 * doc in seeders/deliverables/{slug}-prompt.md; metadata comes from the
 * landing-packs.json manifest.
 */
class LandingPacksSeeder extends Seeder
{
    use SeedsMarketplaceListing;

    public function run(): void
    {
        $manifest = database_path('seeders/landing-packs.json');
        if (! is_file($manifest)) {
            return;
        }

        $products = json_decode(file_get_contents($manifest), true) ?: [];

        foreach ($products as $p) {
            $name = $p['title'];
            $slug = $p['slug'];

            $this->seedMarketplaceListing(
                [
                    'slug'          => $slug,
                    'title'         => $p['title'],
                    'tagline'       => $p['tagline'],
                    'description'   => $p['description'],
                    'category'      => $p['category'] ?? 'landing',
                    'tags'          => $p['tags'] ?? [],
                    'rating'        => 4.9,
                    'reviews_count' => random_int(6, 40),
                    'sales_count'   => random_int(6, 40),
                ],
                [
                    ['tier' => 'prompt', 'title' => $name.' — Prompt Plan', 'price' => $p['price_prompt'], 'type' => 'prompt',   'desc' => 'The Claude Code prompts to build this exact page.'],
                    ['tier' => 'design', 'title' => $name.' — Full Design', 'price' => $p['price_design'], 'type' => 'template', 'desc' => 'The complete, self-contained WebGL page — the exact demo above.'],
                    ['tier' => 'bundle', 'title' => $name.' — Bundle',      'price' => $p['price_bundle'], 'type' => 'template', 'desc' => 'Prompts + full design, packaged as a zip.'],
                ]
            );
        }
    }
}
