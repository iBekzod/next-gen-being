<?php

namespace Tests\Feature\Content;

use App\Models\ContentSource;
use App\Services\SourceWhitelistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InitializeSourcesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_default_manbalar_feed_url_bilan_yaratiladi(): void
    {
        $this->artisan('content:init-sources')->assertSuccessful();

        $this->assertDatabaseHas('content_sources', [
            'name' => 'Hacker News',
            'rss_url' => 'https://news.ycombinator.com/rss',
        ]);
        $this->assertDatabaseHas('content_sources', [
            'name' => 'The Verge',
            'rss_url' => 'https://www.theverge.com/rss/index.xml',
        ]);
        $this->assertDatabaseHas('content_sources', [
            'name' => 'Product Hunt',
            'rss_url' => 'https://www.producthunt.com/feed',
        ]);
    }

    public function test_qayta_ishga_tushirish_mavjud_qatorlarni_yangilaydi(): void
    {
        // Simulates a production row seeded before rss_url existed.
        ContentSource::create([
            'name' => 'Hacker News',
            'url' => 'https://news.ycombinator.com',
            'rss_url' => null,
            'category' => 'news',
            'trust_level' => 90,
            'scraping_enabled' => true,
            'rate_limit_per_sec' => 2,
        ]);

        $this->artisan('content:init-sources')->assertSuccessful();

        $this->assertSame(1, ContentSource::where('name', 'Hacker News')->count());
        $this->assertSame(
            'https://news.ycombinator.com/rss',
            ContentSource::where('name', 'Hacker News')->value('rss_url')
        );
    }

    public function test_operator_qoygan_rss_url_ozgarmaydi(): void
    {
        ContentSource::create([
            'name' => 'Hacker News',
            'url' => 'https://news.ycombinator.com',
            'rss_url' => 'https://news.ycombinator.com/rss?custom=1',
            'category' => 'news',
            'trust_level' => 90,
            'scraping_enabled' => true,
            'rate_limit_per_sec' => 2,
        ]);

        $this->artisan('content:init-sources')->assertSuccessful();

        $this->assertSame(
            'https://news.ycombinator.com/rss?custom=1',
            ContentSource::where('name', 'Hacker News')->value('rss_url'),
            'Operator qoygan rss_url qayta yozildi'
        );

        // A row that was left NULL is still filled from the defaults in the same run.
        $this->assertSame(
            'https://www.theverge.com/rss/index.xml',
            ContentSource::where('name', 'The Verge')->value('rss_url')
        );
    }

    public function test_github_trending_feedsiz_ochirilgan(): void
    {
        $this->artisan('content:init-sources')->assertSuccessful();

        $github = ContentSource::where('name', 'GitHub Trending')->firstOrFail();

        $this->assertNull($github->rss_url);
        $this->assertFalse($github->scraping_enabled);
        $this->assertFalse($github->canScrape());
    }

    public function test_barcha_yoqilgan_manbalarda_feed_url_bor(): void
    {
        $this->artisan('content:init-sources')->assertSuccessful();

        $enabledWithoutFeed = ContentSource::query()
            ->where('scraping_enabled', true)
            ->whereNull('rss_url')
            ->pluck('name')
            ->all();

        $this->assertSame([], $enabledWithoutFeed);
    }

    public function test_takroriy_ishga_tushirish_dublikat_yaratmaydi(): void
    {
        $service = new SourceWhitelistService();

        $service->initializeDefaultSources();
        $first = ContentSource::count();

        $service->initializeDefaultSources();

        $this->assertSame($first, ContentSource::count());
    }
}
