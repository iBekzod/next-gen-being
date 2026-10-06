<?php

namespace Tests\Feature\Content;

use App\Models\Category;
use App\Models\OutboundClick;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoRedirectAndUzbekSectionTest extends TestCase
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

    public function test_uz_section_lists_only_uzbek_posts_with_uz_lang(): void
    {
        $author = User::factory()->create();
        $category = Category::factory()->create();

        $base = [
            'author_id' => $author->id,
            'category_id' => $category->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
            'content' => 'x',
            'excerpt' => 'x',
        ];
        Post::factory()->create($base + ['title' => "O'zbekcha sinov maqolasi", 'slug' => 'ozbekcha-sinov', 'base_language' => 'uz']);
        Post::factory()->create($base + ['title' => 'English only post', 'slug' => 'english-only', 'base_language' => 'en']);

        $res = $this->get('/uz')->assertOk();

        $res->assertSee('<html lang="uz"', false);
        $res->assertSee('hreflang="uz"', false);
        $res->assertSee('sinov maqolasi');
        $res->assertDontSee('English only post');
        // Title must be escaped once, not twice (regression: "&amp;#039;").
        $res->assertDontSee('&amp;#039;', false);
    }
}
