<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesGateContent;
use Tests\TestCase;

class ContentHealthCheckTest extends TestCase
{
    use RefreshDatabase;
    use MakesGateContent;

    protected function setUp(): void
    {
        parent::setUp();

        // DatabaseSeeder (via $seed = true on the base TestCase) seeds several
        // published, non-tutorial posts via ContentSeeder and FeedDemoSeeder
        // (FeedDemoSeeder's are always published_at = now()->subHours(rand(6,72)),
        // i.e. always < 3 days old). Left in place, those posts satisfy the
        // cadence checks in content:health-check regardless of what this test
        // sets up, making every assertion here pass/fail for the wrong reason.
        // Clear them so staleness/backlog checks are driven only by each test's
        // own fixtures. Same pattern as ContentDripPublishTest::setUp().
        Post::where('status', 'published')->whereNull('series_title')->delete();
    }

    public function test_eski_korpus_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'series_title' => null,
            'published_at' => now()->subDays(30),
            'content' => $this->cleanContent(),
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('stale_posts')
            ->assertExitCode(1);
    }

    public function test_nashrga_tayyor_draft_yoqligi_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'published',
            'series_title' => null,
            'published_at' => now(),
            'content' => $this->cleanContent(),
        ]);
        Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'approved',
            'content' => 'Juda qisqa draft.',
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('empty_publishable_backlog')
            ->assertExitCode(1);
    }

    public function test_kutilayotgan_moderatsiya_ogohlantiradi(): void
    {
        Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'pending',
            'content' => $this->cleanContent(),
        ]);

        $this->artisan('content:health-check')
            ->expectsOutputToContain('unattended_moderation')
            ->assertExitCode(1);
    }
}
