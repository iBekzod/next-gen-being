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

    private function makeSource(?string $rssUrl = self::FEED_URL, string $siteUrl = 'https://example.com'): ContentSource
    {
        return ContentSource::create([
            'name' => 'Example Source',
            'url' => $siteUrl,
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

    public function test_link_yoq_bolsa_http_id_ishlatiladi(): void
    {
        $feed = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>Example Atom Feed</title>
  <entry>
    <title>Id Only Article</title>
    <id>https://example.com/articles/id-only</id>
    <summary>No usable link element on this entry.</summary>
    <published>2026-09-01T10:00:00Z</published>
  </entry>
</feed>
XML;

        Http::fake([
            self::FEED_URL => Http::response($feed, 200),
            'https://example.com/articles/*' => Http::response($this->articleHtml(), 200),
        ]);

        $source = $this->makeSource();

        $found = app(ContentScraperService::class)->scrapeSource($source, 10);

        $this->assertSame(1, $found, 'http(s) id fallback ishlamadi');
        $this->assertDatabaseHas('collected_content', [
            'content_source_id' => $source->id,
            'external_url' => 'https://example.com/articles/id-only',
            'title' => 'Id Only Article',
        ]);
    }

    public function test_probing_html_qaytarsa_html_scraperga_qaytadi(): void
    {
        // A catch-all route / SPA soft-404: the HEAD probe gets a 200, then the body is HTML.
        $softNotFound = '<!DOCTYPE html><html><head><meta charset="utf-8">'
            . '<title>Not found</title></head><body><p>No feed here</p><br></body></html>';

        $listing = '<html><body><article>'
            . '<h2>HTML Listing Article</h2>'
            . '<p>Teaser text for the listing card.</p>'
            . '<a href="/articles/html-one">Read more</a>'
            . '</article></body></html>';

        Http::fake([
            'https://example.com/feed/' => Http::response($softNotFound, 200, ['Content-Type' => 'text/html']),
            'https://example.com/articles/*' => Http::response($this->articleHtml(), 200),
            'https://example.com/blog' => Http::response($listing, 200, ['Content-Type' => 'text/html']),
            '*' => Http::response('', 404),
        ]);

        $source = $this->makeSource(rssUrl: null, siteUrl: 'https://example.com/blog');

        $found = app(ContentScraperService::class)->scrapeSource($source, 10);

        // The HTML path must actually have run, not merely returned 0 from the feed path.
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && $request->url() === 'https://example.com/blog');

        $this->assertSame(1, $found, 'HTML scraperga qaytish ishlamadi');
        $this->assertDatabaseHas('collected_content', [
            'content_source_id' => $source->id,
            'external_url' => 'https://example.com/articles/html-one',
            'title' => 'HTML Listing Article',
        ]);
    }

    public function test_saqlangan_feed_bosh_bolsa_html_scraperga_qaytmaydi(): void
    {
        $emptyFeed = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0"><channel><title>Empty</title><link>https://example.com</link></channel></rss>
XML;

        $listing = '<html><body><article>'
            . '<h2>HTML Listing Article</h2>'
            . '<p>Teaser text for the listing card.</p>'
            . '<a href="/articles/html-one">Read more</a>'
            . '</article></body></html>';

        Http::fake([
            self::FEED_URL => Http::response($emptyFeed, 200),
            'https://example.com/blog' => Http::response($listing, 200),
            'https://example.com/articles/*' => Http::response($this->articleHtml(), 200),
            '*' => Http::response('', 404),
        ]);

        $source = $this->makeSource(siteUrl: 'https://example.com/blog');

        $found = app(ContentScraperService::class)->scrapeSource($source, 10);

        $this->assertSame(0, $found);

        // A configured feed returning nothing is real information; the homepage must not be
        // scraped behind the operator's back.
        Http::assertNotSent(fn (Request $request) => $request->url() === 'https://example.com/blog');
        $this->assertDatabaseCount('collected_content', 0);
    }
}
