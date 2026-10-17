<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use App\Models\User;
use App\Services\Content\NewsBriefGate;
use App\Services\Content\PublishGate;
use App\Services\ContentModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsBriefTest extends TestCase
{
    use RefreshDatabase;

    /** ~260 words, 12 sentences: five of these plus takeaways clear the 1200-word floor. */
    private function longSummary(string $topic = 'the plugin installer'): string
    {
        $one = "Anthropic released a Claude Code update that changes {$topic} for everyone on the stable channel. "
            . 'The official changelog lists the change under version 2.1.301 and links the pull request that introduced it. '
            . 'Developers who script their setup should re-run their bootstrap scripts, because the install path moved and old paths now print a deprecation warning. '
            . 'The release notes also mention a fix for a crash that appeared when two sessions wrote to the same settings file at once, which affected teams sharing a workspace. '
            . 'Nothing in the notes suggests a breaking change to the command line flags, but the changelog is the place to confirm that before upgrading a shared build image. '
            . 'Teams that pin versions in continuous integration can adopt it after a single smoke run of their usual project, since the update changes no file formats.';

        return $one . ' ' . str_replace(['Anthropic', 'changelog'], ['The vendor', 'release notes'], $one);
    }

    private function item(array $over = []): array
    {
        return array_merge([
            'headline' => 'Claude Code ships plugin installs',
            'summary' => $this->longSummary(),
            'source_url' => 'https://example.com/changelog',
            'takeaway' => 'Re-run your bootstrap script on a throwaway machine, check that the install path warning is gone, then pin the new version in CI.',
            'source_name' => 'Example Changelog',
        ], $over);
    }

    private function items(): array
    {
        return [
            $this->item(),
            $this->item(['headline' => 'Small open models dominate local use', 'summary' => $this->longSummary('local inference defaults'),
                'source_url' => 'https://example.org/open-models', 'source_name' => '']),
            $this->item(['headline' => 'MCP spec drops protocol sessions', 'summary' => $this->longSummary('session handling'),
                'source_url' => 'https://example.net/mcp']),
            $this->item(['headline' => 'Embedding model trends on the hub', 'summary' => $this->longSummary('embedding defaults'),
                'source_url' => 'https://example.io/embed']),
            $this->item(['headline' => 'Gemini 4 reportedly nears release', 'summary' => $this->longSummary('model naming'),
                'source_url' => 'https://example.dev/gemini']),
        ];
    }

    private function signedPost(array $payload)
    {
        config(['services.blog_bot.secret' => 'test-secret']);
        $body = json_encode($payload);
        $ts = now()->toIso8601String();

        return $this->call('POST', '/api/bot/news', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_BOT_TIMESTAMP' => $ts,
            'HTTP_X_BOT_SIGNATURE' => hash_hmac('sha256', $ts . '.' . $body, 'test-secret'),
        ], $body);
    }

    private function payload(?array $items = null): array
    {
        return [
            'date' => '2026-10-11',
            'author_id' => User::factory()->create()->id,
            'lead' => 'The week in AI, with the primary source for every item.',
            'featured_image_url' => 'https://images.example.com/cover.jpg',
            'items' => $items ?? $this->items(),
        ];
    }

    private function moderatorApproves(): void
    {
        config(['services.groq.api_key' => 'k']);
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' =>
            '{"passed":true,"score":90,"flags":[],"recommendations":[],"reason":"ok"}']]]])]);
    }

    public function test_gate_accepts_clean_items(): void
    {
        $this->assertSame([], app(NewsBriefGate::class)->itemFailures($this->items()));
    }

    public function test_gate_rejects_missing_source_uzbek_and_bad_length(): void
    {
        $gate = app(NewsBriefGate::class);
        $other = $this->item(['source_url' => 'https://example.org/b']);
        $items = $this->items();

        $this->assertContains('item1:no_valid_source', $gate->itemFailures([
            $this->item(['source_url' => 'http://insecure.example.com/x']), $other,
        ]));
        $this->assertContains('item1:no_valid_source', $gate->itemFailures([
            $this->item(['source_url' => 'https://bit.ly/abc']), $other,
        ]));
        $this->assertContains('item1:not_english', $gate->itemFailures([
            $this->item(['summary' => "Claude Code yangi versiyasi bilan plaginlarni o'rnatish uchun yangilik chiqdi. Bu foydali yangilanish bo'ldi va ham tez. Sizga foydasi shuki, bugun yangilik bilan ishlash oson va tez bo'ldi, lekin manba haqida degan gaplar ham bor, shuning uchun tekshiring."]),
            $other,
        ]));
        $this->assertContains('item1:summary_length', $gate->itemFailures([
            $this->item(['summary' => 'Too short. Really.']), $other,
        ]));
        $this->assertContains('item1:sentence_count', $gate->itemFailures([
            $this->item(['summary' => 'One single long sentence that runs well past the minimum word count without ever stopping to take a breath at all']),
            $other,
        ]));
        $this->assertContains('too_few_items', $gate->itemFailures([$this->item()]));
        $this->assertContains('item1:no_takeaway', $gate->itemFailures([$this->item(['takeaway' => 'Check it.']), $other]));
        // depth floor: four thin items, or items from only two domains
        $thin = fn () => $this->item(['summary' => 'Anthropic shipped an update. It changes the installer. Check the changelog for details on it today.']);
        $this->assertContains('too_shallow', $gate->itemFailures([$thin(), $thin(), $thin(), $thin()]));
        $items[2]['source_url'] = 'https://example.com/other';
        $items[3]['source_url'] = 'https://example.org/other';
        $items[4]['source_url'] = 'https://example.com/third';
        $this->assertContains('too_few_source_domains', $gate->itemFailures($items));
    }

    public function test_endpoint_rejects_bad_signature(): void
    {
        config(['services.blog_bot.secret' => 'test-secret']);
        $this->postJson('/api/bot/news', $this->payload(), ['X-Bot-Timestamp' => now()->toIso8601String(), 'X-Bot-Signature' => 'bad'])
            ->assertStatus(401);
    }

    public function test_endpoint_publishes_short_brief_despite_1500_word_floor(): void
    {
        $this->moderatorApproves();

        $this->signedPost($this->payload())->assertStatus(201)->assertJsonPath('status', 'published');

        $post = Post::where('post_type', Post::TYPE_NEWS_BRIEF)->firstOrFail();
        $this->assertSame('AI News — October 11, 2026', $post->title);
        $this->assertSame('ai-news', $post->category->slug);
        $this->assertGreaterThanOrEqual(NewsBriefGate::MIN_TOTAL_WORDS, str_word_count($post->content));
        $this->assertSame('https://images.example.com/cover.jpg', $post->featured_image);
        $this->assertSame(5, substr_count($post->content, NewsBriefGate::TAKEAWAY_LABEL));
        $this->assertStringContainsString('[Source: Example Changelog](https://example.com/changelog)', $post->content);
        $this->assertStringContainsString('[Source: example.org](https://example.org/open-models)', $post->content);
        $this->assertSame([], app(PublishGate::class)->failures($post));
    }

    public function test_endpoint_is_idempotent_per_date(): void
    {
        $this->moderatorApproves();
        $payload = $this->payload();

        $first = $this->signedPost($payload)->assertStatus(201)->json('post_id');
        $this->signedPost($payload)->assertStatus(200)->assertJsonPath('duplicate', true)->assertJsonPath('post_id', $first);

        $this->assertSame(1, Post::where('post_type', Post::TYPE_NEWS_BRIEF)->count());
    }

    public function test_endpoint_refuses_when_gate_fails(): void
    {
        $this->moderatorApproves();
        $items = $this->items();
        $items[1]['source_url'] = 'https://x';

        $this->signedPost($this->payload($items))->assertStatus(422);
        $this->assertSame(0, Post::where('post_type', Post::TYPE_NEWS_BRIEF)->count());
    }

    public function test_endpoint_refuses_without_cover_image(): void
    {
        $this->moderatorApproves();
        $payload = $this->payload();
        unset($payload['featured_image_url']);

        $this->signedPost($payload)->assertStatus(422)->assertJsonPath('failures.0', 'no_cover_image');
        $this->assertSame(0, Post::where('post_type', Post::TYPE_NEWS_BRIEF)->count());
    }

    public function test_moderator_rejection_keeps_it_a_draft(): void
    {
        config(['services.groq.api_key' => 'k']);
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' =>
            '{"passed":false,"score":20,"flags":["spam"],"recommendations":[],"reason":"no"}']]]])]);

        $this->signedPost($this->payload())->assertStatus(201)->assertJsonPath('status', 'draft');
    }

    public function test_moderator_outage_does_not_block_the_brief(): void
    {
        config(['services.groq.api_key' => null]);

        $this->signedPost($this->payload())->assertStatus(201)->assertJsonPath("status", "published");
    }

    public function test_long_form_floor_still_applies_to_normal_posts(): void
    {
        $r = app(ContentModerationService::class)->moderateContent('T', 'Short text.', 'x');
        $this->assertContains('too_short', $r['flags']);

        config(['services.groq.api_key' => null]);
        $r = app(ContentModerationService::class)->moderateContent('T', 'Short text.', 'x', true);
        $this->assertNotContains('too_short', $r['flags']);
    }
}
