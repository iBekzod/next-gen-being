<?php

namespace App\Services\AI;

use App\Models\AIRecommendation;
use App\Models\LearningPath;
use App\Models\Post;
use App\Models\TutorialProgress;
use App\Models\User;
use Illuminate\Support\Collection;

class AIRecommendationEngine
{
    /**
     * Generate recommendations for a user based on their progress
     */
    public function generateRecommendations(User $user, int $limit = 5): Collection
    {
        $recommendations = collect();

        // Get user's progress data
        $userStats = $this->getUserStats($user);
        $userProgress = TutorialProgress::forUser($user->id)->get();
        $completedPostIds = $userProgress->where('completed', true)->pluck('post_id')->toArray();

        // Generate different types of recommendations
        $recommendations->push(...$this->getNextTutorialRecommendations($user, $completedPostIds, 2));
        $recommendations->push(...$this->getSkillGapRecommendations($user, $userStats, 2));
        $recommendations->push(...$this->getRelatedContentRecommendations($user, $completedPostIds, 1));

        // Shuffle and limit
        return $recommendations
            ->shuffle()
            ->take($limit)
            ->map(fn($rec) => $this->saveRecommendation($user, $rec));
    }

    /**
     * Get next tutorial recommendations based on series progression
     */
    protected function getNextTutorialRecommendations(User $user, array $completedPostIds, int $limit): array
    {
        $recommendations = [];

        // Get user's progress by series
        $userProgress = TutorialProgress::forUser($user->id)->get();
        $seriesByProgress = $userProgress->groupBy('series_slug');

        foreach ($seriesByProgress as $seriesSlug => $seriesParts) {
            // Get series info
            $seriesTotal = Post::where('series_slug', $seriesSlug)->count();
            $seriesCompleted = $seriesParts->where('completed', true)->count();

            // If series not completed, recommend next part
            if ($seriesCompleted < $seriesTotal) {
                $nextPart = Post::where('series_slug', $seriesSlug)
                    ->where('series_part', '>', $seriesCompleted)
                    ->orderBy('series_part', 'asc')
                    ->first();

                if ($nextPart && !in_array($nextPart->id, $completedPostIds)) {
                    $confidenceScore = 0.9 - ($seriesCompleted / $seriesTotal) * 0.1;

                    $recommendations[] = [
                        'recommendation_type' => 'next_tutorial',
                        'post_id' => $nextPart->id,
                        'title' => 'Continue: ' . $nextPart->title,
                        'description' => "You've completed {$seriesCompleted} parts of the {$seriesSlug} series. Continue with the next part.",
                        'reason' => "You're making progress in the {$seriesSlug} series.",
                        'confidence_score' => $confidenceScore,
                        'metadata' => [
                            'series_slug' => $seriesSlug,
                            'current_progress' => $seriesCompleted,
                            'total_parts' => $seriesTotal,
                        ],
                    ];

                    if (count($recommendations) >= $limit) {
                        break;
                    }
                }
            }
        }

        return $recommendations;
    }

    /**
     * Get skill gap recommendations based on user's learning level
     */
    protected function getSkillGapRecommendations(User $user, array $userStats, int $limit): array
    {
        $recommendations = [];
        $skillLevel = $this->getUserSkillLevel($userStats);

        // Get posts at the next skill level up
        $nextLevel = match($skillLevel) {
            'beginner' => 'intermediate',
            'intermediate' => 'advanced',
            'advanced' => 'expert',
            default => 'intermediate',
        };

        $nextPosts = Post::where('difficulty', $nextLevel)
            ->whereNotIn('id', TutorialProgress::forUser($user->id)->pluck('post_id')->toArray())
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        foreach ($nextPosts as $post) {
            $recommendations[] = [
                'recommendation_type' => 'skill_gap',
                'post_id' => $post->id,
                'title' => 'Challenge Yourself: ' . $post->title,
                'description' => "Based on your progress, you're ready to tackle {$nextLevel} level content.",
                'reason' => "You've mastered {$skillLevel} content and are ready for the next level.",
                'confidence_score' => 0.75,
                'metadata' => [
                    'current_level' => $skillLevel,
                    'recommended_level' => $nextLevel,
                ],
            ];
        }

        return $recommendations;
    }

