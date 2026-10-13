<?php

namespace Tests\Feature\Content;

use App\Models\Category;
use App\Models\OutboundClick;
use App\Models\Post;
use App\Models\User;
use App\Services\Content\AffiliateLinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateLinkingTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://nextgenbeing.com';

    private function programs(): array
    {
        return [
            'elevenlabs' => ['patterns' => ['elevenlabs', 'eleven labs'], 'anchor' => 'ElevenLabs', 'url' => 'https://try.elevenlabs.io/x'],
            'murf' => ['patterns' => ['murf.ai', 'murf ai'], 'anchor' => 'Murf AI', 'url' => 'https://murf.example/ref'],
        ];
    }

    private function body(): string
    {
        return <<<'MD'
Text to speech with ElevenLabs is easy to start, and this post shows how.

## Setup

First install the SDK. ElevenLabs has a free tier. The `elevenlabs` package is on PyPI.

```python
# ElevenLabs example
from elevenlabs import ElevenLabs
```

| Tool | Price |
|---|---|
| ElevenLabs | $5 |

See the [ElevenLabs docs](https://elevenlabs.io/docs) for more. Murf AI is mentioned once only.

## Next steps

Pick a voice.
MD;
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::factory()->create(array_merge([
            'author_id' => User::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
            'title' => 'Text to speech with ElevenLabs',
            'status' => 'published',
            'post_type' => 'article',
            'content' => $this->body(),
        ], $overrides));
    }

    public function test_links_first_prose_mention_only_and_skips_code_tables_headings_links(): void
    {
        $r = (new AffiliateLinker())->apply('Text to speech', $this->body(), $this->programs(), self::BASE);
        $c = $r['content'];

        $this->assertSame(['elevenlabs'], $r['added']);
        $this->assertStringContainsString('with [ElevenLabs](https://nextgenbeing.com/go/elevenlabs) is easy', $c);
        $this->assertSame(2, substr_count($c, '/go/elevenlabs'), 'one inline link + one CTA link');
        $this->assertStringContainsString('# ElevenLabs example', $c);
        $this->assertStringContainsString('| ElevenLabs | $5 |', $c);
        $this->assertStringContainsString('[ElevenLabs docs](https://elevenlabs.io/docs)', $c);
        $this->assertStringNotContainsString('/go/murf', $c, 'a single passing mention is not linked');
    }

    public function test_adds_one_cta_before_closing_section_and_the_disclosure(): void
    {
        $r = (new AffiliateLinker())->apply('Text to speech', $this->body(), $this->programs(), self::BASE);
        $c = $r['content'];

        $this->assertTrue($r['cta']);
        $this->assertTrue($r['disclosure']);
        $this->assertSame(1, substr_count($c, AffiliateLinker::CTA_MARKER));
        $this->assertLessThan(strpos($c, '## Next steps'), strpos($c, AffiliateLinker::CTA_MARKER));
        $this->assertStringContainsString('affiliate-disclosure', $c);
        $this->assertLessThan(strpos($c, '## Setup'), strpos($c, 'affiliate-disclosure'));
    }

    public function test_is_idempotent(): void
    {
        $linker = new AffiliateLinker();
        $first = $linker->apply('Text to speech', $this->body(), $this->programs(), self::BASE);
        $second = $linker->apply('Text to speech', $first['content'], $this->programs(), self::BASE);

        $this->assertFalse($second['changed']);
        $this->assertSame($first['content'], $second['content']);
    }

    public function test_respects_link_cap(): void
    {
        $programs = [];
        $content = "Intro.\n\n";
        foreach (['aa', 'bb', 'cc', 'dd', 'ee'] as $s) {
            $programs[$s] = ['patterns' => ["tool{$s}"], 'anchor' => "Tool{$s}", 'url' => 'https://x'];
            $content .= "tool{$s} is good. tool{$s} again.\n\n";
        }
        $r = (new AffiliateLinker())->apply('t', $content, $programs, self::BASE, 3);

        $this->assertCount(3, $r['added']);
        $this->assertCount(3, (new AffiliateLinker())->existingSlugs($r['content']));
    }

    public function test_no_candidates_means_no_cta_or_disclosure(): void
    {
        $r = (new AffiliateLinker())->apply('Laravel queues', "Nothing relevant here.\n\n## A\n\nText.\n", $this->programs(), self::BASE);
        $this->assertFalse($r['changed']);
    }

    public function test_command_dry_run_saves_nothing_and_real_run_saves(): void
    {
        config(['affiliate.links.elevenlabs.url' => 'https://try.elevenlabs.io/x']);
        $post = $this->makePost();

        $this->artisan('revenue:insert-affiliate-links', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame($this->body(), $post->fresh()->content);

        $this->artisan('revenue:insert-affiliate-links')->assertSuccessful();
        $this->assertStringContainsString('/go/elevenlabs', $post->fresh()->content);
        $this->assertSame('published', $post->fresh()->status);
    }

    public function test_command_skips_programs_without_referral_url(): void
    {
        config(['affiliate.links.elevenlabs.url' => null]);
        $post = $this->makePost();
        $this->artisan('revenue:insert-affiliate-links')->assertSuccessful();
        $this->assertSame($this->body(), $post->fresh()->content);
    }

    public function test_stats_endpoint_reports_clicks_per_slug_and_top_posts(): void
    {
        config(['services.blog_bot.secret' => 'sekret']);
        $post = $this->makePost(['title' => 'Popular post']);
        foreach ([1, 2, 3] as $i) {
            OutboundClick::create(['slug' => 'elevenlabs', 'post_id' => $post->id, 'is_affiliate' => true, 'ip_hash' => "h{$i}"]);
        }
        OutboundClick::create(['slug' => 'murf', 'post_id' => null, 'is_affiliate' => false, 'ip_hash' => 'h9']);

        $body = json_encode(['days' => 7]);
        $ts = now()->toIso8601String();
        $res = $this->call('POST', '/api/bot/affiliate-stats', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOT_TIMESTAMP' => $ts,
            'HTTP_X_BOT_SIGNATURE' => hash_hmac('sha256', $ts . '.' . $body, 'sekret'),
        ], $body);

        $res->assertOk();
        $this->assertSame(4, $res->json('summary.total_clicks'));
        $this->assertSame(3, $res->json('summary.earning_clicks'));
        $this->assertSame('elevenlabs', $res->json('summary.per_slug.0.slug'));
        $this->assertSame($post->id, $res->json('summary.top_posts.0.post_id'));
        $this->assertNull($res->json('summary.conversions'));
        $this->assertStringContainsString('clicks only', strtolower($res->json('text')));

        $this->postJson('/api/bot/affiliate-stats', ['days' => 7])->assertStatus(401);
    }
}
