<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Collection;

class PersonalizedFeedService
{
    private RecommendationService $recommendationService;
    private LeaderboardService $leaderboardService;

    public function __construct()
    {
        $this->recommendationService = new RecommendationService();
        $this->leaderboardService = new LeaderboardService();
    }

    /**
     * Get complete personalized discovery feed for a user
     * Combines recommendations, followed authors, trending, and curated content
     */
    public function getDiscoveryFeed(User $user, $limit = 50): array
    {
        try {
            $feed = [];
            $feedLimit = $limit;

            // 1. Personalized recommendations (40% of feed)
            $personalizedLimit = (int) ceil($feedLimit * 0.40);
            $personalized = $this->recommendationService->getRecommendationsForUser($user, $personalizedLimit);
            $feed = array_merge($feed, $this->formatFeedItems($personalized, 'personalized'));

            // 2. Followed authors' latest posts (30% of feed)
            if ($user->following()->count() > 0) {
                $followedLimit = (int) ceil($feedLimit * 0.30);
                $followedPosts = $this->recommendationService->getFollowedAuthorPosts($user, $followedLimit);
                $feed = array_merge($feed, $this->formatFeedItems($followedPosts, 'followed_author'));
            }

            // 3. Editor's picks / Curated content (20% of feed)
            $curatedLimit = (int) ceil($feedLimit * 0.20);
            $curated = $this->recommendationService->getEditorsPicks($curatedLimit);
            $feed = array_merge($feed, $this->formatFeedItems($curated, 'editors_pick'));

            // 4. Trending posts (10% of feed)
            $trendingLimit = (int) ceil($feedLimit * 0.10);
            $trending = $this->recommendationService->getTrendingPosts($trendingLimit);
            $feed = array_merge($feed, $this->formatFeedItems($trending, 'trending'));

            // Remove duplicates and limit
            $feed = $this->deduplicate($feed);
            $feed = array_slice($feed, 0, $limit);

            return [
                'success' => true,
                'count' => count($feed),
                'feed' => $feed,
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to generate discovery feed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to load feed',
                'feed' => [],
            ];
        }
    }

    /**
     * Get main feed for user (what they see on home page)
     */
    public function getMainFeed(User $user, $limit = 20): array
    {
        try {
            // Main feed prioritizes: followed authors > trending > personalized recommendations
            $feed = [];

            // 1. Latest from followed authors (50%)
            $followedLimit = (int) ceil($limit * 0.50);
            $followedPosts = $this->recommendationService->getFollowedAuthorPosts($user, $followedLimit);
            $feed = array_merge($feed, $this->formatFeedItems($followedPosts, 'followed_author'));

            // 2. Trending posts (30%)
            $trendingLimit = (int) ceil($limit * 0.30);
            $trending = $this->recommendationService->getTrendingPosts($trendingLimit);
            $feed = array_merge($feed, $this->formatFeedItems($trending, 'trending'));

            // 3. Personalized (20%)
            $personalizedLimit = (int) ceil($limit * 0.20);
            $personalized = $this->recommendationService->getRecommendationsForUser($user, $personalizedLimit);
            $feed = array_merge($feed, $this->formatFeedItems($personalized, 'personalized'));

            $feed = $this->deduplicate($feed);
            $feed = array_slice($feed, 0, $limit);

            return [
                'success' => true,
                'count' => count($feed),
                'feed' => $feed,
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to generate main feed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to load feed',
                'feed' => [],
            ];
        }
    }

    /**
     * Get reading list / saved for later feed
     */
    public function getReadingList(User $user, $limit = 50): array
    {
        try {
            // Get bookmarked posts (articles saved for reading)
            $bookmarkedPostIds = $user->interactions()
                ->where('type', 'bookmark')
                ->pluck('post_id')
                ->unique();

            if ($bookmarkedPostIds->isEmpty()) {
                return [
                    'success' => true,
                    'count' => 0,
                    'feed' => [],
                    'message' => 'No saved articles yet. Bookmark articles to read later!',
                ];
            }

            $readingList = Post::published()
                ->whereIn('id', $bookmarkedPostIds)
                ->orderBy('published_at', 'desc')
                ->with(['author', 'category'])
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'count' => $readingList->count(),
                'feed' => $this->formatFeedItems($readingList, 'reading_list'),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get reading list', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to load reading list',
                'feed' => [],
            ];
        }
    }

    /**
     * Get category-specific feed
     */
    public function getCategoryFeed(User $user, $categoryId, $limit = 30): array
    {
        try {
            $posts = Post::published()
                ->where('category_id', $categoryId)
                ->orderBy('published_at', 'desc')
                ->with(['author', 'tags', 'category'])
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'count' => $posts->count(),
                'feed' => $this->formatFeedItems($posts, 'category'),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get category feed', [
                'user_id' => $user->id,
                'category_id' => $categoryId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to load category feed',
                'feed' => [],
            ];
        }
    }

    /**
     * Get tag-specific feed
     */
    public function getTagFeed($tagId, $limit = 30): array
    {
        try {
            $posts = Post::published()
                ->whereHas('tags', fn($q) => $q->where('id', $tagId))
                ->orderBy('published_at', 'desc')
                ->with(['author', 'tags', 'category'])
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'count' => $posts->count(),
                'feed' => $this->formatFeedItems($posts, 'tag'),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get tag feed', [
                'tag_id' => $tagId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to load tag feed',
                'feed' => [],
            ];
        }
    }

    /**
     * Get "read more from author" feed
     */
    public function getAuthorFeed(User $author, $limit = 20, $excludePostId = null): array
    {
        try {
            $query = Post::published()
                ->where('user_id', $author->id)
                ->orderBy('published_at', 'desc');

            if ($excludePostId) {
                $query->where('id', '!=', $excludePostId);
            }

            $posts = $query->with(['author', 'tags', 'category'])
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'count' => $posts->count(),
                'feed' => $this->formatFeedItems($posts, 'author'),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to get author feed', [
                'author_id' => $author->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to load author posts',
                'feed' => [],
            ];
        }
    }

    // Private helper methods

    private function formatFeedItems($posts, $type): array
    {
        return $posts->map(function ($post) use ($type) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'excerpt' => $post->excerpt ?: substr(strip_tags($post->content), 0, 150) . '...',
                'slug' => $post->slug,
                'type' => $type,
                'author' => [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                    'username' => $post->author->username,
                    'profile_image_url' => $post->author->profile_image_url,
                ],
                'category' => [
                    'id' => $post->category?->id,
                    'name' => $post->category?->name,
                    'slug' => $post->category?->slug,
                ],
                'featured_image_url' => $post->featured_image_url,
                'read_time_minutes' => $post->read_time_minutes,
                'published_at' => $post->published_at->diffForHumans(),
                'views_count' => $post->views_count,
                'likes_count' => $post->likes_count ?? 0,
                'comments_count' => $post->comments()->count(),
            ];
        })->toArray();
    }

    private function deduplicate($feed): array
    {
        $seen = [];
        $deduplicated = [];

        foreach ($feed as $item) {
            if (!in_array($item['id'], $seen)) {
                $seen[] = $item['id'];
                $deduplicated[] = $item;
            }
        }

        return $deduplicated;
    }
}
