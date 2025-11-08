<?php

namespace App\Services\Analytics;

use App\Models\Post;
use App\Models\User;
use App\Models\SocialMediaPost;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get overall platform statistics
     */
    public static function getPlatformStats(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Total posts
        $totalPosts = Post::count();
        $thisMonthPosts = Post::where('created_at', '>=', $thisMonth)->count();
        $lastMonthPosts = Post::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)->count();
        $postsChange = $lastMonthPosts ? (($thisMonthPosts - $lastMonthPosts) / $lastMonthPosts) * 100 : 0;

        // Active users
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))->count();
        $totalUsers = User::count();

        // Social media reach
        $totalImpressions = SocialMediaPost::sum('metrics->impressions') ?? 0;
        $totalEngagement = SocialMediaPost::sum('metrics->engagement') ?? 0;

        return [
            'total_posts' => $totalPosts,
            'this_month_posts' => $thisMonthPosts,
            'posts_change_percent' => round($postsChange, 2),
            'active_users' => $activeUsers,
            'total_users' => $totalUsers,
            'total_impressions' => $totalImpressions,
            'total_engagement' => $totalEngagement,
            'engagement_rate' => $totalImpressions > 0
                ? round(($totalEngagement / $totalImpressions) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get content performance metrics
     */
    public static function getContentMetrics(): array
    {
        return [
            'most_viewed_posts' => Post::where('status', 'published')
                ->orderByDesc('views')
                ->limit(5)
                ->get(['id', 'title', 'views', 'created_at'])
                ->toArray(),

            'most_engaged_posts' => Post::where('status', 'published')
                ->withCount('comments')
                ->orderByDesc('comments_count')
                ->limit(5)
                ->get(['id', 'title', 'comments_count', 'created_at'])
                ->toArray(),

            'top_categories' => \App\Models\Category::withCount(['posts' => function ($q) {
                $q->where('status', 'published');
            }])
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get(['id', 'name', 'posts_count'])
            ->toArray(),

            'content_by_type' => Post::selectRaw('is_premium, COUNT(*) as count')
                ->where('status', 'published')
                ->groupBy('is_premium')
                ->get()
                ->mapWithKeys(fn ($item) => [
                    ($item->is_premium ? 'premium' : 'free') => $item->count
                ])
                ->toArray(),
        ];
    }

    /**
     * Get blogger performance metrics
     */
    public static function getBloggerMetrics(): array
    {
        return [
            'top_bloggers' => User::whereHas('roles', fn ($q) => $q->where('name', 'blogger'))
                ->withCount('followers')
                ->orderByDesc('followers_count')
                ->limit(10)
                ->get(['id', 'name', 'username', 'followers_count', 'avatar'])
                ->toArray(),

            'most_active_bloggers' => User::whereHas('roles', fn ($q) => $q->where('name', 'blogger'))
                ->withCount('posts')
                ->orderByDesc('posts_count')
                ->limit(5)
                ->get(['id', 'name', 'username', 'posts_count'])
                ->toArray(),

            'blogger_earnings' => \App\Models\BloggerEarning::selectRaw('user_id, SUM(amount) as total_earned')
                ->where('status', 'paid')
                ->groupBy('user_id')
                ->orderByDesc('total_earned')
                ->limit(10)
                ->join('users', 'blogger_earnings.user_id', '=', 'users.id')
                ->select('users.id', 'users.name', 'blogger_earnings.total_earned')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get social media performance
     */
    public static function getSocialMediaMetrics(): array
    {
        $platforms = [];

        foreach (['youtube', 'instagram', 'twitter', 'facebook', 'linkedin'] as $platform) {
            $posts = SocialMediaPost::where('platform', $platform)
                ->where('status', 'published')
                ->get();

            $platforms[$platform] = [
                'total_posts' => $posts->count(),
                'total_impressions' => $posts->sum('metrics.impressions') ?? 0,
                'total_engagement' => $posts->sum('metrics.engagement') ?? 0,
                'avg_engagement_rate' => $posts->count() > 0
                    ? round($posts->average('metrics.engagement_rate') ?? 0, 2)
                    : 0,
                'best_post' => $posts->sortByDesc('metrics.engagement')->first(),
            ];
        }

        return $platforms;
    }

    /**
     * Get revenue metrics
     */
    public static function getRevenueMetrics(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $earningsThisMonth = \App\Models\BloggerEarning::where('created_at', '>=', $thisMonth)
            ->sum('amount');
        $earningsLastMonth = \App\Models\BloggerEarning::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)
            ->sum('amount');

        $pendingPayouts = \App\Models\PayoutRequest::where('status', 'pending')
            ->sum('amount');
        $processedPayouts = \App\Models\PayoutRequest::where('status', 'paid')
            ->sum('amount');

        return [
            'earnings_this_month' => $earningsThisMonth,
            'earnings_last_month' => $earningsLastMonth,
            'earnings_growth' => $earningsLastMonth > 0
                ? round((($earningsThisMonth - $earningsLastMonth) / $earningsLastMonth) * 100, 2)
                : 0,
            'pending_payouts' => $pendingPayouts,
            'processed_payouts' => $processedPayouts,
            'total_revenue' => $earningsThisMonth + $earningsLastMonth,
            'earnings_by_type' => \App\Models\BloggerEarning::selectRaw('type, SUM(amount) as total')
                ->where('created_at', '>=', $thisMonth)
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray(),
        ];
    }

    /**
     * Get time-series data for charts
     */
    public static function getTimeSeriesData(string $period = 'month'): array
    {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 30,
        };

        $data = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            $postsCount = Post::whereDate('created_at', $dateStr)->count();
            $earnings = \App\Models\BloggerEarning::whereDate('created_at', $dateStr)->sum('amount');
            $impressions = SocialMediaPost::whereDate('created_at', $dateStr)->sum('metrics->impressions') ?? 0;

            $data[] = [
                'date' => $date->format('M d'),
                'posts' => $postsCount,
                'earnings' => $earnings ?? 0,
                'impressions' => $impressions,
            ];
        }

        return $data;
    }

    /**
     * Get user growth data
     */
    public static function getUserGrowthData(string $period = 'month'): array
    {
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 30,
        };

        $data = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $totalUsers = User::where('created_at', '<=', $date)->count();
            $bloggers = User::where('created_at', '<=', $date)
                ->whereHas('roles', fn ($q) => $q->where('name', 'blogger'))
                ->count();

            $data[] = [
                'date' => $date->format('M d'),
                'total_users' => $totalUsers,
                'bloggers' => $bloggers,
                'readers' => $totalUsers - $bloggers,
            ];
        }

        return $data;
    }

    /**
     * Get engagement metrics by hour
     */
    public static function getEngagementByHour(): array
    {
        $data = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $start = Carbon::now()->startOfDay()->addHours($hour);
            $end = $start->copy()->addHour();

            $impressions = SocialMediaPost::whereBetween('created_at', [$start, $end])
                ->sum('metrics->impressions') ?? 0;
            $engagement = SocialMediaPost::whereBetween('created_at', [$start, $end])
                ->sum('metrics->engagement') ?? 0;

            $data[] = [
                'hour' => sprintf('%02d:00', $hour),
                'impressions' => $impressions,
                'engagement' => $engagement,
            ];
        }

        return $data;
    }

    /**
     * Get demographic data
     */
    public static function getDemographicData(): array
    {
        $users = User::all();
        $bloggers = User::whereHas('roles', fn ($q) => $q->where('name', 'blogger'))->count();

        return [
            'total_users' => $users->count(),
            'total_bloggers' => $bloggers,
            'total_readers' => $users->count() - $bloggers,
            'avg_followers_per_blogger' => $bloggers > 0
                ? round(User::whereHas('roles', fn ($q) => $q->where('name', 'blogger'))
                    ->withCount('followers')
                    ->get()
                    ->average('followers_count'), 0)
                : 0,
            'verification_rate' => round(($users->filter(fn ($u) => $u->email_verified_at)->count() / $users->count()) * 100, 2),
        ];
    }
}
