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

    private function reviewed(string $content, string $createdAt): Post
    {
        return Post::factory()->create([
            'status' => 'draft',
            'series_title' => null,
            'moderation_status' => 'approved',
            'content' => $content,
            'published_at' => null,
            'created_at' => $createdAt,
            'quality_report' => ['passed' => true],
        ]);
    }

    public function test_eng_eski_darvozadan_otgan_draft_birinchi_chiqadi(): void
    {
        $new = $this->reviewed($this->cleanContent(), now()->subDay()->toDateTimeString());
        $old = $this->reviewed($this->cleanContent(1700), now()->subDays(9)->toDateTimeString());

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('published', $old->fresh()->status);
        $this->assertSame('draft', $new->fresh()->status);
    }

    public function test_kuniga_ikkitadan_ortiq_va_5_soatdan_tez_nashr_qilinmaydi(): void
    {
        $a = $this->reviewed($this->cleanContent(), now()->subDays(3)->toDateTimeString());
        $b = $this->reviewed($this->cleanContent(1700), now()->subDays(2)->toDateTimeString());
        $c = $this->reviewed($this->cleanContent(1800), now()->subDay()->toDateTimeString());

        $this->artisan('content:drip')->assertSuccessful();
        $this->assertSame('published', $a->fresh()->status);

        // darhol qayta ishga tushsa: oraliq 5 soatdan kam
        $this->artisan('content:drip')->assertSuccessful();
        $this->assertSame('draft', $b->fresh()->status);

        // 6 soat o'tdi -> ikkinchisi chiqadi
        $a->fresh()->update(['published_at' => now()->startOfDay()->subHours(6)]);
        $this->artisan('content:drip')->assertSuccessful();
        $this->assertSame('published', $b->fresh()->status);
    }

    public function test_kunlik_chegara_toldi(): void
    {
        $x = Post::factory()->create(['status' => 'published', 'series_title' => null, 'published_at' => now()->subHours(7)]);
        $y = Post::factory()->create(['status' => 'published', 'series_title' => null, 'published_at' => now()->subHours(6)]);
        $c = $this->reviewed($this->cleanContent(), now()->subDay()->toDateTimeString());

        $this->travelTo(now()->startOfDay()->addHours(20));
        $x->update(['published_at' => now()->subHours(7)]);
        $y->update(['published_at' => now()->subHours(6)]);

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('draft', $c->fresh()->status);
    }

    public function test_news_brief_drip_ni_bloklamaydi(): void
    {
        Post::factory()->create(['status' => 'published', 'published_at' => now(), 'post_type' => Post::TYPE_NEWS_BRIEF]);
        $d = $this->reviewed($this->cleanContent(), now()->subDay()->toDateTimeString());

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('published', $d->fresh()->status);
    }

    public function test_toza_draft_nashr_qilinadi(): void
    {
        $post = $this->draft($this->cleanContent());

        $this->artisan('content:drip')->assertSuccessful();

        $this->assertSame('published', $post->fresh()->status);
    }
}
