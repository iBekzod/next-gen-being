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
                'category' => 'news',
                'trust_level' => 100,
                'description' => 'Breaking tech news and startup coverage',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Dev.to',
                'url' => 'https://dev.to',
                'category' => 'blog',
                'trust_level' => 95,
                'description' => 'Community of software developers sharing knowledge',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Hacker News',
                'url' => 'https://news.ycombinator.com',
                'category' => 'news',
                'trust_level' => 90,
                'description' => 'Community-curated tech and startup news',
                'rate_limit_per_sec' => 2,
            ],
            [
                'name' => 'CSS-Tricks',
                'url' => 'https://css-tricks.com',
                'category' => 'blog',
                'trust_level' => 95,
                'description' => 'Daily articles about CSS, HTML, JavaScript, and web design',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Smashing Magazine',
                'url' => 'https://www.smashingmagazine.com',
                'category' => 'blog',
                'trust_level' => 95,
                'description' => 'Web design and development insights',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'The Verge',
                'url' => 'https://www.theverge.com',
                'category' => 'news',
                'trust_level' => 90,
                'description' => 'Technology, science, and culture coverage',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'Wired',
                'url' => 'https://www.wired.com',
                'category' => 'news',
                'trust_level' => 90,
                'description' => 'News, culture, and technology insights',
                'rate_limit_per_sec' => 1,
            ],
            [
                'name' => 'ArXiv',
                'url' => 'https://arxiv.org',
                'category' => 'research',
                'trust_level' => 100,
                'description' => 'Open-access preprints in physics, CS, math, and more',
                'rate_limit_per_sec' => 2,
            ],
            [
                'name' => 'Product Hunt',
                'url' => 'https://www.producthunt.com',
                'category' => 'news',
                'trust_level' => 85,
                'description' => 'Community-driven product discovery',
                'rate_limit_per_sec' => 2,
            ],
            [
                'name' => 'GitHub Trending',
                'url' => 'https://github.com/trending',
                'category' => 'blog',
                'trust_level' => 90,
                'description' => 'Trending open-source repositories on GitHub',
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
        int $rateLimitPerSec = 1
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

        $source = ContentSource::create([
            'name' => $name,
            'url' => $url,
            'category' => $category,
            'trust_level' => $trustLevel,
            'description' => $description,
            'rate_limit_per_sec' => $rateLimitPerSec,
            'scraping_enabled' => $trustLevel >= 70,
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
     * Initialize default sources
     */
    public function initializeDefaultSources(): int
    {
        $defaults = $this->getDefaultSources();
        $count = 0;

        foreach ($defaults as $sourceData) {
            try {
                if (!ContentSource::where('name', $sourceData['name'])->exists()) {
                    $this->addSource(
                        name: $sourceData['name'],
                        url: $sourceData['url'],
                        category: $sourceData['category'],
                        trustLevel: $sourceData['trust_level'],
                        description: $sourceData['description'] ?? null,
                        rateLimitPerSec: $sourceData['rate_limit_per_sec'],
                    );
                    $count++;
                }
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
