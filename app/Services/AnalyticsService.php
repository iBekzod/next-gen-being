<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PostAnalytic;
use App\Models\AuthorStat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * Get dashboard overview stats for an author
     */
    public function getAuthorDashboardStats(User $author): array
    {
        $authorStats = AuthorStat::where('author_id', $author->id)->first();

        if (!$authorStats) {
            // Create default if doesn't exist
            $authorStats = $this->refreshAuthorStats($author);
        }

        return [
            'total_posts' => $authorStats->total_posts,
            'total_views' => $authorStats->total_views,
            'total_likes' => $authorStats->total_likes,
            'total_comments' => $authorStats->total_comments,
            'total_followers' => $author->followers()->count(),
            'engagement_rate' => $authorStats->engagement_rate,
            'avg_post_views' => $authorStats->avg_post_views,
            'last_post_date' => $authorStats->last_post_date,
            'top_topics' => $authorStats->top_topics ?? [],
        ];
    }

    /**
     * Get post-specific analytics
     */
    public function getPostAnalytics(Post $post, $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $analytics = PostAnalytic::where('post_id', $post->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date')
            ->get();

        return [
            'total_views' => (int) $post->views_count ?? 0,
            'total_likes' => (int) $post->likes_count ?? 0,
            'total_comments' => (int) $post->comments()->count(),
            'total_shares' => (int) $post->shares_count ?? 0,
            'engagement_rate' => $this->calculateEngagementRate($post),
            'avg_read_time' => $this->getAverageReadTime($post),
            'daily_data' => $this->formatDailyData($analytics),
            'top_referrers' => $this->getTopReferrers($post, 5),
            'geo_breakdown' => $this->getGeoBreakdown($post),
            'device_breakdown' => $this->getDeviceBreakdown($post),
            'traffic_sources' => $this->getTrafficSources($post),
            'trending_rank' => $this->getTrendingRank($post),
            'growth_rate' => $this->calculateGrowthRate($post, $days),
        ];
    }

    /**
     * Get trending posts for an author
     */
    public function getAuthorTrendingPosts(User $author, $limit = 5): Collection
    {
        return Post::where('author_id', $author->id)
            ->published()
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'views' => $post->views_count ?? 0,
                    'likes' => $post->likes_count ?? 0,
                    'comments' => $post->comments()->count(),
                    'engagement_rate' => $this->calculateEngagementRate($post),
                    'published_at' => $post->published_at,
                    'slug' => $post->slug,
                ];
            });
    }

    /**
     * Get audience insights
     */
    public function getAudienceInsights(User $author): array
    {
        $posts = Post::where('author_id', $author->id)->published()->get();

        // Collect all geo data from all posts
        $geoData = [];
        foreach ($posts as $post) {
            $analytics = PostAnalytic::where('post_id', $post->id)->latest('date')->first();
            if ($analytics && $analytics->geo_data) {
                $geoData = array_merge($geoData, $analytics->geo_data);
            }
        }

        // Sum up geo data
        $topCountries = collect($geoData)
            ->sort()
            ->reverse()
            ->take(5);

        return [
            'total_readers' => $author->followers()->count(),
            'active_readers_7d' => $this->getActiveReadersLastDays($author, 7),
            'active_readers_30d' => $this->getActiveReadersLastDays($author, 30),
            'avg_session_duration' => $this->getAverageSessionDuration($author),
            'top_countries' => $topCountries->toArray(),
            'retention_rate' => $this->getRetentionRate($author),
        ];
    }

    /**
     * Get comparison data between time periods
     */
    public function getGrowthComparison(User $author, $period = 'month'): array
    {
        $now = now();

        if ($period === 'month') {
            $currentStart = $now->clone()->startOfMonth();
            $currentEnd = $now->clone()->endOfMonth();
            $previousStart = $now->clone()->subMonth()->startOfMonth();
            $previousEnd = $now->clone()->subMonth()->endOfMonth();
        } else { // week
            $currentStart = $now->clone()->startOfWeek();
            $currentEnd = $now->clone()->endOfWeek();
            $previousStart = $now->clone()->subWeek()->startOfWeek();
            $previousEnd = $now->clone()->subWeek()->endOfWeek();
        }

        $currentStats = $this->getStatsForPeriod($author, $currentStart, $currentEnd);
        $previousStats = $this->getStatsForPeriod($author, $previousStart, $previousEnd);

        return [
            'current' => $currentStats,
            'previous' => $previousStats,
            'view_growth' => $this->calculatePercentChange($previousStats['views'], $currentStats['views']),
            'engagement_growth' => $this->calculatePercentChange($previousStats['engagement'], $currentStats['engagement']),
            'follower_growth' => $this->calculatePercentChange($previousStats['followers'], $currentStats['followers']),
        ];
    }

    /**
     * Refresh cached author stats
     */
    public function refreshAuthorStats(User $author): AuthorStat
    {
        $posts = Post::where('author_id', $author->id)->published()->get();

        $totalViews = (int) $posts->sum('views_count');
        $totalLikes = (int) $posts->sum('likes_count');
        $totalComments = (int) $posts->reduce(fn ($carry, $post) => $carry + $post->comments()->count(), 0);

        $totalEngagement = $totalLikes + $totalComments;
        $engagementRate = $totalViews > 0 ? round(($totalEngagement / $totalViews) * 100, 2) : 0;

        $avgPostViews = count($posts) > 0 ? round($totalViews / count($posts)) : 0;

        $topTopics = $posts->pluck('tags')
            ->flatten()
            ->pluck('name')
            ->countBy()
            ->sort()
            ->reverse()
            ->take(5)
            ->toArray();

        $authorStats = AuthorStat::updateOrCreate(
            ['author_id' => $author->id],
            [
                'total_posts' => count($posts),
                'total_views' => $totalViews,
                'total_likes' => $totalLikes,
                'total_comments' => $totalComments,
                'total_followers' => $author->followers()->count(),
                'engagement_rate' => $engagementRate,
                'avg_post_views' => $avgPostViews,
                'last_post_date' => $posts->max('published_at'),
                'top_topics' => $topTopics,
            ]
        );

        return $authorStats;
    }

    // ============ HELPER METHODS ============

    private function calculateEngagementRate(Post $post): float
    {
        $totalEngagement = ($post->likes_count ?? 0) + $post->comments()->count();
        $views = $post->views_count ?? 1;
        return round(($totalEngagement / $views) * 100, 2);
    }

    private function getAverageReadTime(Post $post): int
    {
        $analytics = PostAnalytic::where('post_id', $post->id)->avg('avg_read_time');
        return (int) ($analytics ?? 0);
    }

    private function formatDailyData(Collection $analytics): array
    {
        return $analytics->map(function ($analytic) {
            return [
                'date' => $analytic->date->format('M d'),
                'views' => $analytic->views,
                'likes' => $analytic->likes,
                'comments' => $analytic->comments,
                'engagement' => $analytic->likes + $analytic->comments,
            ];
        })->toArray();
    }

    private function getTopReferrers(Post $post, $limit = 5): array
    {
        $latest = PostAnalytic::where('post_id', $post->id)
            ->latest('date')
            ->first();

        return $latest && $latest->top_referrers
            ? array_slice($latest->top_referrers, 0, $limit)
            : [];
    }

    private function getGeoBreakdown(Post $post): array
    {
        $latest = PostAnalytic::where('post_id', $post->id)
            ->latest('date')
            ->first();

        return $latest && $latest->geo_data
            ? collect($latest->geo_data)
                ->sortDesc()
                ->take(5)
                ->toArray()
            : [];
    }

    private function getDeviceBreakdown(Post $post): array
    {
        $latest = PostAnalytic::where('post_id', $post->id)
            ->latest('date')
            ->first();

        return $latest && $latest->device_breakdown
            ? $latest->device_breakdown
            : [];
    }

    private function getTrafficSources(Post $post): array
    {
        $latest = PostAnalytic::where('post_id', $post->id)
            ->latest('date')
            ->first();

        return $latest && $latest->traffic_sources
            ? $latest->traffic_sources
            : [];
    }

    private function getTrendingRank(Post $post): ?int
    {
        // Simplified - would integrate with TrendingService in production
        return null;
    }

    private function calculateGrowthRate(Post $post, $days = 30): float
    {
        $analytics = PostAnalytic::where('post_id', $post->id)
            ->where('date', '>=', now()->subDays($days))
            ->orderBy('date')
            ->get();

        if ($analytics->count() < 2) {
            return 0;
        }

        $firstViews = $analytics->first()->views;
        $lastViews = $analytics->last()->views;

        return $firstViews > 0 ? round((($lastViews - $firstViews) / $firstViews) * 100, 2) : 0;
    }

    private function getStatsForPeriod(User $author, Carbon $start, Carbon $end): array
    {
        $posts = Post::where('author_id', $author->id)->published()->get();

        $analytics = PostAnalytic::whereIn('post_id', $posts->pluck('id'))
            ->whereBetween('date', [$start, $end])
            ->get();

        return [
            'views' => (int) $analytics->sum('views'),
            'likes' => (int) $analytics->sum('likes'),
            'comments' => (int) $analytics->sum('comments'),
            'engagement' => (int) ($analytics->sum('likes') + $analytics->sum('comments')),
            'followers' => $author->followers()->count(),
        ];
    }

    private function calculatePercentChange($old, $new): float
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }
        return round((($new - $old) / $old) * 100, 1);
    }

    private function getActiveReadersLastDays(User $author, $days): int
    {
        $posts = Post::where('author_id', $author->id)->published()->get();

        return PostAnalytic::whereIn('post_id', $posts->pluck('id'))
            ->where('date', '>=', now()->subDays($days))
            ->sum('unique_readers');
    }

    private function getAverageSessionDuration(User $author): int
    {
        $posts = Post::where('author_id', $author->id)->published()->get();

        $avg = PostAnalytic::whereIn('post_id', $posts->pluck('id'))
            ->avg('avg_read_time');

        return (int) ($avg ?? 0);
    }

    private function getRetentionRate(User $author): float
    {
        $posts = Post::where('author_id', $author->id)->published()->get();

        // Simplified retention calculation
        $uniqueReaders = PostAnalytic::whereIn('post_id', $posts->pluck('id'))
            ->selectRaw('COUNT(DISTINCT reader_id) as count')
            ->value('count');

        $totalReaders = $author->followers()->count();

        return $totalReaders > 0 ? round(($uniqueReaders / $totalReaders) * 100, 2) : 0;
    }
}
