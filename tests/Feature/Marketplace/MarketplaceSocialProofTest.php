<?php

namespace Tests\Feature\Marketplace;

use App\Models\DigitalProduct;
use App\Models\MarketplaceListing;
use App\Models\ProductPurchase;
use App\Models\User;
use App\Services\DigitalProductService;
use Database\Seeders\AscendListingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceSocialProofTest extends TestCase
{
    use RefreshDatabase;

    private function listingWithTier(array $listingAttrs = []): array
    {
        $seller = User::factory()->create();
        $listing = MarketplaceListing::factory()->for($seller, 'seller')->create(array_merge(['title' => 'Proof Listing'], $listingAttrs));
        $tier = DigitalProduct::factory()->tier('design', 9)->create(['creator_id' => $seller->id, 'listing_id' => $listing->id]);

        return [$listing, $tier];
    }

    public function test_seeders_do_not_invent_social_proof(): void
    {
        User::factory()->create();
        (new AscendListingSeeder())->run();

        $listing = MarketplaceListing::where('slug', 'ascend-saas-landing')->firstOrFail();
        $this->assertSame(0, (int) $listing->sales_count);
        $this->assertSame(0, (int) $listing->reviews_count);
        $this->assertSame(0.0, (float) $listing->rating);
    }

    public function test_pages_hide_rating_and_sales_when_zero(): void
    {
        [$listing] = $this->listingWithTier();

        $index = $this->get(route('marketplace.index'))->assertOk();
        $index->assertSee('New');
        $index->assertDontSee('0 sales');
        $index->assertDontSee('★');

        $show = $this->get(route('marketplace.show', $listing))->assertOk();
        $show->assertDontSee('aggregateRating', false);
        $show->assertDontSee('0 sales');
    }

    public function test_real_sales_show_and_reviews_stay_hidden_without_reviews(): void
    {
        [$listing] = $this->listingWithTier(['sales_count' => 3]);

        $this->get(route('marketplace.index'))->assertSee('3 sales')->assertDontSee('★');
        $this->get(route('marketplace.show', $listing))->assertDontSee('aggregateRating', false);
    }

    public function test_aggregate_rating_emitted_only_with_real_reviews(): void
    {
        [$listing] = $this->listingWithTier(['rating' => 4.5, 'reviews_count' => 2]);

        $this->get(route('marketplace.show', $listing))->assertSee('aggregateRating', false);
    }

    public function test_completed_purchase_updates_sales_count(): void
    {
        [$listing, $tier] = $this->listingWithTier(['sales_count' => 40, 'rating' => 4.9, 'reviews_count' => 40]);
        $buyer = User::factory()->create();

        $payload = fn (string $orderId) => [
            'meta' => ['custom_data' => ['product_id' => $tier->id, 'user_id' => $buyer->id]],
            'data' => ['id' => $orderId, 'attributes' => ['total' => 900, 'urls' => ['receipt' => null]]],
        ];

        app(DigitalProductService::class)->processPurchase($payload('ord-1'));
        $this->assertSame(1, (int) $listing->fresh()->sales_count); // fake 40 replaced by real count

        app(DigitalProductService::class)->processPurchase($payload('ord-2'));
        $this->assertSame(2, (int) $listing->fresh()->sales_count);
        $this->assertSame(0, (int) $listing->fresh()->reviews_count);
        $this->assertSame(2, ProductPurchase::count());
    }

    public function test_recompute_command_zeroes_fake_numbers(): void
    {
        [$listing] = $this->listingWithTier(['sales_count' => 54, 'rating' => 4.9, 'reviews_count' => 54]);

        $this->artisan('marketplace:recompute-stats')->assertSuccessful();

        $listing->refresh();
        $this->assertSame([0, 0, 0.0], [(int) $listing->sales_count, (int) $listing->reviews_count, (float) $listing->rating]);
    }
}
