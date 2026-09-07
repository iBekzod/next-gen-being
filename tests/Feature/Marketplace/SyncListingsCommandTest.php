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

    public function test_prompt_tier_bepul_code_tier_nashrdan_olingan(): void
    {
        User::factory()->create();
        $this->artisan('marketplace:sync-listings')->assertSuccessful();

        $prompts = \App\Models\DigitalProduct::where('tier', 'prompt')->get();
        $this->assertNotEmpty($prompts, 'prompt tier topilmadi');

        foreach ($prompts as $prompt) {
            $this->assertTrue((bool) $prompt->is_free, "prompt tier bepul emas: {$prompt->id}");
            $this->assertSame('published', $prompt->status);
            $this->assertNotNull($prompt->file_path, 'prompt tier deliverable yo\'q');
        }

        foreach (\App\Models\DigitalProduct::where('tier', 'code')->get() as $code) {
            $this->assertSame('archived', $code->status, 'code tier hamon nashrda');
        }

        // design va bundle o'zgarmasligi kerak
        foreach (\App\Models\DigitalProduct::whereIn('tier', ['design', 'bundle'])->get() as $paid) {
            $this->assertFalse((bool) $paid->is_free, "{$paid->tier} bepul bo'lib qolgan");
            $this->assertSame('published', $paid->status);
        }
    }
}
