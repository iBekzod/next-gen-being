<?php

namespace Tests\Feature\Content;

use App\Models\Category;
use App\Models\OutboundClick;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoRedirectAndSiteLanguageTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/129.0 Safari/537.36';

    public function test_go_redirects_to_affiliate_url_and_logs_click(): void
    {
        config(['affiliate.links.elevenlabs.url' => 'https://try.elevenlabs.io/test123']);

        $this->withHeaders(['User-Agent' => self::BROWSER_UA, 'Referer' => 'https://nextgenbeing.com/uz'])
            ->get('/go/elevenlabs')
            ->assertStatus(302)
            ->assertRedirect('https://try.elevenlabs.io/test123')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $click = OutboundClick::sole();
        $this->assertSame('elevenlabs', $click->slug);
        $this->assertTrue($click->is_affiliate);
        $this->assertSame('https://nextgenbeing.com/uz', $click->referrer);
        $this->assertSame(64, strlen($click->ip_hash));
    }

    public function test_go_falls_back_to_homepage_when_no_referral_url(): void
    {
        config(['affiliate.links.murf.url' => null]);

        $this->withHeaders(['User-Agent' => self::BROWSER_UA])
            ->get('/go/murf')
            ->assertRedirect('https://murf.ai');

        $this->assertFalse(OutboundClick::sole()->is_affiliate);
    }

    public function test_go_unknown_slug_is_404_and_bots_are_not_logged(): void
    {
        $this->get('/go/does-not-exist')->assertNotFound();

        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])
            ->get('/go/elevenlabs')
            ->assertStatus(302);

        $this->assertSame(0, OutboundClick::count());
    }

    public function test_go_links_in_post_markdown_are_marked_sponsored(): void
    {
        $post = new Post(['content' => "Try [ElevenLabs](https://nextgenbeing.com/go/elevenlabs) and [docs](https://example.com)."]);

        $html = $post->rendered_content;

        $this->assertStringContainsString('href="https://nextgenbeing.com/go/elevenlabs" rel="sponsored nofollow noopener" target="_blank"', $html);
        $this->assertStringNotContainsString('href="https://example.com" rel="sponsored', $html);
    }

    public function test_uz_section_is_retired_with_a_permanent_redirect(): void
    {
        $this->get('/uz')->assertStatus(301)->assertRedirect('/');
    }

    public function test_retired_post_slugs_redirect_permanently(): void
    {
        foreach (config('redirects.posts') as $from => $to) {
            $this->get('/posts/' . $from)->assertStatus(301)->assertRedirect('/posts/' . $to);
        }
        $this->assertTrue(true);
    }

    public function test_slug_does_not_change_when_title_is_edited(): void
    {
        $post = Post::factory()->create([
            'author_id' => User::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
            'title' => 'Original title for slug stability',
            'slug' => null,
        ]);
        $slug = $post->slug;
        $this->assertNotEmpty($slug);

        $post->update(['title' => 'A completely different title']);

        $this->assertSame($slug, $post->fresh()->slug);
    }

    public function test_review_is_required_for_auto_publication(): void
    {
        config(['content.require_quality_review' => true]);
        $gate = app(\App\Services\Content\PublishGate::class);

        $post = new Post(['content' => 'x', 'moderation_status' => 'approved']);
        $this->assertContains('no_quality_review', $gate->failures($post));

        $post->quality_report = ['passed' => true, 'mean' => 4.4];
        $this->assertNotContains('no_quality_review', $gate->failures($post));

        $post->quality_report = ['passed' => false, 'mean' => 3.1];
        $this->assertContains('no_quality_review', $gate->failures($post));
    }
}
