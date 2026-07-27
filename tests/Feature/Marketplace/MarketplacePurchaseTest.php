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
}
