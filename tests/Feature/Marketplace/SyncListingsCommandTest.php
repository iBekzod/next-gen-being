<?php

namespace Tests\Feature\Marketplace;

use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncListingsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyruq_barcha_listinglarni_yaratadi(): void
    {
        User::factory()->create();
        MarketplaceListing::query()->delete();

        $this->artisan('marketplace:sync-listings')->assertSuccessful();

        $slugs = MarketplaceListing::pluck('slug');

        foreach (['fittrack-workout-saas', 'linkfolio-dev-portfolio', 'nebula-analytics-saas',
                  'halo-ai-writer', 'ascend-saas-landing'] as $slug) {
            $this->assertContains($slug, $slugs->all(), "Listing yetishmayapti: {$slug}");
        }

        $this->assertGreaterThanOrEqual(11, $slugs->count());
    }

    public function test_buyruq_idempotent(): void
    {
        User::factory()->create();

        $this->artisan('marketplace:sync-listings')->assertSuccessful();
        $first = MarketplaceListing::count();

        $this->artisan('marketplace:sync-listings')->assertSuccessful();

        $this->assertSame($first, MarketplaceListing::count());
    }
}
