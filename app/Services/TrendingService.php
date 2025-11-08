<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TrendingService
{
    /**
     * Get trending posts globally
     */
    public function getTrendingPosts(int $limit = 10, string $period = '7days'): Collection
    {
        $days = match($period) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            default => 7,
        };

        $startDate = now()->subDays($days);

        return Post::published()
            ->where('created_at', '>=', $startDate)
            ->with(['author', 'category', 'tags'])
            ->withCount('likes')
            ->withCount('comments')
            ->withCount('views')
            ->selectRaw('
                posts.*,
                CASE
                    WHEN (likes_count * 5 + comments_count * 3 + views_count * 0.5) > 0
                    THEN (likes_count * 5 + comments_count * 3 + views_count * 0.5)
                    ELSE 0
                END as trending_score
            ')
            ->orderByRaw('trending_score DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular posts (all time)
     */
    public function getPopularPosts(int $limit = 10): Collection
    {
        return Post::published()
            ->with(['author', 'category'])
            ->withCount('likes')
            ->withCount('comments')
            ->withCount('views')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending posts by category
     */
    public function getTrendingByCategory(Category $category, int $limit = 8, string $period = '7days'): Collection
    {
        $days = match($period) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            default => 7,
        };

        $startDate = now()->subDays($days);

        return Post::published()
            ->where('category_id', $category->id)
            ->where('created_at', '>=', $startDate)
            ->with(['author', 'tags'])
            ->withCount('likes')
            ->withCount('comments')
            ->withCount('views')
            ->selectRaw('
                posts.*,
                (likes_count * 5 + comments_count * 3 + views_count * 0.5) as trending_score
            ')
            ->orderByRaw('trending_score DESC')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending tags
     */
    public function getTrendingTags(int $limit = 15, string $period = '7days'): Collection
    {
        $days = match($period) {
            '24hours' => 1,
            '7days' => 7,
            '30days' => 30,
            default => 7,
        };

        $startDate = now()->subDays($days);

        return \DB::table('tags')
            ->join('post_tag', 'tags.id', '=', 'post_tag.tag_id')
            ->join('posts', 'post_tag.post_id', '=', 'posts.id')
            ->where('posts.created_at', '>=', $startDate)
            ->where('posts.status', 'published')
            ->select('tags.*')
            ->selectRaw('COUNT(*) as usage_count')
            ->selectRaw('SUM(COALESCE(posts.view_count, 0)) as total_views')
            ->groupBy('tags.id', 'tags.name', 'tags.slug', 'tags.created_at', 'tags.updated_at')
            ->orderBy('usage_count', 'desc')
            ->orderBy('total_views', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending authors (by engagement)
     */
    public function getTrendingAuthors(int $limit = 10): Collection
    {
        return \DB::table('users')
            ->join('posts', 'users.id', '=', 'posts.author_id')
            ->where('posts.status', 'published')
            ->where('posts.created_at', '>=', now()->subDays(30))
            ->select('users.*')
            ->selectRaw('COUNT(posts.id) as published_posts')
            ->selectRaw('SUM(COALESCE(posts.view_count, 0)) as total_views')
            ->selectRaw('SUM(COALESCE(posts.likes_count, 0)) as total_likes')
            ->selectRaw('(SUM(COALESCE(posts.likes_count, 0)) * 5 + SUM(COALESCE(posts.view_count, 0)) * 0.5) as engagement_score')
            ->groupBy(
                'users.id', 'users.name', 'users.email', 'users.password',
                'users.avatar', 'users.bio', 'users.website', 'users.twitter',
                'users.linkedin', 'users.is_active', 'users.last_seen_at',
                'users.email_verified_at', 'users.remember_token', 'users.created_at',
                'users.updated_at'
            )
            ->orderBy('engagement_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending series
     */
    public function getTrendingSeries(int $limit = 6): array
    {
        $series = Post::where('status', 'published')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('series_slug')
            ->select('series_slug', 'series_title', 'series_description')
            ->selectRaw('COUNT(*) as part_count')
            ->selectRaw('SUM(COALESCE(view_count, 0)) as total_views')
            ->selectRaw('SUM(COALESCE(likes_count, 0)) as total_likes')
            ->selectRaw('(SUM(COALESCE(likes_count, 0)) * 5 + SUM(COALESCE(view_count, 0)) * 0.5) as score')
            ->groupBy('series_slug', 'series_title', 'series_description')
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        return $series;
    }

    /**
     * Get growth stats for a post
     */
    public function getPostGrowthStats(Post $post, int $days = 7): array
    {
        $startDate = now()->subDays($days);

        $views = $post->views()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date')
            ->get();

        $likes = $post->likes()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date')
            ->get();

        return [
            'views' => $views,
            'likes' => $likes,
            'total_views' => $post->view_count ?? 0,
            'total_likes' => $post->likes_count ?? 0,
            'engagement_rate' => $post->view_count > 0
                ? round((($post->likes_count + $post->comments_count) / $post->view_count) * 100, 2)
                : 0,
        ];
    }

    /**
     * Cache trending data (call periodically)
     */
    public function refreshTrendingCache(): void
    {
        \Cache::put('trending:posts:7days', $this->getTrendingPosts(20, '7days'), minutes: 60);
        \Cache::put('trending:posts:24hours', $this->getTrendingPosts(20, '24hours'), minutes: 30);
        \Cache::put('trending:posts:popular', $this->getPopularPosts(20), hours: 24);
        \Cache::put('trending:tags', $this->getTrendingTags(20), hours: 12);
        \Cache::put('trending:authors', $this->getTrendingAuthors(20), hours: 24);
    }
}
