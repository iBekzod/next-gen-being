<?php

namespace Tests\Feature\Marketplace;

use App\Models\DigitalProduct;
use App\Models\MarketplaceListing;
use App\Models\User;
use App\Services\LemonSqueezyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplacePurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_tier_creates_a_completed_purchase_inline(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $listing = MarketplaceListing::factory()->for($seller, 'seller')->create();
        $tier = DigitalProduct::factory()->tier('prompt', 0)->create([
            'creator_id' => $seller->id, 'listing_id' => $listing->id,
            'is_free' => true, 'price' => 0, 'file_path' => 'private/demo.txt',
        ]);

        $this->actingAs($buyer)
            ->post(route('digital-products.purchase', $tier))
            ->assertRedirect();

        $this->assertDatabaseHas('product_purchases', [
            'digital_product_id' => $tier->id, 'user_id' => $buyer->id, 'status' => 'completed',
        ]);
    }

    public function test_paid_tier_redirects_to_lemon_squeezy_checkout(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $listing = MarketplaceListing::factory()->for($seller, 'seller')->create();
        $tier = DigitalProduct::factory()->tier('code', 49)->create([
            'creator_id' => $seller->id, 'listing_id' => $listing->id,
            'lemonsqueezy_variant_id' => '1889316',
            'file_path' => 'deliverables/x/code.zip',
        ]);

        // The tier carries the marketplace 90/10 split.
        $this->assertEqualsWithDelta(90.0, (float) $tier->revenue_share_percentage, 0.001);

        $this->mock(LemonSqueezyService::class, function ($mock) {
            $mock->shouldReceive('createCheckout')->once()->andReturn('https://checkout.example/abc');
        });

        $this->actingAs($buyer)
            ->post(route('digital-products.purchase', $tier))
            ->assertRedirect('https://checkout.example/abc');

        // Paid purchases are recorded later by the webhook, not now.
        $this->assertDatabaseMissing('product_purchases', ['digital_product_id' => $tier->id]);
    }

    public function test_marketplace_tier_without_variant_uses_catch_all_and_custom_price(): void
    {
        config(['services.lemonsqueezy.marketplace_variant_id' => '555000']);

        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $listing = MarketplaceListing::factory()->for($seller, 'seller')->create();
        // A marketplace tier with NO variant id of its own, price $7.
        $tier = DigitalProduct::factory()->tier('design', 7)->create([
            'creator_id' => $seller->id, 'listing_id' => $listing->id,
            'lemonsqueezy_variant_id' => null,
            'file_path' => 'deliverables/x/design.html',
        ]);

        $this->mock(LemonSqueezyService::class, function ($mock) {
            $mock->shouldReceive('createCheckout')
                ->once()
                ->with(\Mockery::on(function ($data) {
                    return ($data['variant_id'] ?? null) === '555000'   // catch-all variant
                        && ($data['custom_price'] ?? null) === 700       // $7 in cents, set by us
                        && isset($data['product_options']['name']);      // tier name shown on checkout
                }))
                ->andReturn('https://checkout.example/dyn');
        });

        $this->actingAs($buyer)
            ->post(route('digital-products.purchase', $tier))
            ->assertRedirect('https://checkout.example/dyn');
    }

    public function test_marketplace_tier_without_a_deliverable_is_not_purchasable(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $listing = MarketplaceListing::factory()->for($seller, 'seller')->create();
        // A code tier with a variant but NO deliverable file yet.
        $tier = DigitalProduct::factory()->tier('code', 49)->create([
            'creator_id' => $seller->id, 'listing_id' => $listing->id,
            'lemonsqueezy_variant_id' => '1889316', 'file_path' => null,
        ]);

        $this->mock(LemonSqueezyService::class, function ($mock) {
            $mock->shouldReceive('createCheckout')->never();
        });

        $this->actingAs($buyer)
            ->post(route('digital-products.purchase', $tier))
            ->assertRedirect(); // bounced back with an info message

        $this->assertDatabaseMissing('product_purchases', ['digital_product_id' => $tier->id]);
    }
}
