<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostInteraction;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Get personalized post recommendations for a user
     */
    public function getRecommendationsForUser(User $user, $limit = 5): Collection
    {
        try {
            // Get user's interaction history
            $likedPostIds = PostInteraction::where('user_id', $user->id)
                ->where('type', 'like')
                ->pluck('post_id')
                ->toArray();

            $viewedPostIds = PostInteraction::where('user_id', $user->id)
                ->where('type', 'view')
                ->pluck('post_id')
                ->toArray();

            $allInteractedIds = array_merge($likedPostIds, $viewedPostIds);

            // If user has no history, show trending posts
            if (empty($allInteractedIds)) {
                return $this->getTrendingPosts($limit);
            }
        } catch (\Exception $e) {
            // If PostInteraction table doesn't exist yet, show trending posts
            \Log::warning('PostInteraction table not ready: ' . $e->getMessage());
            return $this->getTrendingPosts($limit);
        }

        // Get user's favorite categories and tags
        $favoriteCategories = Post::whereIn('id', $likedPostIds)
            ->pluck('category_id')
            ->countBy()
            ->sort()
            ->reverse()
            ->take(3)
            ->keys();

        $favoriteTags = Post::whereIn('id', $likedPostIds)
            ->with('tags')
            ->get()
            ->pluck('tags')
            ->flatten()
            ->pluck('id')
            ->countBy()
            ->sort()
            ->reverse()
            ->take(5)
            ->keys();

        // Score posts based on multiple factors
        $posts = Post::published()
            ->whereNotIn('id', $allInteractedIds)
            ->with(['category', 'tags', 'author'])
            ->get();

        $scored = $posts->map(function ($post) use ($favoriteCategories, $favoriteTags, $likedPostIds) {
            $score = 0;

            // Category match (30%)
            if ($favoriteCategories->contains($post->category_id)) {
                $score += 30;
            }

            // Tag match (25%)
            $matchingTags = $post->tags->whereIn('id', $favoriteTags)->count();
            $score += min(25, $matchingTags * 5);

            // Author match - follow authors of liked posts (15%)
            $likedPosts = Post::whereIn('id', $likedPostIds)->get();
            $likedAuthors = $likedPosts->pluck('author_id')->unique();
            if ($likedAuthors->contains($post->author_id)) {
                $score += 15;
            }

            // Engagement score (20%)
            $engagementScore = $this->calculateEngagementScore($post);
            $score += min(20, $engagementScore);

            // Trending boost (10%)
            if ($this->isTrending($post)) {
                $score += 10;
            }

            // Recency bonus (5%)
            $daysOld = now()->diffInDays($post->published_at);
            if ($daysOld < 7) {
                $score += 5;
            } elseif ($daysOld < 30) {
                $score += 2;
            }

            return [
                'post' => $post,
                'score' => $score,
            ];
        });

        // Sort by score and return
        return $scored
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('post');
    }

    /**
     * Get similar posts based on a given post
     */
    public function getSimilarPosts(Post $post, $limit = 5): Collection
    {
        $categoryId = $post->category_id;
        $tagIds = $post->tags->pluck('id')->toArray();
        $authorId = $post->author_id;

        $similar = Post::published()
            ->where('id', '!=', $post->id)
            ->with(['category', 'tags', 'author'])
            ->get()
            ->map(function ($candidate) use ($categoryId, $tagIds, $authorId) {
                $score = 0;

                // Same category (40%)
                if ($candidate->category_id === $categoryId) {
                    $score += 40;
                }

                // Shared tags (40%)
                $sharedTags = $candidate->tags
                    ->whereIn('id', $tagIds)
                    ->count();
                $score += min(40, $sharedTags * 10);

                // Same author (10%)
                if ($candidate->author_id === $authorId) {
                    $score += 10;
                }

                // Engagement (10%)
                $engagementScore = $this->calculateEngagementScore($candidate);
                $score += min(10, $engagementScore);

                return [
                    'post' => $candidate,
                    'score' => $score,
                ];
            })
            ->filter(fn($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('post');

        // If not enough similar posts, fill with trending
        if ($similar->count() < $limit) {
            $remaining = $limit - $similar->count();
            $trending = $this->getTrendingPosts($remaining);
            $similar = $similar->merge($trending);
        }

        return $similar;
    }

    /**
     * Get trending posts
     */
    public function getTrendingPosts($limit = 5): Collection
    {
        return Post::published()
            ->orderByDesc('views_count')
            ->with(['category', 'tags', 'author'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get posts by followed authors
     */
    public function getFollowedAuthorPosts(User $user, $limit = 5): Collection
    {
        $followedAuthorIds = $user->following()
            ->where('followable_type', 'App\\Models\\User')
            ->pluck('followable_id')
            ->toArray();

        if (empty($followedAuthorIds)) {
            return collect();
        }

        return Post::published()
            ->whereIn('author_id', $followedAuthorIds)
            ->orderByDesc('published_at')
            ->with(['category', 'tags', 'author'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get editor's picks - curated recommendations
     */
    public function getEditorsPicks($limit = 5): Collection
    {
        // High engagement + recent posts = editor's picks
        return Post::published()
            ->where('published_at', '>=', now()->subDays(60))
            ->with(['category', 'tags', 'author'])
            ->selectRaw('posts.*,
                        (likes_count + comments_count) / GREATEST(views_count, 1) as engagement_rate')
            ->orderByDesc('engagement_rate')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate engagement score for a post
     */
    private function calculateEngagementScore(Post $post): float
    {
        $views = $post->views_count ?? 1;
        $engagement = ($post->likes_count ?? 0) + ($post->comments()->count() ?? 0);

        return $views > 0 ? round(($engagement / $views) * 100, 2) : 0;
    }

    /**
     * Check if post is trending
     */
    private function isTrending(Post $post): bool
    {
        try {
            // Post is trending if it has high engagement in the last 7 days
            $recentEngagement = PostInteraction::where('post_id', $post->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            return $recentEngagement > 10;
        } catch (\Exception $e) {
            // If PostInteraction table doesn't exist, fall back to views
            return $post->views_count > 50;
        }
    }

    /**
     * Log recommendation for analytics
     */
    public function logRecommendationShown(User $user, Post $post, string $type): void
    {
        // Could be stored in a table for tracking recommendation effectiveness
        // For now, just log it
        \Log::info('Recommendation shown', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'type' => $type,
        ]);
    }

    /**
     * Track recommendation click
     */
    public function trackRecommendationClick(User $user, Post $post, string $type): void
    {
        \Log::info('Recommendation clicked', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'type' => $type,
        ]);
    }
}
