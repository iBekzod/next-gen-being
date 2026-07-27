<?php

namespace Tests\Feature\Marketplace;

use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceListingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_listing_and_resolves_by_slug(): void
    {
        $seller = User::factory()->create();

        $listing = MarketplaceListing::create([
            'seller_id'    => $seller->id,
            'title'        => 'FitTrack Workout SaaS',
            'slug'         => 'fittrack-workout-saas',
            'tagline'      => 'Ship a fitness SaaS in a day',
            'description'  => 'A production-ready fitness app.',
            'category'     => 'saas',
            'tags'         => ['laravel', 'saas', 'fitness'],
            'demo_type'    => 'static',
            'demo_path'    => 'demos/fittrack-workout-saas/index.html',
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('marketplace_listings', ['slug' => 'fittrack-workout-saas']);
        $this->assertIsArray($listing->tags);
        $this->assertSame('laravel', $listing->tags[0]);
        $this->assertSame('slug', (new MarketplaceListing())->getRouteKeyName());
    }

    public function test_published_scope_only_returns_published(): void
    {
        $seller = User::factory()->create();
        $published = MarketplaceListing::factory()->for($seller, 'seller')
            ->create(['status' => 'published', 'published_at' => now()]);
        $draft = MarketplaceListing::factory()->for($seller, 'seller')
            ->create(['status' => 'draft', 'published_at' => null]);

        // Count-robust: survives any globally seeded listings.
        $ids = MarketplaceListing::published()->pluck('id');
        $this->assertTrue($ids->contains($published->id));
        $this->assertFalse($ids->contains($draft->id));
    }

    public function test_tiers_are_grouped_and_ordered_by_price(): void
    {
        $seller = User::factory()->create();
        $listing = MarketplaceListing::factory()->for($seller, 'seller')->create();

        \App\Models\DigitalProduct::factory()->create([
            'creator_id' => $seller->id, 'listing_id' => $listing->id,
            'tier' => 'code', 'price' => 49,
        ]);
        \App\Models\DigitalProduct::factory()->create([
            'creator_id' => $seller->id, 'listing_id' => $listing->id,
            'tier' => 'prompt', 'price' => 5,
        ]);

        $tiers = $listing->tiers()->get();
        $this->assertCount(2, $tiers);
        $this->assertSame('prompt', $tiers->first()->tier);   // cheapest first
        $this->assertEqualsWithDelta(5.0, $listing->cheapestPrice(), 0.001);
    }
}
