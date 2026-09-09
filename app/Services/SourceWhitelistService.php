<?php

namespace App\Services;

use App\Models\ContentSource;
use Illuminate\Support\Facades\Log;

class SourceWhitelistService
{
    /**
     * Default whitelisted sources
     */
    public function getDefaultSources(): array
    {
        return [
            [
                'name' => 'TechCrunch',
                'url' => 'https://techcrunch.com',
                'rss_url' => 'https://techcrunch.com/feed/',
                'category' => 'news',
                'trust_level' => 100,
                'description' => 'Breaking tech news and startup coverage',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Dev.to',
                'url' => 'https://dev.to',
                'rss_url' => 'https://dev.to/feed',
                'category' => 'blog',
                'trust_level' => 95,
                'description' => 'Community of software developers sharing knowledge',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Hacker News',
                'url' => 'https://news.ycombinator.com',
                'rss_url' => 'https://news.ycombinator.com/rss',
                'category' => 'news',
                'trust_level' => 90,
                'description' => 'Community-curated tech and startup news',
                'rate_limit_per_sec' => 2,
            ],
            [
                'name' => 'CSS-Tricks',
                'url' => 'https://css-tricks.com',
                'rss_url' => 'https://css-tricks.com/feed/',
                'category' => 'blog',
                'trust_level' => 95,
                'description' => 'Daily articles about CSS, HTML, JavaScript, and web design',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Smashing Magazine',
                'url' => 'https://www.smashingmagazine.com',
                'rss_url' => 'https://www.smashingmagazine.com/feed/',
                'category' => 'blog',
                'trust_level' => 95,
                'description' => 'Web design and development insights',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'The Verge',
                'url' => 'https://www.theverge.com',
                'rss_url' => 'https://www.theverge.com/rss/index.xml',
                'category' => 'news',
                'trust_level' => 90,
                'description' => 'Technology, science, and culture coverage',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Wired',
                'url' => 'https://www.wired.com',
                'rss_url' => 'https://www.wired.com/feed/rss',
                'category' => 'news',
                'trust_level' => 90,
                'description' => 'News, culture, and technology insights',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'ArXiv',
                'url' => 'https://arxiv.org',
                'rss_url' => 'http://export.arxiv.org/api/query?search_query=cat:cs.SE&max_results=10',
                'category' => 'research',
                'trust_level' => 100,
                'description' => 'Open-access preprints in physics, CS, math, and more',
                'rate_limit_per_sec' => 2,
            ],
            [
                'name' => 'Product Hunt',
                'url' => 'https://www.producthunt.com',
                'rss_url' => 'https://www.producthunt.com/feed',
                'category' => 'news',
                'trust_level' => 85,
                'description' => 'Community-driven product discovery',
                'rate_limit_per_sec' => 2,
            ],
            [
                // GitHub does not publish a feed for /trending and the generic HTML scrape returns
                // nothing, so the source is kept on the whitelist but disabled instead of pretending
                // to contribute. Re-enable it only together with a real, measured feed URL.
                'name' => 'GitHub Trending',
                'url' => 'https://github.com/trending',
                'rss_url' => null,
                'category' => 'blog',
                'trust_level' => 90,
                'scraping_enabled' => false,
                'description' => 'Trending open-source repositories on GitHub (disabled: no RSS/Atom feed exists)',
                'rate_limit_per_sec' => 1,
            ],
        ];
    }

    /**
     * Add a new source to whitelist
     */
    public function addSource(
        string $name,
        string $url,
        string $category,
        int $trustLevel = 75,
        ?string $description = null,
        int $rateLimitPerSec = 1,
        ?string $rssUrl = null,
        ?bool $scrapingEnabled = null
    ): ContentSource {
        Log::info("Adding source to whitelist: {$name}");

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid URL: {$url}");
        }

        // Validate category
        $validCategories = ['news', 'blog', 'research', 'announcement', 'social'];
        if (!in_array($category, $validCategories)) {
            throw new \Exception("Invalid category: {$category}");
        }

        // Validate trust level
        if ($trustLevel < 0 || $trustLevel > 100) {
            throw new \Exception("Trust level must be between 0-100");
        }

        // Check for duplicates
        if (ContentSource::where('name', $name)->exists()) {
            throw new \Exception("Source '{$name}' already exists");
        }

        if ($rssUrl !== null && !filter_var($rssUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid RSS URL: {$rssUrl}");
        }

        $source = ContentSource::create([
            'name' => $name,
            'url' => $url,
            'rss_url' => $rssUrl,
            'category' => $category,
            'trust_level' => $trustLevel,
            'description' => $description,
            'rate_limit_per_sec' => $rateLimitPerSec,
            'scraping_enabled' => $scrapingEnabled ?? ($trustLevel >= 70),
        ]);

        Log::info("Source added successfully: {$source->name} (Trust: {$source->trust_level})");

        return $source;
    }

    /**
     * Update a source's trust level
     */
    public function updateTrustLevel(ContentSource $source, int $newLevel, ?string $reason = null): void
    {
        if ($newLevel < 0 || $newLevel > 100) {
            throw new \Exception("Trust level must be between 0-100");
        }

        $oldLevel = $source->trust_level;
        $source->update(['trust_level' => $newLevel]);

        // Disable scraping if trust drops below 70
        if ($newLevel < 70 && $source->scraping_enabled) {
            $source->update(['scraping_enabled' => false]);
        }

        // Enable scraping if trust rises above 70
        if ($newLevel >= 70 && !$source->scraping_enabled) {
            $source->update(['scraping_enabled' => true]);
        }

        Log::info("Trust level updated for {$source->name}: {$oldLevel} → {$newLevel}. Reason: {$reason}");
    }

