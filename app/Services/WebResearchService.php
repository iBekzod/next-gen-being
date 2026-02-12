<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for gathering research from multiple web sources
 * Integrates with popular tech content platforms
 */
class WebResearchService
{
    private array $sources = [
        'medium' => [
            'name' => 'Medium',
            'base_url' => 'https://medium.com',
            'search_endpoint' => '/search',
        ],
        'dev_to' => [
            'name' => 'Dev.to',
            'base_url' => 'https://dev.to/api',
            'search_endpoint' => '/articles',
        ],
        'hackernews' => [
            'name' => 'HackerNews',
            'base_url' => 'https://news.ycombinator.com',
            'api_endpoint' => 'https://hn.algolia.com/api/v1',
        ],
        'github' => [
            'name' => 'GitHub Discussions',
            'base_url' => 'https://api.github.com',
            'search_endpoint' => '/search/repositories',
        ],
    ];

    /**
     * Gather research from multiple sources for a topic
     */
    public function gatherResearch(string $topic, int $articlesPerSource = 3): array
    {
        Log::info('Starting research gathering', ['topic' => $topic]);

        $research = [
            'topic' => $topic,
            'sources' => [],
            'keyInsights' => [],
            'caseStudies' => [],
            'bestPractices' => [],
            'relatedTopics' => [],
            'gatheredAt' => now(),
        ];

        // Gather from each source
        $research['sources']['medium'] = $this->gatherFromMedium($topic, $articlesPerSource);
        $research['sources']['devto'] = $this->gatherFromDevTo($topic, $articlesPerSource);
        $research['sources']['hackernews'] = $this->gatherFromHackerNews($topic, $articlesPerSource);
        $research['sources']['github'] = $this->gatherFromGitHub($topic, $articlesPerSource);

        // Extract key insights
        $research['keyInsights'] = $this->extractKeyInsights($research['sources']);

        // Identify case studies
        $research['caseStudies'] = $this->extractCaseStudies($research['sources']);

        // Compile best practices
        $research['bestPractices'] = $this->compileBestPractices($research['sources']);

        Log::info('Research gathering completed', [
            'topic' => $topic,
            'sourcesCount' => count(array_filter($research['sources'])),
        ]);

        return $research;
    }

