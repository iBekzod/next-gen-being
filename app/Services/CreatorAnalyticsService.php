<?php

namespace App\Services;

use App\Models\User;
use App\Models\CreatorAnalytic;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CreatorAnalyticsService
{
    /**
     * Generate daily analytics snapshot for a creator
     */
    public function generateDailyAnalytics(User $user, ?Carbon $date = null): CreatorAnalytic
    {
        $date = $date ?? now()->subDay()->toDateString();

        // Get today's data
        $posts = Post::where('user_id', $user->id)
            ->where('published_at', '>=', $date . ' 00:00:00')
            ->where('published_at', '<=', $date . ' 23:59:59')
            ->get();

        // Calculate metrics
        $postsPublished = $posts->count();
        $postsViews = (int) $posts->sum('views_count');
        $postsLikes = (int) $posts->sum('likes_count');
        $postsComments = (int) $posts->flatMap(fn($p) => $p->comments())->count();
        $postsShares = (int) $posts->sum(function($p) {
            return \App\Models\SocialShare::where('post_id', $p->id)
                ->where('created_at', '>=', $date)
                ->count();
        });

        // Followers
        $followersGained = $user->followers()
            ->where('created_at', '>=', $date . ' 00:00:00')
            ->where('created_at', '<=', $date . ' 23:59:59')
            ->count();

        // Calculate followers lost by comparing with previous day
        $followersLost = $this->calculateFollowersLost($user, $date);

        // Tips
        $tipsData = \App\Models\Tip::where('to_user_id', $user->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', $date . ' 00:00:00')
            ->where('created_at', '<=', $date . ' 23:59:59')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        // Calculate engagement metrics
        $totalEngagement = $postsLikes + $postsComments + $postsShares;
        $engagementRate = $postsViews > 0 ? ($totalEngagement / $postsViews) : 0;

        // Subscription revenue (from subscriptions created today)
        $subscriptionRevenue = \App\Models\BloggerEarning::where('user_id', $user->id)
            ->where('type', 'premium_content')
            ->where('status', 'pending')
            ->where('created_at', '>=', $date . ' 00:00:00')
            ->where('created_at', '<=', $date . ' 23:59:59')
            ->sum('amount');

        // Create or update analytics record
        return CreatorAnalytic::updateOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            [
                'posts_published' => $postsPublished,
                'posts_views' => $postsViews,
                'posts_likes' => $postsLikes,
                'posts_comments' => $postsComments,
                'posts_shares' => $postsShares,
                'followers_gained' => $followersGained,
                'followers_lost' => $followersLost,
                'tips_received' => (int) ($tipsData->count ?? 0),
                'tips_amount' => (float) ($tipsData->total ?? 0),
                'subscription_revenue' => (float) $subscriptionRevenue,
                'total_engagement' => $totalEngagement,
                'engagement_rate' => round($engagementRate, 4),
                'average_read_time' => $this->calculateAverageReadTime($posts),
                'bounce_rate' => $this->calculateBounceRate($posts),
            ]
        );
    }

    /**
     * Get creator's analytics dashboard data
     */
    public function getDashboardData(User $user, $days = 30): array
    {
        $analytics = CreatorAnalytic::byUser($user)
            ->recent($days)
            ->orderBy('date', 'desc')
            ->get();

        return [
            'date_range' => [
                'start' => now()->subDays($days)->toDateString(),
                'end' => now()->toDateString(),
                'days' => $days,
            ],
            'summary' => $this->calculateSummary($analytics),
            'daily' => $analytics->map(fn($a) => [
                'date' => $a->date->format('Y-m-d'),
                'posts_published' => $a->posts_published,
                'views' => $a->posts_views,
                'engagement' => $a->total_engagement,
                'revenue' => $a->getTotalRevenueAttribute(),
                'followers_gained' => $a->followers_gained,
                'tips' => $a->tips_amount,
            ]),
            'trends' => $this->calculateTrends($analytics),
            'top_posts' => $this->getTopPosts($user, $days),
            'revenue_breakdown' => $this->getRevenueBreakdown($user, $days),
        ];
    }

    /**
     * Get summary statistics
     */
    public function calculateSummary($analytics): array
    {
        $totalViews = (int) $analytics->sum('posts_views');
        $totalLikes = (int) $analytics->sum('posts_likes');
        $totalComments = (int) $analytics->sum('posts_comments');
        $totalShares = (int) $analytics->sum('posts_shares');
        $totalFollowersGained = (int) $analytics->sum('followers_gained');
        $totalRevenue = (float) $analytics->sum(function($a) {
            return $a->getTotalRevenueAttribute();
        });
        $totalTips = (float) $analytics->sum('tips_amount');
        $totalPostsPublished = (int) $analytics->sum('posts_published');

        $avgEngagementRate = $analytics->count() > 0
            ? round($analytics->avg('engagement_rate'), 4)
            : 0;

        return [
            'period_posts_published' => $totalPostsPublished,
            'period_views' => $totalViews,
            'period_engagement' => $totalLikes + $totalComments + $totalShares,
            'period_followers_gained' => $totalFollowersGained,
            'period_revenue' => round($totalRevenue, 2),
            'period_tips' => round($totalTips, 2),
            'avg_views_per_post' => $totalPostsPublished > 0
                ? round($totalViews / $totalPostsPublished, 1)
                : 0,
            'avg_engagement_rate' => $avgEngagementRate,
            'avg_tips_per_post' => $totalPostsPublished > 0
                ? round($totalTips / $totalPostsPublished, 2)
                : 0,
        ];
    }

    /**
     * Calculate trends (growth rates)
     */
    public function calculateTrends($analytics): array
    {
        if ($analytics->count() < 2) {
            return [
                'views_trend' => 0,
                'engagement_trend' => 0,
                'revenue_trend' => 0,
                'followers_trend' => 0,
            ];
        }

        $firstHalf = $analytics->slice(0, (int)($analytics->count() / 2));
        $secondHalf = $analytics->slice((int)($analytics->count() / 2));

        $firstHalfViews = (float) $firstHalf->sum('posts_views');
        $secondHalfViews = (float) $secondHalf->sum('posts_views');
        $viewsTrend = $firstHalfViews > 0
            ? round(($secondHalfViews - $firstHalfViews) / $firstHalfViews * 100, 1)
            : 0;

        $firstHalfEngagement = (float) $firstHalf->sum('total_engagement');
        $secondHalfEngagement = (float) $secondHalf->sum('total_engagement');
        $engagementTrend = $firstHalfEngagement > 0
            ? round(($secondHalfEngagement - $firstHalfEngagement) / $firstHalfEngagement * 100, 1)
            : 0;

        $firstHalfRevenue = (float) $firstHalf->sum(function($a) {
            return $a->getTotalRevenueAttribute();
        });
        $secondHalfRevenue = (float) $secondHalf->sum(function($a) {
            return $a->getTotalRevenueAttribute();
        });
        $revenueTrend = $firstHalfRevenue > 0
            ? round(($secondHalfRevenue - $firstHalfRevenue) / $firstHalfRevenue * 100, 1)
            : 0;

        $firstHalfFollowers = (float) $firstHalf->sum('followers_gained');
        $secondHalfFollowers = (float) $secondHalf->sum('followers_gained');
        $followersTrend = $firstHalfFollowers > 0
            ? round(($secondHalfFollowers - $firstHalfFollowers) / $firstHalfFollowers * 100, 1)
            : 0;

        return [
            'views_trend' => $viewsTrend,
            'engagement_trend' => $engagementTrend,
            'revenue_trend' => $revenueTrend,
            'followers_trend' => $followersTrend,
        ];
    }

    /**
     * Get top performing posts
     */
    public function getTopPosts(User $user, $days = 30): array
    {
        return Post::where('user_id', $user->id)
            ->published()
            ->where('published_at', '>=', now()->subDays($days))
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'views' => $post->views_count,
                'likes' => $post->likes_count ?? 0,
                'comments' => $post->comments()->count(),
                'engagement_rate' => $post->views_count > 0
                    ? round((($post->likes_count ?? 0) + $post->comments()->count()) / $post->views_count, 4)
                    : 0,
                'published_at' => $post->published_at->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Get revenue breakdown
     */
    public function getRevenueBreakdown(User $user, $days = 30): array
    {
        $tips = (float) \App\Models\Tip::where('to_user_id', $user->id)
            ->completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('amount');

        $premiumRevenue = (float) \App\Models\BloggerEarning::where('user_id', $user->id)
            ->where('type', 'premium_content')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('amount');

        $milestoneRevenue = (float) \App\Models\BloggerEarning::where('user_id', $user->id)
            ->where('type', 'follower_milestone')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('amount');

        $engagementBonus = (float) \App\Models\BloggerEarning::where('user_id', $user->id)
            ->where('type', 'engagement_bonus')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('amount');

        $total = $tips + $premiumRevenue + $milestoneRevenue + $engagementBonus;

        return [
            'tips' => [
                'amount' => round($tips, 2),
                'percentage' => $total > 0 ? round($tips / $total * 100, 1) : 0,
            ],
            'premium_subscriptions' => [
                'amount' => round($premiumRevenue, 2),
                'percentage' => $total > 0 ? round($premiumRevenue / $total * 100, 1) : 0,
            ],
            'milestone_rewards' => [
                'amount' => round($milestoneRevenue, 2),
                'percentage' => $total > 0 ? round($milestoneRevenue / $total * 100, 1) : 0,
            ],
            'engagement_bonuses' => [
                'amount' => round($engagementBonus, 2),
                'percentage' => $total > 0 ? round($engagementBonus / $total * 100, 1) : 0,
            ],
            'total' => round($total, 2),
        ];
    }

    // Private helpers

    private function calculateAverageReadTime($posts): int
    {
        if ($posts->isEmpty()) {
            return 0;
        }

        return (int) round($posts->avg('read_time_minutes'));
    }

    private function calculateBounceRate($posts): float
    {
        if ($posts->isEmpty()) {
            return 0;
        }

        // Bounce rate = views without engagement / total views
        $totalViews = (int) $posts->sum('views_count');
        if ($totalViews === 0) {
            return 0;
        }

        $viewsWithoutEngagement = 0;
        foreach ($posts as $post) {
            $engagements = ($post->likes_count ?? 0) + $post->comments()->count();
            if ($engagements === 0) {
                $viewsWithoutEngagement += $post->views_count;
            }
        }

        return round($viewsWithoutEngagement / $totalViews, 4);
    }

    /**
     * Calculate followers lost by comparing with previous day
     */
    private function calculateFollowersLost(User $user, string $date): int
    {
        // Get previous day's analytics
        $previousDate = \Carbon\Carbon::parse($date)->subDay()->toDateString();
        $previousAnalytics = CreatorAnalytic::where('user_id', $user->id)
            ->where('date', $previousDate)
            ->first();

        if (!$previousAnalytics) {
            // If no previous data, return 0 (can't calculate)
            return 0;
        }

        // Get current follower count
        $currentFollowers = $user->followers()->count();

        // Calculate: previous followers = previous followers_gained - previous followers_lost
        // But we stored the absolute follower count, so let's use a different approach
        // We can check for unfollow records in the user_follows table (soft deleted or timestamped)

        // Alternative: Calculate based on follower history
        // For now, we'll use a simple calculation:
        // followers_lost = followers_gained_yesterday - (current_followers - followers_from_previous_day)
        // But without tracking absolute values, we estimate based on growth patterns

        // This is a best-effort calculation. In production, you'd want to:
        // 1. Store absolute follower counts in analytics
        // 2. Use timestamps on user_follows to detect when follows are removed

        // For now, return 0 as a placeholder (no reliable way to calculate without additional tracking)
        return 0;
    }
}