    /**
     * Disable a source
     */
    public function disableSource(ContentSource $source, ?string $reason = null): void
    {
        $source->update(['scraping_enabled' => false]);
        Log::warning("Source disabled: {$source->name}. Reason: {$reason}");
    }

    /**
     * Enable a source
     */
    public function enableSource(ContentSource $source): void
    {
        if ($source->trust_level >= 70) {
            $source->update(['scraping_enabled' => true]);
            Log::info("Source enabled: {$source->name}");
        } else {
            throw new \Exception("Cannot enable source with trust level below 70. Current: {$source->trust_level}");
        }
    }

    /**
     * Validate a new source by scraping one article
     */
    public function validateNewSource(ContentSource $source): array
    {
        Log::info("Validating new source: {$source->name}");

        $validation = [
            'valid' => false,
            'message' => '',
            'issues' => [],
        ];

        try {
            // Test if URL is accessible
            $response = \Illuminate\Support\Facades\Http::timeout(10)->head($source->url);

            if (!$response->successful()) {
                $validation['issues'][] = "Website not accessible (HTTP {$response->status()})";
                $validation['message'] = 'Website is not reachable';
                return $validation;
            }

            // Try to scrape one article
            $scraper = new ContentScraperService();
            $articlesFound = $scraper->scrapeSource($source, limit: 1);

            if ($articlesFound === 0) {
                $validation['issues'][] = 'Could not find any articles to scrape';
                $validation['message'] = 'Unable to detect article structure on this website';
                return $validation;
            }

            // Source is valid
            $validation['valid'] = true;
            $validation['message'] = "Successfully validated and scraped 1 article";

        } catch (\Exception $e) {
            $validation['issues'][] = $e->getMessage();
            $validation['message'] = 'Validation failed: ' . $e->getMessage();
            Log::error("Source validation failed for {$source->name}: {$e->getMessage()}");
        }

        return $validation;
    }

    /**
     * Get scrape configuration for a source
     */
    public function getScrapeConfig(ContentSource $source): array
    {
        return [
            'source_id' => $source->id,
            'source_name' => $source->name,
            'url' => $source->url,
            'rss_url' => $source->rss_url,
            'category' => $source->category,
            'enabled' => $source->scraping_enabled,
            'trust_level' => $source->trust_level,
            'rate_limit' => $source->rate_limit_per_sec,
            'css_selectors' => json_decode($source->css_selectors, true) ?? [],
            'last_scraped' => $source->last_scraped_at?->toIso8601String(),
            'next_scrape_after' => $source->last_scraped_at?->addHours(6)->toIso8601String(),
        ];
    }

    /**
     * Get all active sources
     */
    public function getActiveSources()
    {
        return ContentSource::active()
            ->orderByDesc('trust_level')
            ->get();
    }

    /**
     * Get sources by category
     */
    public function getSourcesByCategory(string $category)
    {
        return ContentSource::byCategory($category)
            ->active()
            ->orderByDesc('trust_level')
            ->get();
    }

    /**
     * Get high-trust sources only
     */
    public function getHighTrustSources()
    {
        return ContentSource::highTrust()
            ->active()
            ->orderByDesc('trust_level')
            ->get();
    }

    /**
     * Get sources that need scraping (haven't been scraped in 24 hours)
     */
    public function getSourcesNeedingScraping()
    {
        return ContentSource::needsScraping()
            ->orderByDesc('trust_level')
            ->get();
    }

    /**
     * Initialize default sources.
     *
     * Existing rows are UPDATED rather than skipped, otherwise a production database seeded before
     * feed URLs existed would never pick them up. Operator-managed fields (trust_level, and
     * scraping_enabled unless the default explicitly pins it) are left untouched.
     *
     * @return int number of sources created or updated
     */
    public function initializeDefaultSources(): int
    {
        $defaults = $this->getDefaultSources();
        $count = 0;

        foreach ($defaults as $sourceData) {
            try {
                $existing = ContentSource::where('name', $sourceData['name'])->first();

                if ($existing === null) {
                    $this->addSource(
                        name: $sourceData['name'],
                        url: $sourceData['url'],
                        category: $sourceData['category'],
                        trustLevel: $sourceData['trust_level'],
                        description: $sourceData['description'] ?? null,
                        rateLimitPerSec: $sourceData['rate_limit_per_sec'],
                        rssUrl: $sourceData['rss_url'] ?? null,
                        scrapingEnabled: $sourceData['scraping_enabled'] ?? null,
                    );
                    $count++;
                    continue;
                }

                $updates = [
                    'url' => $sourceData['url'],
                    'rss_url' => $sourceData['rss_url'] ?? null,
                    'category' => $sourceData['category'],
                    'description' => $sourceData['description'] ?? null,
                    'rate_limit_per_sec' => $sourceData['rate_limit_per_sec'],
                ];

                // Only force scraping_enabled when the default pins it deliberately
                // (GitHub Trending, which has no feed at all).
                if (array_key_exists('scraping_enabled', $sourceData)) {
                    $updates['scraping_enabled'] = $sourceData['scraping_enabled'];
                }

                $existing->update($updates);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to initialize source {$sourceData['name']}: {$e->getMessage()}");
            }
        }

        Log::info("Initialized {$count} default sources");
        return $count;
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        $allSources = ContentSource::all();
        $activeSources = $allSources->where('scraping_enabled', true);

        return [
            'total_sources' => $allSources->count(),
            'active_sources' => $activeSources->count(),
            'disabled_sources' => $allSources->count() - $activeSources->count(),
            'avg_trust_level' => (int) $allSources->avg('trust_level'),
            'total_articles_collected' => \App\Models\CollectedContent::count(),
            'sources_by_category' => $allSources->groupBy('category')->map->count(),
        ];
    }
}
