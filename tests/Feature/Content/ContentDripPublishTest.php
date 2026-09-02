<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use App\Models\User;
use App\Services\Content\PublishGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesGateContent;
use Tests\TestCase;

class ContentDripPublishTest extends TestCase
{
    use RefreshDatabase;
    use MakesGateContent;

    protected function setUp(): void
    {
        parent::setUp();

        // DatabaseSeeder (via $seed = true on the base TestCase) seeds a
        // "regular" (non-tutorial) published post dated within the last few
        // days. That satisfies the POST_INTERVAL_DAYS cadence check before
        // this test's draft is even considered, which would make these
        // assertions pass/fail for the wrong reason (cadence, not the
        // quality gate under test). Clear it so isDue() for regular posts
        // is driven only by what each test sets up.
        Post::where('status', 'published')->whereNull('series_title')->delete();
    }

    private function draft(string $content, string $moderation = 'approved'): Post
    {
        return Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => $moderation,
            'content' => $content,
            'published_at' => null,
        ]);
    }

    public function test_takrorlanuvchi_draft_nashr_qilinmaydi(): void
    {
        $repeat = 'Bu jumla ataylab bir necha marta takrorlanadi va altmish belgidan uzunroq.';
        $post = $this->draft($this->cleanContent() . ' ' . str_repeat($repeat . ' ', 5));

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('draft', $post->fresh()->status);
    }

    public function test_toza_draft_nashr_qilinadi(): void
    {
        $post = $this->draft($this->cleanContent());

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('published', $post->fresh()->status);
    }
}
