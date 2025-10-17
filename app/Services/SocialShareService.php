<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SocialShare;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SocialShareService
{
    /**
     * Track a social share
     */
    public function trackShare(
        int $postId,
        string $platform,
        ?int $userId = null,
        ?string $referrer = null,
        ?array $metadata = null
    ): SocialShare {
        // Create share record
        $share = SocialShare::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'platform' => $platform,
            'utm_source' => 'social',
            'utm_medium' => $platform,
            'utm_campaign' => 'article_share',
            'referrer' => $referrer,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
            'shared_at' => now(),
        ]);

        // Increment share counters on post
        $this->incrementShareCount($postId, $platform);

        // Clear share count cache
        $this->clearShareCountCache($postId);

        // Log the share
        Log::info('Social share tracked', [
            'share_id' => $share->id,
            'post_id' => $postId,
            'platform' => $platform,
            'user_id' => $userId,
        ]);

        return $share;
    }

    /**
     * Increment share count on post
     */
    protected function incrementShareCount(int $postId, string $platform): void
    {
        $post = Post::find($postId);

        if (!$post) {
            return;
        }

        // Increment total shares
        $post->increment('total_shares');

        // Increment platform-specific shares
        $platformColumn = $platform . '_shares';
        if (in_array($platformColumn, ['twitter_shares', 'linkedin_shares', 'facebook_shares', 'whatsapp_shares', 'telegram_shares'])) {
            $post->increment($platformColumn);
        }
    }

    /**
     * Generate UTM-tagged URL for sharing
     */
    public function generateUtmUrl(string $url, string $platform, int $postId): string
    {
        $params = [
            'utm_source' => 'social',
            'utm_medium' => $platform,
            'utm_campaign' => 'article_share',
            'utm_content' => 'post_' . $postId,
        ];

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($params);
    }

    /**
     * Get share count for a post (with caching)
     */
    public function getShareCount(int $postId, bool $useCache = true): int
    {
        if (!$useCache) {
            return SocialShare::where('post_id', $postId)->count();
        }

        return Cache::remember("share_count_{$postId}", 300, function () use ($postId) {
            return SocialShare::where('post_id', $postId)->count();
        });
    }

    /**
     * Get platform breakdown for a post (with caching)
     */
    public function getPlatformBreakdown(int $postId, bool $useCache = true): array
    {
        if (!$useCache) {
            return SocialShare::getPlatformBreakdown($postId);
        }

        return Cache::remember("share_breakdown_{$postId}", 900, function () use ($postId) {
            return SocialShare::getPlatformBreakdown($postId);
        });
    }

    /**
     * Get top shared posts (with caching)
     */
    public function getTopSharedPosts(int $limit = 10, int $days = 30, bool $useCache = true)
    {
        if (!$useCache) {
            return SocialShare::getTopSharedPosts($limit, $days);
        }

        return Cache::remember("top_shared_posts_{$limit}_{$days}", 3600, function () use ($limit, $days) {
            return SocialShare::getTopSharedPosts($limit, $days);
        });
    }

    /**
     * Get share velocity (shares per hour)
     */
    public function getShareVelocity(int $postId, int $hours = 24): float
    {
        return SocialShare::getShareVelocity($postId, $hours);
    }

    /**
     * Detect viral content (high share velocity)
     */
    public function detectViralContent(int $threshold = 50, int $hours = 24)
    {
        return SocialShare::viral($threshold, $hours)->get();
    }

    /**
     * Get share analytics for a post
     */
    public function getShareAnalytics(int $postId): array
    {
        $post = Post::find($postId);

        if (!$post) {
            return [];
        }

        $platformBreakdown = $this->getPlatformBreakdown($postId);
        $shareVelocity = $this->getShareVelocity($postId);

        return [
            'total_shares' => $post->total_shares,
            'platform_breakdown' => $platformBreakdown,
            'share_velocity' => round($shareVelocity, 2),
            'is_viral' => $shareVelocity > 2, // More than 2 shares per hour
            'twitter_shares' => $post->twitter_shares,
            'linkedin_shares' => $post->linkedin_shares,
            'facebook_shares' => $post->facebook_shares,
            'whatsapp_shares' => $post->whatsapp_shares,
            'telegram_shares' => $post->telegram_shares,
        ];
    }

    /**
     * Get share trends (daily shares over time)
     */
    public function getShareTrends(int $postId, int $days = 30): array
    {
        $shares = SocialShare::where('post_id', $postId)
            ->where('shared_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(shared_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return $shares;
    }

    /**
     * Get share leaderboard (top sharers)
     */
    public function getShareLeaderboard(int $limit = 10, int $days = 30)
    {
        return SocialShare::selectRaw('user_id, COUNT(*) as share_count')
            ->whereNotNull('user_id')
            ->where('shared_at', '>=', now()->subDays($days))
            ->groupBy('user_id')
            ->orderBy('share_count', 'desc')
            ->limit($limit)
            ->with('user')
            ->get();
    }

    /**
     * Clear share count cache
     */
    protected function clearShareCountCache(int $postId): void
    {
        Cache::forget("share_count_{$postId}");
        Cache::forget("share_breakdown_{$postId}");

        // Clear top shared posts cache (all variations)
        for ($days = 7; $days <= 90; $days += 7) {
            for ($limit = 5; $limit <= 20; $limit += 5) {
                Cache::forget("top_shared_posts_{$limit}_{$days}");
            }
        }
    }

    /**
     * Get share rate (shares per view)
     */
    public function getShareRate(int $postId): float
    {
        $post = Post::find($postId);

        if (!$post || $post->views === 0) {
            return 0.0;
        }

        return ($post->total_shares / $post->views) * 100;
    }

    /**
     * Get platform preferences for a user
     */
    public function getUserPlatformPreferences(int $userId): array
    {
        return SocialShare::where('user_id', $userId)
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->pluck('count', 'platform')
            ->toArray();
    }
}
