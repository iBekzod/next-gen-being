<?php

namespace App\Services;

use App\Models\ContentSource;
use App\Models\CollectedContent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ContentScraperService
{
    private const REQUEST_TIMEOUT = 30;
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

    /**
     * Scrape a single content source
     */
    public function scrapeSource(ContentSource $source, int $limit = 50): int
    {
        Log::info("Starting to scrape source: {$source->name}");

        if (!$source->canScrape()) {
            Log::warning("Source {$source->name} is not available for scraping");
            return 0;
        }

        try {
            $articlesFound = 0;

            // Try to fetch RSS feed first if available
            if ($this->hasRSSFeed($source)) {
                $articlesFound = $this->scrapeRSSFeed($source, $limit);
            } else {
                // Fall back to direct website scraping
                $articlesFound = $this->scrapWebsite($source, $limit);
            }

            $source->markAsScraped();
            Log::info("Successfully scraped {$source->name}, found {$articlesFound} articles");

            return $articlesFound;

        } catch (\Exception $e) {
            Log::error("Error scraping {$source->name}: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Scrape from RSS feed
     */
    private function scrapeRSSFeed(ContentSource $source, int $limit): int
    {
        try {
            $rssUrl = $this->guessRSSUrl($source->url);
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($rssUrl);

            if (!$response->successful()) {
                return 0;
            }

            $xml = simplexml_load_string($response->body());
            if ($xml === false) {
                return 0;
            }

            $articlesAdded = 0;
            $items = $xml->channel->item ?? $xml->entry;

            foreach ($items as $item) {
                if ($articlesAdded >= $limit) {
                    break;
                }

                $articleData = [
                    'title' => (string) ($item->title ?? ''),
                    'url' => (string) ($item->link ?? $item->id ?? ''),
                    'description' => (string) ($item->description ?? $item->summary ?? ''),
                    'author' => (string) ($item->author ?? $item->{'dc:creator'} ?? ''),
                    'published_at' => $this->parseDate((string) ($item->pubDate ?? $item->published ?? '')),
                ];

                if ($articleData['url'] && $this->storeContent($source, $articleData)) {
                    $articlesAdded++;
                }
            }

            return $articlesAdded;

        } catch (\Exception $e) {
            Log::error("RSS scraping failed for {$source->name}: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Scrape website directly (generic)
     */
    private function scrapWebsite(ContentSource $source, int $limit): int
    {
        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($source->url);

            if (!$response->successful()) {
                return 0;
            }

            $crawler = new Crawler($response->body());
            $articlesAdded = 0;

            // Try common article selectors
            $selectors = [
                'article',
                '[data-article]',
                '.article-item',
                '.post',
                '.post-item',
                '[role="article"]',
            ];

            foreach ($selectors as $selector) {
                if ($articlesAdded >= $limit) {
                    break;
                }

                try {
                    $crawler->filter($selector)->each(function (Crawler $node) use ($source, $limit, &$articlesAdded) {
                        if ($articlesAdded >= $limit) {
                            return;
                        }

                        $articleData = $this->extractArticleData($node, $source);
                        if ($articleData && $this->storeContent($source, $articleData)) {
                            $articlesAdded++;
                        }
                    });
                } catch (\Exception $e) {
                    Log::debug("Selector {$selector} failed for {$source->name}");
                }
            }

            return $articlesAdded;

        } catch (\Exception $e) {
            Log::error("Website scraping failed for {$source->name}: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Extract article data from HTML node
     */
    private function extractArticleData(Crawler $node, ContentSource $source): ?array
    {
        try {
            $title = $node->filter('h1, h2, h3, [data-title], .title')->first()?->text() ?? '';
            $excerpt = $node->filter('p, [data-summary], .excerpt')->first()?->text() ?? '';
            $link = $node->filter('a')->first()?->attr('href') ?? '';
            $author = $node->filter('[data-author], .author, .by')->first()?->text() ?? '';

            if (empty($title) || empty($link)) {
                return null;
            }

            // Make relative URLs absolute
            $link = $this->resolveUrl($link, $source->url);

            // Check if already scraped
            if (CollectedContent::where('external_url', $link)->exists()) {
                return null;
            }

            return [
                'title' => $title,
                'url' => $link,
                'description' => $excerpt,
                'author' => $author,
                'published_at' => now(),
            ];

        } catch (\Exception $e) {
            Log::debug("Failed to extract article data: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Store collected content
     */
    private function storeContent(ContentSource $source, array $articleData): bool
    {
        try {
            // Check for duplicate URL
            if (CollectedContent::where('external_url', $articleData['url'])->exists()) {
                return false;
            }

            // Fetch full content
            $fullContent = $this->fetchFullContent($articleData['url']);

            if (empty($fullContent)) {
                return false;
            }

            // Validate content quality
            if (!$this->validateContent($fullContent)) {
                Log::debug("Content quality check failed for: {$articleData['url']}");
                return false;
            }

            // Determine content type
            $contentType = $this->detectContentType($fullContent, $articleData['title']);

            // Create collected content record
            CollectedContent::create([
                'content_source_id' => $source->id,
                'external_url' => $articleData['url'],
                'title' => $articleData['title'],
                'excerpt' => substr($articleData['description'], 0, 500),
                'full_content' => $fullContent,
                'author' => $articleData['author'] ?? null,
                'published_at' => $articleData['published_at'],
                'language' => $source->language,
                'content_type' => $contentType,
                'image_url' => $this->extractImageUrl($fullContent),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to store content: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Fetch full content from URL
     */
    private function fetchFullContent(string $url): string
    {
        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get($url);

            if (!$response->successful()) {
                return '';
            }

            // Extract main content (remove navigation, ads, etc.)
            $crawler = new Crawler($response->body());

            // Try site-specific selectors first
            $siteSelectors = $this->getSiteSpecificSelectors($url);
            foreach ($siteSelectors as $selector) {
                try {
                    $content = $crawler->filter($selector)->first()?->html();
                    if ($content && strlen(strip_tags($content)) > 100) {
                        return strip_tags($content);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Try common content selectors
            $selectors = [
                'article',
                '[role="main"]',
                '.content',
                '.post-content',
                '.article-content',
                'main',
                '[data-article]',
                '.crayons-article__body',  // Dev.to specific
                '.article',
                '#article',
            ];

            foreach ($selectors as $selector) {
                try {
                    $content = $crawler->filter($selector)->first()?->html();
                    if ($content && strlen(strip_tags($content)) > 100) {
                        return strip_tags($content);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Fall back to all paragraphs in body
            $bodyContent = $crawler->filter('body p');
            if ($bodyContent->count() > 0) {
                $text = '';
                $bodyContent->each(function (Crawler $node) use (&$text) {
                    $paragraphText = $node->text();
                    // Skip nav/header/footer text
                    if (!preg_match('/(navbar|menu|footer|nav|comment|reaction|follow)/i', $paragraphText)) {
                        $text .= $paragraphText . "\n";
                    }
                });
                if (strlen($text) > 100) {
                    return $text;
                }
            }

            // Try extracting from div containers with content
            try {
                $allDivs = $crawler->filter('div, section');
                foreach ($allDivs as $div) {
                    $text = strip_tags($div->textContent ?? '');
                    if (strlen($text) > 500 && !preg_match('/(navbar|menu|footer|sidebar|comment)/i', $text)) {
                        return substr($text, 0, 5000); // Return first 5000 chars to avoid too much data
                    }
                }
            } catch (\Exception $e) {
                // Continue
            }

            // Last resort: get body content but remove scripts and styles
            $html = $crawler->filter('body')->html() ?? '';
            // Remove script and style tags
            $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
            $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
            $text = strip_tags($html);

            // Clean up excessive whitespace
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);

            if (strlen($text) > 100) {
                return $text;
            }

            return '';

        } catch (\Exception $e) {
            Log::error("Failed to fetch content from {$url}: {$e->getMessage()}");
            return '';
        }
    }

    /**
     * Get site-specific CSS selectors
     */
    private function getSiteSpecificSelectors(string $url): array
    {
        if (strpos($url, 'dev.to') !== false) {
            return [
                '.crayons-article__body',
                '[data-article-body]',
                '.article__body',
                '.article-body',
            ];
        }

        if (strpos($url, 'medium.com') !== false) {
            return [
                'article',
                '[data-article-content]',
                '.postArticle-content',
                '.pw-post-body-paragraph',
            ];
        }

        if (strpos($url, 'techcrunch.com') !== false) {
            return [
                '.article-content',
                '[data-article]',
                '.post-content',
            ];
        }

        if (strpos($url, 'css-tricks.com') !== false) {
            return [
                '.post-content',
                'article',
                '[role="main"]',
            ];
        }

        if (strpos($url, 'smashingmagazine.com') !== false) {
            return [
                '.c-article-content',
                'article',
                '[role="main"]',
            ];
        }

        return [];
    }

    /**
     * Validate content quality
     */
    public function validateContent(string $content): bool
    {
        // Minimum word count
        $wordCount = str_word_count(strip_tags($content));
        if ($wordCount < 100) {
            return false;
        }

        // Should have substantial alphabetic content (not just numbers)
        $letterCount = preg_match_all('/[a-zA-Z]/i', $content);
        $totalChars = strlen($content);

        // At least 50% should be letters
        if ($totalChars > 0 && ($letterCount / $totalChars) < 0.3) {
            return false;
        }

        return true;
    }

    /**
     * Detect content type
     */
    private function detectContentType(string $content, string $title): string
    {
        $lowerContent = strtolower($content);
        $lowerTitle = strtolower($title);

        // Detect tutorial
        if (preg_match('/(tutorial|guide|how to|step by step|walkthrough)/i', $lowerTitle . ' ' . $lowerContent)) {
            return 'tutorial';
        }

        // Detect news
        if (preg_match('/(breaking|announce|launch|released|new version)/i', $lowerTitle)) {
            return 'news';
        }

        // Detect research
        if (preg_match('/(research|study|paper|findings|experiment)/i', $lowerTitle)) {
            return 'research';
        }

        return 'article';
    }

    /**
     * Extract image URL from content
     */
    private function extractImageUrl(string $content): ?string
    {
        if (preg_match('/<img[^>]+src="([^"]+)"/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Resolve relative URLs to absolute
     */
    private function resolveUrl(string $url, string $baseUrl): string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (strpos($url, '//') === 0) {
            return 'https:' . $url;
        }

        $baseParts = parse_url($baseUrl);
        $baseScheme = $baseParts['scheme'] ?? 'https';
        $baseHost = $baseParts['host'] ?? '';
        $basePath = dirname($baseParts['path'] ?? '');

        if ($url[0] === '/') {
            return "{$baseScheme}://{$baseHost}{$url}";
        }

        return "{$baseScheme}://{$baseHost}{$basePath}/{$url}";
    }

    /**
     * Parse date string
     */
    private function parseDate(string $dateStr): ?\DateTime
    {
        try {
            return \Carbon\Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Guess RSS feed URL
     */
    private function guessRSSUrl(string $siteUrl): string
    {
        $baseParts = parse_url($siteUrl);
        $baseScheme = $baseParts['scheme'] ?? 'https';
        $baseHost = $baseParts['host'] ?? '';

        $commonPaths = [
            '/feed/',
            '/rss/',
            '/feed.xml',
            '/rss.xml',
            '/feed/atom/',
            '/?feed=rss2',
        ];

        foreach ($commonPaths as $path) {
            $url = "{$baseScheme}://{$baseHost}{$path}";
            try {
                $response = Http::timeout(5)->head($url);
                if ($response->successful()) {
                    return $url;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return "{$baseScheme}://{$baseHost}/feed/";
    }

    /**
     * Check if source has RSS feed
     */
    private function hasRSSFeed(ContentSource $source): bool
    {
        // Sources known to have good RSS feeds
        $rssKnownSources = ['Dev.to', 'Medium', 'Hacker News', 'TechCrunch', 'CSS-Tricks'];

        return in_array($source->name, $rssKnownSources);
    }
}
