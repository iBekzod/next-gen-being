<?php

namespace Tests\Feature\Content;

use App\Models\ContentSource;
use App\Services\ContentScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RssFeedScrapingTest extends TestCase
{
    use RefreshDatabase;

    private const FEED_URL = 'https://feeds.example.com/custom-feed.xml';

    protected function setUp(): void
    {
        parent::setUp();

        // Nothing in this test may reach the network.
        Http::preventStrayRequests();
    }

    private function makeSource(?string $rssUrl = self::FEED_URL): ContentSource
    {
        return ContentSource::create([
            'name' => 'Example Source',
            'url' => 'https://example.com',
            'rss_url' => $rssUrl,
            'category' => 'blog',
            'language' => 'en',
            'trust_level' => 95,
            'scraping_enabled' => true,
            'rate_limit_per_sec' => 1,
        ]);
    }

    private function atomFeed(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Example Atom Feed</title>
  <link rel="self" href="https://feeds.example.com/custom-feed.xml"/>
  <entry>
    <title>Atom Article Title</title>
    <link rel="alternate" type="text/html" href="https://example.com/articles/atom-one"/>
    <link rel="edit" href="https://example.com/edit/atom-one"/>
    <id>tag:example.com,2026:entry-1</id>
    <summary>Short atom summary.</summary>
    <published>2026-09-01T10:00:00Z</published>
  </entry>
</feed>
XML;
    }

    private function rssFeed(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
  <channel>
    <title>Example RSS Feed</title>
    <link>https://example.com</link>
    <item>
      <title>RSS Article Title</title>
      <link>https://example.com/articles/rss-one</link>
      <description>Short rss description.</description>
      <pubDate>Mon, 01 Sep 2026 10:00:00 +0000</pubDate>
    </item>
  </channel>
</rss>
XML;
    }

    private function articleHtml(): string
    {
        $paragraph = trim(str_repeat('The quick brown fox jumps over the lazy dog while writing about software design. ', 20));

        return "<html><body><article><p>{$paragraph}</p></article></body></html>";
    }

    public function test_atom_link_href_saqlanadi(): void
    {
        Http::fake([
            self::FEED_URL => Http::response($this->atomFeed(), 200, ['Content-Type' => 'application/atom+xml']),
            'https://example.com/articles/*' => Http::response($this->articleHtml(), 200),
        ]);

        $source = $this->makeSource();

        $found = app(ContentScraperService::class)->scrapeSource($source, 10);

        $this->assertSame(1, $found, 'Atom feed hech qanday maqola saqlamadi');
        $this->assertDatabaseHas('collected_content', [
            'content_source_id' => $source->id,
            'external_url' => 'https://example.com/articles/atom-one',
            'title' => 'Atom Article Title',
        ]);
    }

    public function test_rss_link_matni_saqlanadi(): void
    {
        Http::fake([
            self::FEED_URL => Http::response($this->rssFeed(), 200, ['Content-Type' => 'application/rss+xml']),
            'https://example.com/articles/*' => Http::response($this->articleHtml(), 200),
        ]);

        $source = $this->makeSource();

        $found = app(ContentScraperService::class)->scrapeSource($source, 10);

        $this->assertSame(1, $found, 'RSS feed hech qanday maqola saqlamadi');
        $this->assertDatabaseHas('collected_content', [
            'content_source_id' => $source->id,
            'external_url' => 'https://example.com/articles/rss-one',
            'title' => 'RSS Article Title',
        ]);
    }

    public function test_saqlangan_rss_url_ishlatiladi_probing_qilinmaydi(): void
    {
        Http::fake([
            self::FEED_URL => Http::response($this->rssFeed(), 200, ['Content-Type' => 'application/rss+xml']),
            'https://example.com/articles/*' => Http::response($this->articleHtml(), 200),
        ]);

        $source = $this->makeSource();

        app(ContentScraperService::class)->scrapeSource($source, 10);

        Http::assertSent(fn (Request $request) => $request->url() === self::FEED_URL);

        // No probing of the site root for a feed, and no HEAD requests at all.
        Http::assertNotSent(fn (Request $request) => $request->method() === 'HEAD');
        Http::assertNotSent(function (Request $request) {
            return $request->url() !== self::FEED_URL
                && preg_match('#/(feed|rss|atom|index)(\.xml|/)?(\?|$)#i', $request->url()) === 1;
        });
    }

    public function test_rss_url_yoq_manba_probing_qiladi(): void
    {
        Http::fake([
            'https://example.com/feed/' => Http::response($this->rssFeed(), 200),
            'https://example.com/articles/*' => Http::response($this->articleHtml(), 200),
            '*' => Http::response('', 404),
        ]);

        $source = $this->makeSource(rssUrl: null);

        $found = app(ContentScraperService::class)->scrapeSource($source, 10);

        $this->assertSame(1, $found, 'Probing orqali feed topilmadi');
        Http::assertSent(fn (Request $request) => $request->method() === 'HEAD'
            && $request->url() === 'https://example.com/feed/');
    }
}
