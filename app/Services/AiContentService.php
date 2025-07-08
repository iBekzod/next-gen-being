<?php
namespace App\Services;

use App\Models\AiContentSuggestion;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->baseUrl = 'https://api.openai.com/v1';
    }

    public function generateContentSuggestions(): array
    {
        try {
            // Get trending topics from various sources
            $trendingTopics = $this->fetchTrendingTopics();

            // Analyze current content to avoid duplication
            $existingTopics = $this->getExistingContentTopics();

            // Generate AI suggestions
            $suggestions = $this->generateSuggestionsFromAI($trendingTopics, $existingTopics);

            // Store suggestions in database
            $savedSuggestions = [];
            foreach ($suggestions as $suggestion) {
                $saved = AiContentSuggestion::create([
                    'title' => $suggestion['title'],
                    'description' => $suggestion['description'],
                    'source_url' => $suggestion['source_url'] ?? null,
                    'topics' => $suggestion['topics'] ?? [],
                    'keywords' => $suggestion['keywords'] ?? [],
                    'relevance_score' => $suggestion['relevance_score'] ?? 0.5,
                    'status' => 'pending',
                ]);

                $savedSuggestions[] = $saved;
            }

            return $savedSuggestions;

        } catch (\Exception $e) {
            Log::error('Failed to generate AI content suggestions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function fetchTrendingTopics(): array
    {
        $sources = [
            'google_trends' => $this->fetchGoogleTrends(),
            'hackernews' => $this->fetchHackerNewsTopics(),
            'reddit' => $this->fetchRedditTopics(),
        ];

        return array_merge(...array_values($sources));
    }

    private function fetchGoogleTrends(): array
    {
        // Implement Google Trends API integration
        try {
            $response = Http::timeout(10)->get('https://trends.google.com/trends/api/dailytrends', [
                'hl' => 'en-US',
                'tz' => 360,
                'geo' => 'US',
            ]);

            if ($response->successful()) {
                $data = json_decode(substr($response->body(), 6), true); // Remove )]}' prefix
                $trends = [];

                foreach ($data['default']['trendingSearchesDays'][0]['trendingSearches'] as $trend) {
                    $trends[] = [
                        'title' => $trend['title']['query'],
                        'traffic' => $trend['formattedTraffic'],
                        'source' => 'google_trends',
                        'articles' => collect($trend['articles'])->map(fn($article) => [
                            'title' => $article['title'],
                            'url' => $article['url'],
                            'source' => $article['source'],
                        ])->toArray()
                    ];
                }

                return $trends;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Google Trends', ['error' => $e->getMessage()]);
        }

        return [];
    }

    private function fetchHackerNewsTopics(): array
    {
        try {
            $response = Http::timeout(10)->get('https://hacker-news.firebaseio.com/v0/topstories.json');

            if ($response->successful()) {
                $storyIds = array_slice($response->json(), 0, 20);
                $stories = [];

                foreach ($storyIds as $id) {
                    $storyResponse = Http::timeout(5)->get("https://hacker-news.firebaseio.com/v0/item/{$id}.json");
                    if ($storyResponse->successful()) {
                        $story = $storyResponse->json();
                        if (isset($story['title']) && isset($story['url'])) {
                            $stories[] = [
                                'title' => $story['title'],
                                'url' => $story['url'],
                                'score' => $story['score'] ?? 0,
                                'source' => 'hackernews'
                            ];
                        }
                    }
                }

                return $stories;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Hacker News topics', ['error' => $e->getMessage()]);
        }

        return [];
    }

    private function fetchRedditTopics(): array
    {
        try {
            $subreddits = ['technology', 'programming', 'webdev', 'artificial', 'MachineLearning'];
            $topics = [];

            foreach ($subreddits as $subreddit) {
                $response = Http::timeout(10)->get("https://www.reddit.com/r/{$subreddit}/hot.json", [
                    'limit' => 10
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    foreach ($data['data']['children'] as $post) {
                        $postData = $post['data'];
                        $topics[] = [
                            'title' => $postData['title'],
                            'url' => $postData['url'],
                            'score' => $postData['score'],
                            'subreddit' => $subreddit,
                            'source' => 'reddit'
                        ];
                    }
                }
            }

            return $topics;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Reddit topics', ['error' => $e->getMessage()]);
        }

        return [];
    }

    private function getExistingContentTopics(): array
    {
        return Post::published()
            ->select('title', 'excerpt', 'content')
            ->orderBy('published_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($post) {
                return [
                    'title' => $post->title,
                    'keywords' => $this->extractKeywords($post->title . ' ' . $post->excerpt),
                ];
            })
            ->toArray();
    }

    private function generateSuggestionsFromAI(array $trendingTopics, array $existingTopics): array
    {
        $prompt = $this->buildAIPrompt($trendingTopics, $existingTopics);

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a content strategist for a technology blog. Generate unique, engaging article ideas based on trending topics while avoiding duplication with existing content.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ]);

        if (!$response->successful()) {
            throw new \Exception('AI API request failed: ' . $response->body());
        }

        $content = $response->json()['choices'][0]['message']['content'];
        return $this->parseAISuggestions($content);
    }

    private function buildAIPrompt(array $trendingTopics, array $existingTopics): string
    {
        $trendingText = collect($trendingTopics)->pluck('title')->take(20)->implode(', ');
        $existingText = collect($existingTopics)->pluck('title')->take(10)->implode(', ');

        return "Based on these trending topics: {$trendingText}

Existing content we've already covered: {$existingText}

Generate 10 unique blog post ideas in JSON format. Each idea should include:
- title: Catchy, SEO-friendly title
- description: 2-3 sentence description
- topics: Array of main topics covered
- keywords: Array of SEO keywords
- relevance_score: Float between 0-1 indicating how relevant/trending this topic is

Focus on technology, programming, AI, and web development. Avoid duplicating existing content themes. Make titles engaging and clickable.

Return only valid JSON array.";
    }

    private function parseAISuggestions(string $content): array
    {
        try {
            // Extract JSON from the response
            preg_match('/\[.*\]/s', $content, $matches);

            if (empty($matches)) {
                throw new \Exception('No valid JSON found in AI response');
            }

            $suggestions = json_decode($matches[0], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON in AI response: ' . json_last_error_msg());
            }

            return $suggestions;
        } catch (\Exception $e) {
            Log::error('Failed to parse AI suggestions', [
                'content' => $content,
                'error' => $e->getMessage()
            ]);

            // Return fallback suggestions
            return $this->getFallbackSuggestions();
        }
    }

    private function getFallbackSuggestions(): array
    {
        return [
            [
                'title' => 'The Future of Web Development in 2025',
                'description' => 'Exploring emerging trends and technologies that will shape web development.',
                'topics' => ['web development', 'trends', 'technology'],
                'keywords' => ['web development', '2025 trends', 'frontend', 'backend'],
                'relevance_score' => 0.8
            ],
            [
                'title' => 'Building Scalable APIs with Laravel',
                'description' => 'Best practices for creating robust and scalable REST APIs using Laravel framework.',
                'topics' => ['laravel', 'api', 'backend'],
                'keywords' => ['laravel api', 'scalable', 'rest api', 'php'],
                'relevance_score' => 0.7
            ]
        ];
    }

    private function extractKeywords(string $text): array
    {
        // Simple keyword extraction - in production, use more sophisticated NLP
        $words = str_word_count(strtolower($text), 1);
        $stopWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'];

        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        return array_values(array_unique($keywords));
    }
}
