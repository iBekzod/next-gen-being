<?php

namespace App\Services;

use App\Models\ContentIdea;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CreatorToolsService
{
    /**
     * Generate content ideas using AI
     */
    public function generateContentIdeas(User $creator, array $params = []): array
    {
        try {
            $topic = $params['topic'] ?? 'technology';
            $contentType = $params['content_type'] ?? 'medium_post';
            $count = $params['count'] ?? 5;
            $useAI = $params['use_ai'] ?? true;

            $ideas = [];

            if ($useAI) {
                // Call Claude API for AI-powered ideas
                $response = Http::withToken(config('services.anthropic.key'))
                    ->post('https://api.anthropic.com/v1/messages', [
                        'model' => 'claude-opus-4-1-20250805',
                        'max_tokens' => 2000,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => "Generate {$count} creative and trending content ideas for a {$topic} blog.
                                Content type: {$contentType}.
                                For each idea, provide a JSON object with: title, description, keywords (array), target_audience.
                                Return a JSON array only, no explanation.",
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $content = $response->json('content')[0]['text'] ?? '[]';
                    $ideas = json_decode($content, true) ?? [];
                }
            } else {
                // Generate trending ideas from existing posts
                $ideas = $this->generateTrendingIdeas($topic, $contentType, $count);
            }

            // Save ideas to database
            $savedIdeas = [];
            foreach ($ideas as $idea) {
                $contentIdea = ContentIdea::create([
                    'user_id' => $creator->id,
                    'title' => $idea['title'] ?? '',
                    'description' => $idea['description'] ?? '',
                    'topic' => $topic,
                    'content_type' => $contentType,
                    'target_audience' => $idea['target_audience'] ?? 'general',
                    'keywords' => $idea['keywords'] ?? [],
                    'status' => 'active',
                    'source' => $useAI ? 'ai_generated' : 'trending_analysis',
                    'trending_score' => rand(40, 95),
                    'difficulty_score' => $this->calculateDifficulty($contentType),
                    'priority' => 'medium',
                ]);

                $savedIdeas[] = $this->formatIdea($contentIdea);
            }

            Log::info('Content ideas generated', [
                'creator_id' => $creator->id,
                'count' => count($savedIdeas),
                'topic' => $topic,
            ]);

            return [
                'success' => true,
                'ideas' => $savedIdeas,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate content ideas', [
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate trending ideas from analysis
     */
    private function generateTrendingIdeas(string $topic, string $contentType, int $count): array
    {
        // Analyze trending posts in the topic
        $trendingPosts = Post::where('title', 'ilike', "%{$topic}%")
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        $ideas = [];
        foreach ($trendingPosts->take($count) as $post) {
            $ideas[] = [
                'title' => 'Exploring: ' . $post->title,
                'description' => 'A deep dive into the concepts from: ' . substr($post->title, 0, 50),
                'keywords' => $post->tags ?? [],
                'target_audience' => 'professionals',
            ];
        }

        return $ideas;
    }

    /**
     * Generate content outline from idea
     */
    public function generateOutline(ContentIdea $idea): array
    {
        try {
            $outline = [
                'introduction' => [
                    'hook' => 'Start with an engaging hook',
                    'problem_statement' => 'Define the problem',
                    'value_proposition' => 'Explain what readers will learn',
                ],
                'main_sections' => [
                    ['title' => 'Core Concept 1', 'points' => []],
                    ['title' => 'Core Concept 2', 'points' => []],
                    ['title' => 'Core Concept 3', 'points' => []],
                ],
                'conclusion' => [
                    'summary' => 'Recap key points',
                    'call_to_action' => 'Next steps for reader',
                ],
            ];

            $idea->update([
                'outline' => $outline,
                'status' => 'in_progress',
            ]);

            return [
                'success' => true,
                'outline' => $outline,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate outline', [
                'idea_id' => $idea->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyze SEO for content
     */
    public function analyzeSEO(string $title, array $keywords, string $content = ''): array
    {
        $wordCount = str_word_count($content);
        $keywordDensity = 0;

        if ($wordCount > 0) {
            $keywordMatches = 0;
            foreach ($keywords as $keyword) {
                $keywordMatches += substr_count(strtolower($content), strtolower($keyword));
            }
            $keywordDensity = round(($keywordMatches / $wordCount) * 100, 2);
        }

        $score = 0;
        $issues = [];

        // Title analysis
        if (strlen($title) >= 30 && strlen($title) <= 60) {
            $score += 20;
        } else {
            $issues[] = 'Title length should be 30-60 characters';
        }

        // Keyword usage
        if ($keywordDensity >= 1 && $keywordDensity <= 3) {
            $score += 25;
        } elseif ($keywordDensity > 0) {
            $issues[] = 'Keyword density is ' . $keywordDensity . '% (target: 1-3%)';
        } else {
            $issues[] = 'No keywords found in content';
        }

        // Content length
        if ($wordCount >= 300) {
            $score += 20;
        } else {
            $issues[] = 'Content should be at least 300 words';
        }

        // Meta description
        $score += 15; // Placeholder

        // Internal links, headers, etc
        $score += 20; // Placeholder

        return [
            'score' => $score,
            'keyword_density' => $keywordDensity,
            'word_count' => $wordCount,
            'issues' => $issues,
            'recommendations' => $this->generateSEORecommendations($score, $issues),
        ];
    }

    /**
     * Generate SEO recommendations
     */
    private function generateSEORecommendations(int $score, array $issues): array
    {
        $recommendations = [];

        if ($score >= 80) {
            $recommendations[] = 'Your SEO score is excellent!';
        } elseif ($score >= 60) {
            $recommendations[] = 'Good SEO foundation. Fix issues above to improve.';
        } else {
            $recommendations[] = 'Several SEO issues to address. Follow recommendations above.';
        }

        return $recommendations;
    }

    /**
     * Get audience insights
     */
    public function getAudienceInsights(User $creator): array
    {
        try {
            $posts = Post::where('user_id', $creator->id)->get();

            $insights = [
                'total_posts' => $posts->count(),
                'total_views' => (int) $posts->sum('views_count'),
                'total_likes' => (int) $posts->sum('likes_count'),
                'total_comments' => (int) $posts->sum('comments_count'),
                'average_views_per_post' => $posts->count() > 0 ? round($posts->sum('views_count') / $posts->count()) : 0,
                'average_engagement' => $posts->count() > 0
                    ? round(
                        ($posts->sum('likes_count') + $posts->sum('comments_count')) / $posts->count()
                    )
                    : 0,
                'top_posts' => $posts->sortByDesc('views_count')
                    ->take(5)
                    ->map(fn($p) => [
                        'title' => $p->title,
                        'views' => $p->views_count,
                        'engagement' => $p->likes_count + $p->comments_count,
                    ])
                    ->toArray(),
                'engagement_rate' => $posts->count() > 0
                    ? round(
                        (($posts->sum('likes_count') + $posts->sum('comments_count')) /
                        max($posts->sum('views_count'), 1)) * 100,
                        2
                    )
                    : 0,
            ];

            return [
                'success' => true,
                'insights' => $insights,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get audience insights', [
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get content performance report
     */
    public function getPerformanceReport(User $creator, int $days = 30): array
    {
        try {
            $startDate = now()->subDays($days);

            $posts = Post::where('user_id', $creator->id)
                ->where('published_at', '>=', $startDate)
                ->orderByDesc('published_at')
                ->get();

            $report = [
                'period_days' => $days,
                'posts_published' => $posts->count(),
                'total_views' => (int) $posts->sum('views_count'),
                'total_engagement' => (int) ($posts->sum('likes_count') + $posts->sum('comments_count')),
                'average_views' => $posts->count() > 0 ? round($posts->sum('views_count') / $posts->count()) : 0,
                'posts' => $posts->map(fn($p) => [
                    'title' => $p->title,
                    'views' => $p->views_count,
                    'likes' => $p->likes_count,
                    'comments' => $p->comments_count,
                    'shares' => $p->shares_count ?? 0,
                    'published_at' => $p->published_at->format('Y-m-d'),
                ])->toArray(),
            ];

            return [
                'success' => true,
                'report' => $report,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate performance report', [
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get content ideas for creator
     */
    public function getCreatorIdeas(User $creator, string $status = 'active', int $limit = 20): array
    {
        $ideas = ContentIdea::byCreator($creator)
            ->where('status', $status)
            ->orderByDesc('trending_score')
            ->limit($limit)
            ->get();

        return [
            'count' => $ideas->count(),
            'ideas' => $ideas->map(fn($i) => $this->formatIdea($i))->toArray(),
        ];
    }

    /**
     * Update idea priority/status
     */
    public function updateIdea(ContentIdea $idea, array $data): array
    {
        try {
            $idea->update([
                'priority' => $data['priority'] ?? $idea->priority,
                'status' => $data['status'] ?? $idea->status,
                'notes' => $data['notes'] ?? $idea->notes,
            ]);

            return [
                'success' => true,
                'idea' => $this->formatIdea($idea),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update idea', [
                'idea_id' => $idea->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete idea
     */
    public function deleteIdea(ContentIdea $idea): array
    {
        try {
            $idea->delete();

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error('Failed to delete idea', [
                'idea_id' => $idea->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate difficulty score based on content type
     */
    private function calculateDifficulty(string $contentType): int
    {
        return match ($contentType) {
            'short_post' => 20,
            'medium_post' => 40,
            'long_form' => 60,
            'tutorial' => 75,
            'case_study' => 70,
            'news' => 15,
            default => 50,
        };
    }

    /**
     * Format idea for response
     */
    private function formatIdea(ContentIdea $idea): array
    {
        return [
            'id' => $idea->id,
            'title' => $idea->title,
            'description' => $idea->description,
            'topic' => $idea->topic,
            'content_type' => $idea->content_type,
            'target_audience' => $idea->target_audience,
            'keywords' => $idea->keywords,
            'status' => $idea->status,
            'source' => $idea->source,
            'priority' => $idea->priority,
            'trending_score' => $idea->trending_score,
            'difficulty' => $idea->difficulty_level,
            'estimated_read_time' => $idea->estimated_read_time,
            'estimated_word_count' => $idea->estimated_word_count,
            'outline' => $idea->outline,
            'created_at' => $idea->created_at->toIso8601String(),
        ];
    }
}
