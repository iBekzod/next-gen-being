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

    public function test_design_and_prompt_tiers_get_real_deliverables(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        (new \Database\Seeders\FitTrackListingSeeder())->run();

        $listing = MarketplaceListing::where('slug', 'fittrack-workout-saas')->first();
        $design = $listing->tiers()->where('tier', 'design')->first();
        $prompt = $listing->tiers()->where('tier', 'prompt')->first();
        $bundle = $listing->tiers()->where('tier', 'bundle')->first();
        $code   = $listing->tiers()->where('tier', 'code')->first();

        $this->assertNotEmpty($design->file_path, 'design tier should have a deliverable');
        $this->assertNotEmpty($prompt->file_path, 'prompt tier should have a deliverable');
        $this->assertNotEmpty($bundle->file_path, 'bundle tier should ship a zip');
        $this->assertStringEndsWith('.zip', $bundle->file_path);
        $this->assertNull($code->file_path, 'code (full project) tier has no deliverable yet');

        Storage::disk('private')->assertExists($design->file_path);
        Storage::disk('private')->assertExists($prompt->file_path);
        Storage::disk('private')->assertExists($bundle->file_path);
    }

    public function test_landing_packs_seeder_creates_products_with_demos_and_bundles(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        (new \Database\Seeders\LandingPacksSeeder())->run();

        $manifest = database_path('seeders/landing-packs.json');
        $expected = json_decode(file_get_contents($manifest), true) ?: [];
        $this->assertGreaterThanOrEqual(6, count($expected));

        foreach ($expected as $p) {
            $listing = MarketplaceListing::where('slug', $p['slug'])->first();
            $this->assertNotNull($listing, "missing listing for {$p['slug']}");
            $this->assertSame('published', $listing->status);
            Storage::disk('public')->assertExists("demos/{$p['slug']}/index.html");
            $bundle = $listing->tiers()->where('tier', 'bundle')->first();
            $this->assertNotEmpty($bundle->file_path, "bundle missing for {$p['slug']}");
        }
    }

    public function test_all_first_party_seeders_produce_published_listings_with_demos(): void
    {
        Storage::fake('public');
        Storage::fake('private');

        $map = [
            \Database\Seeders\FitTrackListingSeeder::class => 'fittrack-workout-saas',
            \Database\Seeders\LinkFolioListingSeeder::class => 'linkfolio-dev-portfolio',
            \Database\Seeders\NebulaListingSeeder::class    => 'nebula-analytics-saas',
            \Database\Seeders\HaloListingSeeder::class      => 'halo-ai-writer',
            \Database\Seeders\AscendListingSeeder::class    => 'ascend-saas-landing',
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
