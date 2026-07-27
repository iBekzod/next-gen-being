<?php

namespace Tests\Feature\Marketplace;

use App\Models\MarketplaceListing;
use Database\Seeders\FitTrackListingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FitTrackSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_one_published_listing_with_tiers_and_is_idempotent(): void
    {
        Storage::fake('public');

        (new FitTrackListingSeeder())->run();
        (new FitTrackListingSeeder())->run(); // idempotent

        $listing = MarketplaceListing::where('slug', 'fittrack-workout-saas')->first();
        $this->assertNotNull($listing);
        $this->assertSame('published', $listing->status);
        $this->assertGreaterThanOrEqual(3, $listing->tiers()->count());
        $this->assertSame(1, MarketplaceListing::where('slug', 'fittrack-workout-saas')->count());
        Storage::disk('public')->assertExists('demos/fittrack-workout-saas/index.html');
    }

    public function test_linkfolio_seeds_with_demo_and_tiers(): void
    {
        Storage::fake('public');

        (new \Database\Seeders\LinkFolioListingSeeder())->run();

        $listing = MarketplaceListing::where('slug', 'linkfolio-dev-portfolio')->first();
        $this->assertNotNull($listing);
        $this->assertSame('landing', $listing->category);
        $this->assertSame('published', $listing->status);
        $this->assertGreaterThanOrEqual(3, $listing->tiers()->count());
        Storage::disk('public')->assertExists('demos/linkfolio-dev-portfolio/index.html');
    }

    public function test_all_first_party_seeders_produce_published_listings_with_demos(): void
    {
        Storage::fake('public');

        $map = [
            \Database\Seeders\FitTrackListingSeeder::class => 'fittrack-workout-saas',
            \Database\Seeders\LinkFolioListingSeeder::class => 'linkfolio-dev-portfolio',
            \Database\Seeders\NebulaListingSeeder::class    => 'nebula-analytics-saas',
            \Database\Seeders\HaloListingSeeder::class      => 'halo-ai-writer',
        ];

        foreach ($map as $seeder => $slug) {
            (new $seeder())->run();
            $listing = MarketplaceListing::where('slug', $slug)->first();
            $this->assertNotNull($listing, "seeder {$seeder} produced no listing");
            $this->assertSame('published', $listing->status);
            $this->assertGreaterThanOrEqual(3, $listing->tiers()->count());
            Storage::disk('public')->assertExists("demos/{$slug}/index.html");
        }
    }
}