    /**
     * Gather from Medium API
     */
    private function gatherFromMedium(string $topic, int $limit): array
    {
        try {
            $response = Http::timeout(10)->get('https://medium.com/search', [
                'q' => $topic,
            ]);

            if (!$response->successful()) {
                Log::warning('Medium scraping failed', ['status' => $response->status()]);
                return [];
            }

            // Parse HTML to extract articles
            $articles = $this->parseMediumArticles($response->body(), $limit);

            return [
                'source' => 'Medium',
                'count' => count($articles),
                'articles' => $articles,
            ];

        } catch (\Exception $e) {
            Log::error('Medium research gathering failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Gather from Dev.to API (has public API)
     */
    private function gatherFromDevTo(string $topic, int $limit): array
    {
        try {
            $response = Http::timeout(10)->get('https://dev.to/api/articles', [
                'query' => $topic,
                'state' => 'published',
                'per_page' => $limit,
            ]);

            if (!$response->successful()) {
                Log::warning('Dev.to API request failed', ['status' => $response->status()]);
                return [];
            }

            $articles = array_map(function ($item) {
                return [
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'author' => $item['user']['name'] ?? 'Unknown',
                    'published_at' => $item['published_at'],
                    'reading_time_minutes' => $item['reading_time_minutes'],
                    'description' => $item['description'] ?? substr($item['body_markdown'], 0, 300),
                    'tags' => $item['tag_list'] ?? [],
                ];
            }, $response->json());

            return [
                'source' => 'Dev.to',
                'count' => count($articles),
                'articles' => $articles,
            ];

        } catch (\Exception $e) {
            Log::error('Dev.to research gathering failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Gather from HackerNews
     */
    private function gatherFromHackerNews(string $topic, int $limit): array
    {
        try {
            $response = Http::timeout(10)->get('https://hn.algolia.com/api/v1/search', [
                'query' => $topic,
                'hitsPerPage' => $limit,
                'typoTolerance' => 'min',
            ]);

            if (!$response->successful()) {
                Log::warning('HackerNews API request failed', ['status' => $response->status()]);
                return [];
            }

            $articles = array_map(function ($item) {
                return [
                    'title' => $item['title'] ?? $item['story_title'] ?? '',
                    'url' => $item['url'] ?? "https://news.ycombinator.com/item?id={$item['objectID']}",
                    'author' => $item['author'] ?? 'Anonymous',
                    'published_at' => date('c', $item['created_at'] ?? time()),
                    'comments' => $item['num_comments'] ?? 0,
                    'points' => $item['points'] ?? 0,
                    'source' => 'HackerNews',
                ];
            }, $response->json('hits', []));

            return [
                'source' => 'HackerNews',
                'count' => count($articles),
                'articles' => $articles,
            ];

        } catch (\Exception $e) {
            Log::error('HackerNews research gathering failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Gather from GitHub repositories and discussions
     */
    private function gatherFromGitHub(string $topic, int $limit): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeader('Accept', 'application/vnd.github.v3+json')
                ->get('https://api.github.com/search/repositories', [
                    'q' => $topic . ' in:name,description language:markdown',
                    'per_page' => $limit,
                    'sort' => 'stars',
                    'order' => 'desc',
                ]);

            if (!$response->successful()) {
                Log::warning('GitHub API request failed', ['status' => $response->status()]);
                return [];
            }

            $repos = array_map(function ($item) {
                return [
                    'title' => $item['name'],
                    'url' => $item['html_url'],
                    'description' => $item['description'],
                    'author' => $item['owner']['login'],
                    'stars' => $item['stargazers_count'],
                    'language' => $item['language'],
                    'topics' => $item['topics'] ?? [],
                    'updated_at' => $item['updated_at'],
                ];
            }, $response->json('items', []));

            return [
                'source' => 'GitHub',
                'count' => count($repos),
                'repositories' => $repos,
            ];

        } catch (\Exception $e) {
            Log::error('GitHub research gathering failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Extract key insights from gathered sources
     */
    private function extractKeyInsights(array $sources): array
    {
        $insights = [];

        // Common themes across sources
        $titleKeywords = [];

        foreach ($sources as $source) {
            if (isset($source['articles'])) {
                foreach ($source['articles'] as $article) {
                    $words = str_word_count(strtolower($article['title']), 1);
                    foreach ($words as $word) {
                        if (strlen($word) > 4) { // Skip small words
                            $titleKeywords[$word] = ($titleKeywords[$word] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        // Get top keywords as insights
        arsort($titleKeywords);
        $insights = array_slice(array_keys($titleKeywords), 0, 10);

        return $insights;
    }

    /**
     * Extract case studies from sources
     */
    private function extractCaseStudies(array $sources): array
    {
        $caseStudies = [];

        // Look for articles mentioning specific companies or scale
        $companyPatterns = ['netflix', 'uber', 'amazon', 'google', 'facebook', 'stripe', 'shopify'];
        $scalePatterns = ['million', 'billion', 'scale', '100k', '1m', '10m'];

        foreach ($sources as $source) {
            if (isset($source['articles'])) {
                foreach ($source['articles'] as $article) {
                    $title = strtolower($article['title']);
                    foreach (array_merge($companyPatterns, $scalePatterns) as $pattern) {
                        if (strpos($title, $pattern) !== false) {
                            $caseStudies[] = [
                                'title' => $article['title'],
                                'url' => $article['url'],
                                'source' => $source['source'],
                                'relevance' => 'case_study',
                            ];
                            break;
                        }
                    }
                }
            }
        }

        return array_unique($caseStudies, SORT_REGULAR);
    }

    /**
     * Compile best practices from sources
     */
    private function compileBestPractices(array $sources): array
    {
        $practices = [];

        // Look for articles with keywords suggesting best practices
        $practicePatterns = [
            'best practices',
            'lessons learned',
            'tips',
            'tricks',
            'guide',
            'tutorial',
            'how to',
            'checklist',
        ];

        foreach ($sources as $source) {
            if (isset($source['articles'])) {
                foreach ($source['articles'] as $article) {
                    $title = strtolower($article['title']);
                    foreach ($practicePatterns as $pattern) {
                        if (strpos($title, $pattern) !== false) {
                            $practices[] = [
                                'title' => $article['title'],
                                'url' => $article['url'],
                                'source' => $source['source'],
                            ];
                            break;
                        }
                    }
                }
            }
        }

        return $practices;
    }

    /**
     * Parse Medium articles from HTML
     * Note: Medium has anti-scraping measures, this is a fallback
     */
    private function parseMediumArticles(string $html, int $limit): array
    {
        $articles = [];

        // This is a simplified parser
        // In production, use a proper HTML parsing library
        preg_match_all('/<h2.*?>(.*?)<\/h2>/i', $html, $matches);

        foreach (array_slice($matches[1], 0, $limit) as $title) {
            $articles[] = [
                'title' => strip_tags($title),
                'source' => 'Medium',
                'url' => 'https://medium.com/search?q=' . urlencode($title),
            ];
        }

        return $articles;
    }

    /**
     * Format research for use in content generation
     */
    public function formatForContentGeneration(array $research): string
    {
        $formatted = "Research gathered on: {$research['topic']}\n\n";

        // Sources summary
        $formatted .= "SOURCES:\n";
        foreach ($research['sources'] as $source) {
            if (!empty($source) && isset($source['count']) && $source['count'] > 0) {
                $formatted .= "- {$source['source']}: {$source['count']} articles\n";
            }
        }

        // Key insights
        if (!empty($research['keyInsights'])) {
            $formatted .= "\nKEY TOPICS:\n";
            foreach (array_slice($research['keyInsights'], 0, 5) as $insight) {
                $formatted .= "- {$insight}\n";
            }
        }

        // Case studies
        if (!empty($research['caseStudies'])) {
            $formatted .= "\nRELEVANT CASE STUDIES:\n";
            foreach (array_slice($research['caseStudies'], 0, 3) as $study) {
                $formatted .= "- {$study['title']} ({$study['source']})\n";
            }
        }

        // Best practices
        if (!empty($research['bestPractices'])) {
            $formatted .= "\nBEST PRACTICE REFERENCES:\n";
            foreach (array_slice($research['bestPractices'], 0, 3) as $practice) {
                $formatted .= "- {$practice['title']}\n";
            }
        }

        return $formatted;
    }
}
