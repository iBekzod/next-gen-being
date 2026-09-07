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
        // The prompt tier is a free lead magnet: it shows an email-collection
        // form instead of a price, so it is asserted via the form marker
        // rather than "$5" (see PromptGateTest for the gate behaviour itself).
        $response->assertSee(route('marketplace.prompt.request', $listing), false);
        $response->assertSee('$7');
    }

    public function test_show_404_for_unpublished(): void
    {
        $listing = MarketplaceListing::factory()->create(['status' => 'draft', 'published_at' => null]);
        $this->get(route('marketplace.show', $listing))->assertStatus(404);
    }

    public function test_show_page_emits_product_schema(): void
    {
        $listing = $this->publishedListing(); // prompt $5 + design $7 tiers

        $response = $this->get(route('marketplace.show', $listing));

        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Product"', false);
        $response->assertSee('"lowPrice":"5.00"', false);
        $response->assertSee('"highPrice":"7.00"', false);
    }

    /**
     * The JSON-LD must describe what the page actually offers: the archived
     * `code` tier is not an offer, and the free prompt tier costs 0, not the
     * $5 its `price` column still carries.
     */
    public function test_schema_prices_only_published_tiers_and_zeroes_free_ones(): void
    {
        User::factory()->create();
        $this->artisan('marketplace:sync-listings');
        $listing = MarketplaceListing::where('slug', 'fittrack-workout-saas')->firstOrFail();

        $offers = $this->productOffers($this->get(route('marketplace.show', $listing))->getContent());

        $published = $listing->tiers()->where('status', 'published')->count();

        $this->assertSame('0.00', $offers['lowPrice'], 'bepul prompt hali ham narx e\'lon qilyapti');
        $this->assertSame($published, $offers['offerCount']);
        $this->assertLessThan(
            $listing->tiers()->count(),
            $offers['offerCount'],
            'arxivlangan tier offerCount ni shishiryapti'
        );
    }

    /**
     * Pull the AggregateOffer out of the page's Product ld+json block.
     *
     * The layout emits its own Organization block first, so the Product one has
     * to be picked out by @type rather than by position.
     */
    private function productOffers(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        foreach ($m[1] as $json) {
            $ld = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            if (($ld['@type'] ?? null) === 'Product') {
                $this->assertArrayHasKey('offers', $ld, 'Product JSON-LD da offers yo\'q');

                return $ld['offers'];
            }
        }

        $this->fail('sahifada Product JSON-LD topilmadi');
    }

    public function test_sitemap_includes_marketplace(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
        $listing = $this->publishedListing();

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee(route('marketplace.index'), false);
        $response->assertSee(route('marketplace.show', $listing), false);
    }
}
