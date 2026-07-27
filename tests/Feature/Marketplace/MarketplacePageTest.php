<?php

namespace Tests\Feature\Marketplace;

use App\Models\DigitalProduct;
use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplacePageTest extends TestCase
{
    use RefreshDatabase;

    private function publishedListing(): MarketplaceListing
    {
        $seller = User::factory()->create();
        $listing = MarketplaceListing::factory()->for($seller, 'seller')->create([
            'title' => 'FitTrack Workout SaaS', 'status' => 'published', 'published_at' => now(),
        ]);
        DigitalProduct::factory()->tier('prompt', 5)->create(['creator_id' => $seller->id, 'listing_id' => $listing->id]);
        DigitalProduct::factory()->tier('design', 7)->create(['creator_id' => $seller->id, 'listing_id' => $listing->id]);

        return $listing;
    }

    public function test_index_lists_published_listings(): void
    {
        $this->publishedListing();
        MarketplaceListing::factory()->create(['status' => 'draft', 'published_at' => null]); // hidden

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertSee('FitTrack Workout SaaS');
        $response->assertSee('from $5'); // cheapest tier price
    }

    public function test_show_renders_demo_and_tiers(): void
    {
        $listing = $this->publishedListing();

        $response = $this->get(route('marketplace.show', $listing));

        $response->assertStatus(200);
        $response->assertSee('FitTrack Workout SaaS');
        $response->assertSee('$5');
        $response->assertSee('$7');
    }

    public function test_show_404_for_unpublished(): void
    {
        $listing = MarketplaceListing::factory()->create(['status' => 'draft', 'published_at' => null]);
        $this->get(route('marketplace.show', $listing))->assertStatus(404);
    }
}