    /**
     * Get related content recommendations
     */
    protected function getRelatedContentRecommendations(User $user, array $completedPostIds, int $limit): array
    {
        $recommendations = [];

        // Get user's most recent completed post
        $lastCompleted = TutorialProgress::forUser($user->id)
            ->where('completed', true)
            ->orderByDesc('completed_at')
            ->first();

        if ($lastCompleted && $lastCompleted->post) {
            // Find related posts by category or tags
            $relatedPosts = Post::where('category_id', $lastCompleted->post->category_id)
                ->whereNotIn('id', $completedPostIds)
                ->where('id', '!=', $lastCompleted->post_id)
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            foreach ($relatedPosts as $post) {
                $recommendations[] = [
                    'recommendation_type' => 'related_content',
                    'post_id' => $post->id,
                    'title' => 'Related: ' . $post->title,
                    'description' => "Since you read about {$lastCompleted->post->title}, you might enjoy this related content.",
                    'reason' => "Based on your recent activity.",
                    'confidence_score' => 0.65,
                    'metadata' => [
                        'related_to_post_id' => $lastCompleted->post_id,
                        'category' => $lastCompleted->post->category?->name,
                    ],
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Get user statistics
     */
    protected function getUserStats(User $user): array
    {
        $progress = TutorialProgress::forUser($user->id)->get();

        return [
            'total_parts_read' => $progress->count(),
            'total_parts_completed' => $progress->where('completed', true)->count(),
            'total_hours_spent' => round($progress->sum('time_spent_minutes') / 60, 1),
            'total_achievements' => $user->achievements->count(),
            'total_series_started' => $progress->groupBy('series_slug')->count(),
        ];
    }

    /**
     * Determine user's current skill level
     */
    protected function getUserSkillLevel(array $stats): string
    {
        $completionPercentage = $stats['total_parts_read'] > 0
            ? ($stats['total_parts_completed'] / $stats['total_parts_read']) * 100
            : 0;

        if ($stats['total_parts_completed'] >= 20 && $completionPercentage >= 80) {
            return 'expert';
        } elseif ($stats['total_parts_completed'] >= 10 && $completionPercentage >= 70) {
            return 'advanced';
        } elseif ($stats['total_parts_completed'] >= 5 && $completionPercentage >= 60) {
            return 'intermediate';
        }

        return 'beginner';
    }

    /**
     * Save a recommendation to the database
     */
    protected function saveRecommendation(User $user, array $data): AIRecommendation
    {
        return AIRecommendation::create(array_merge($data, [
            'user_id' => $user->id,
        ]));
    }

    /**
     * Get active recommendations for a user
     */
    public function getActiveRecommendations(User $user, int $limit = 5): Collection
    {
        return AIRecommendation::forUser($user->id)
            ->active()
            ->orderByDesc('confidence_score')
            ->limit($limit)
            ->get();
    }

    /**
     * Dismiss a recommendation
     */
    public function dismissRecommendation(AIRecommendation $recommendation): void
    {
        $recommendation->dismiss();
    }

    /**
     * Mark recommendation as acted upon
     */
    public function markAsActedOn(AIRecommendation $recommendation): void
    {
        $recommendation->markAsActedOn();
    }

    /**
     * Get recommendation performance metrics
     */
    public function getMetrics(User $user): array
    {
        $totalGenerated = AIRecommendation::forUser($user->id)->count();
        $totalActedOn = AIRecommendation::forUser($user->id)->actedOn()->count();
        $totalDismissed = AIRecommendation::forUser($user->id)->dismissed()->count();

        return [
            'total_recommendations' => $totalGenerated,
            'acted_on' => $totalActedOn,
            'dismissed' => $totalDismissed,
            'engagement_rate' => $totalGenerated > 0 ? round(($totalActedOn / $totalGenerated) * 100, 2) : 0,
            'dismiss_rate' => $totalGenerated > 0 ? round(($totalDismissed / $totalGenerated) * 100, 2) : 0,
        ];
    }
}
