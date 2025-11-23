<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Streak;
use App\Models\Tip;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    /**
     * Get top creators by various metrics
     */
    public function getTopCreators($limit = 50, $days = 30, $metric = 'engagement'): array
    {
        $query = User::query()
            ->where('is_blogger', true)
            ->where('status', 'active');

        switch ($metric) {
            case 'posts':
                return $this->topCreatorsByPostCount($query, $days, $limit);
            case 'views':
                return $this->topCreatorsByViews($query, $days, $limit);
            case 'engagement':
                return $this->topCreatorsByEngagement($query, $days, $limit);
            case 'earnings':
                return $this->topCreatorsByEarnings($query, $days, $limit);
            case 'followers':
                return $this->topCreatorsByFollowers($query, $limit);
            default:
                return $this->topCreatorsByEngagement($query, $days, $limit);
        }
    }

    /**
     * Get top readers by reading streaks or activity
     */
    public function getTopReaders($limit = 50, $metric = 'reading_streak'): array
    {
        switch ($metric) {
            case 'reading_streak':
                return $this->topReadersByReadingStreak($limit);
            case 'posts_read':
                return $this->topReadersByPostsRead($limit);
            case 'engagement':
                return $this->topReadersByEngagement($limit);
            case 'followers':
                return $this->topReadersByFollowers($limit);
            default:
                return $this->topReadersByReadingStreak($limit);
        }
    }

    /**
     * Get top engagers (commenters, tip givers, sharers)
     */
    public function getTopEngagers($limit = 50, $days = 30): array
    {
        $topCommenters = $this->topEngagersByComments($days, $limit);
        $topTippers = $this->topEngagersByTips($days, $limit);
        $topSharers = $this->topEngagersByShares($days, $limit);

        // Combine and rank by composite score
        $engagers = [];
        foreach ($topCommenters as $user) {
            $engagers[$user['user']->id] = [
                'user' => $user['user'],
                'comments' => $user['count'],
                'tips' => 0,
                'shares' => 0,
                'score' => $user['count'] * 1, // 1 point per comment
            ];
        }

        foreach ($topTippers as $user) {
            $id = $user['user']->id;
            if (!isset($engagers[$id])) {
                $engagers[$id] = [
                    'user' => $user['user'],
                    'comments' => 0,
                    'tips' => (float) $user['total'],
                    'shares' => 0,
                    'score' => $user['total'] * 5, // 5 points per dollar tipped
                ];
            } else {
                $engagers[$id]['tips'] = (float) $user['total'];
                $engagers[$id]['score'] += $user['total'] * 5;
            }
        }

        // Sort by score and return top N
        usort($engagers, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($engagers, 0, $limit);
    }

    /**
     * Get trending posts (last 7 days)
     */
    public function getTrendingPosts($limit = 20, $days = 7): array
    {
        return Post::published()
            ->selectRaw('
                posts.*,
                (posts.views_count * 0.5 +
                 (SELECT COALESCE(COUNT(*), 0) FROM post_interactions WHERE post_id = posts.id AND type = "like") * 5 +
                 (SELECT COALESCE(COUNT(*), 0) FROM post_interactions WHERE post_id = posts.id AND type = "comment") * 3) as engagement_score
            ')
            ->where('published_at', '>=', now()->subDays($days))
            ->orderByRaw('engagement_score DESC')
            ->limit($limit)
            ->with('author:id,name,username,profile_image_url')
            ->get()
            ->map(fn($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'author' => $post->author,
                'views' => $post->views_count,
                'engagement_score' => round($post->engagement_score, 2),
            ]);
    }

    /**
     * Get user's rank in various leaderboards
     */
    public function getUserRank(User $user, $metric = 'engagement', $days = 30): array
    {
        $leaderboard = $this->getTopCreators(1000, $days, $metric); // Get large leaderboard

        $rank = null;
        foreach ($leaderboard as $index => $entry) {
            if ($entry['user']->id === $user->id) {
                $rank = $index + 1;
                break;
            }
        }

        return [
            'user_id' => $user->id,
            'metric' => $metric,
            'rank' => $rank,
            'total_on_leaderboard' => count($leaderboard),
            'in_top_10' => $rank && $rank <= 10,
            'in_top_50' => $rank && $rank <= 50,
            'in_top_100' => $rank && $rank <= 100,
        ];
    }

    // Private helper methods

    private function topCreatorsByPostCount($query, $days, $limit): array
    {
        return $query->withCount(['posts' => function ($q) use ($days) {
                $q->published()->where('published_at', '>=', now()->subDays($days));
            }])
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'posts_count' => $user->posts_count,
                'metric_value' => $user->posts_count,
            ]);
    }

    private function topCreatorsByViews($query, $days, $limit): array
    {
        return $query->selectRaw('
                users.*,
                COALESCE(SUM(posts.views_count), 0) as total_views
            ')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->where('posts.published_at', '>=', now()->subDays($days))
            ->where('posts.status', 'published')
            ->groupBy('users.id')
            ->orderByRaw('total_views DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'total_views' => (int) $user->total_views,
                'metric_value' => (int) $user->total_views,
            ]);
    }

    private function topCreatorsByEngagement($query, $days, $limit): array
    {
        return $query->selectRaw('
                users.*,
                COALESCE(SUM(posts.views_count), 0) * 0.5 as engagement_score,
                COALESCE(SUM(posts.views_count), 0) as total_views
            ')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->where('posts.published_at', '>=', now()->subDays($days))
            ->where('posts.status', 'published')
            ->groupBy('users.id')
            ->orderByRaw('engagement_score DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'engagement_score' => round($user->engagement_score, 2),
                'total_views' => (int) $user->total_views,
                'metric_value' => round($user->engagement_score, 2),
            ]);
    }

    private function topCreatorsByEarnings($query, $days, $limit): array
    {
        return $query->selectRaw('
                users.*,
                COALESCE(SUM(blogger_earnings.amount), 0) as total_earnings
            ')
            ->leftJoin('blogger_earnings', 'users.id', '=', 'blogger_earnings.user_id')
            ->where('blogger_earnings.created_at', '>=', now()->subDays($days))
            ->where('blogger_earnings.status', 'pending')
            ->groupBy('users.id')
            ->orderByRaw('total_earnings DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'total_earnings' => (float) $user->total_earnings,
                'metric_value' => (float) $user->total_earnings,
            ]);
    }

    private function topCreatorsByFollowers($query, $limit): array
    {
        return $query->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'followers_count' => $user->followers_count,
                'metric_value' => $user->followers_count,
            ]);
    }

    private function topReadersByReadingStreak($limit): array
    {
        return Streak::reading()
            ->active()
            ->orderBy('current_count', 'desc')
            ->limit($limit)
            ->with('user:id,name,username,profile_image_url')
            ->get()
            ->map(fn($streak) => [
                'user' => $streak->user,
                'current_streak' => $streak->current_count,
                'longest_streak' => $streak->longest_count,
                'metric_value' => $streak->current_count,
            ]);
    }

    private function topReadersByPostsRead($limit): array
    {
        return User::selectRaw('
                users.*,
                COALESCE(COUNT(DISTINCT content_views.post_id), 0) as posts_read
            ')
            ->leftJoin('content_views', 'users.id', '=', 'content_views.user_id')
            ->groupBy('users.id')
            ->orderByRaw('posts_read DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'posts_read' => (int) $user->posts_read,
                'metric_value' => (int) $user->posts_read,
            ]);
    }

    private function topReadersByEngagement($limit): array
    {
        return User::selectRaw('
                users.*,
                COALESCE(COUNT(DISTINCT user_interactions.id), 0) as interactions
            ')
            ->leftJoin('user_interactions', 'users.id', '=', 'user_interactions.user_id')
            ->groupBy('users.id')
            ->orderByRaw('interactions DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'interactions' => (int) $user->interactions,
                'metric_value' => (int) $user->interactions,
            ]);
    }

    private function topReadersByFollowers($limit): array
    {
        return User::withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'followers_count' => $user->followers_count,
                'metric_value' => $user->followers_count,
            ]);
    }

    private function topEngagersByComments($days, $limit): array
    {
        return User::selectRaw('
                users.*,
                COUNT(comments.id) as comment_count
            ')
            ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
            ->where('comments.created_at', '>=', now()->subDays($days))
            ->groupBy('users.id')
            ->orderByRaw('comment_count DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'count' => (int) $user->comment_count,
            ]);
    }

    private function topEngagersByTips($days, $limit): array
    {
        return User::selectRaw('
                users.*,
                COALESCE(SUM(tips.amount), 0) as total_tips
            ')
            ->leftJoin('tips', 'users.id', '=', 'tips.from_user_id')
            ->where('tips.created_at', '>=', now()->subDays($days))
            ->where('tips.status', 'completed')
            ->groupBy('users.id')
            ->orderByRaw('total_tips DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'total' => (float) $user->total_tips,
            ]);
    }

    private function topEngagersByShares($days, $limit): array
    {
        return User::selectRaw('
                users.*,
                COUNT(social_shares.id) as share_count
            ')
            ->leftJoin('social_shares', 'users.id', '=', 'social_shares.user_id')
            ->where('social_shares.created_at', '>=', now()->subDays($days))
            ->groupBy('users.id')
            ->orderByRaw('share_count DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'count' => (int) $user->share_count,
            ]);
    }
}
