<?php

namespace Tests\Feature\Newsletter;

use App\Models\NewsletterCampaign;
use App\Models\Post;
use App\Services\NewsletterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyDigestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // DatabaseSeeder (via $seed = true on the base TestCase) seeds
        // FeedDemoSeeder posts with published_at = now()->subHours(rand(6, 72)),
        // which land inside generateDailyDigest()'s 24-hour window roughly half
        // the time. Left in place, that makes the digest's "top 5 by recency"
        // selection nondeterministic and can bump this test's own post out of
        // the result. Clear them so the digest content is driven only by what
        // each test sets up. Same pattern as ContentHealthCheckTest::setUp()
        // and ContentDripPublishTest::setUp().
        Post::where('status', 'published')->delete();
    }

    public function test_kunlik_digest_kampaniya_yaratadi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subHours(3),
            'title' => 'Bugungi maqola sarlavhasi',
        ]);

        $campaign = app(NewsletterService::class)->generateDailyDigest();

        $this->assertInstanceOf(NewsletterCampaign::class, $campaign);
        $this->assertSame('digest', $campaign->type);
        $this->assertStringContainsString('Bugungi maqola sarlavhasi', $campaign->content);
    }

    public function test_aytadigan_narsa_bolmasa_digest_yaratilmaydi(): void
    {
        Post::where('status', 'published')->delete();

        $this->assertNull(app(NewsletterService::class)->generateDailyDigest());
    }

    public function test_buyruq_bosh_digestda_hech_narsa_yubormaydi(): void
    {
        Post::where('status', 'published')->delete();

        $this->artisan('newsletter:send-daily')
            ->expectsOutputToContain('yuboriladigan')
            ->assertSuccessful();

        $this->assertSame(0, NewsletterCampaign::count());
    }
}
