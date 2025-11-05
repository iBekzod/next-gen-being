<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\User;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Collection;
use Exception;

class SocialMediaPublishingService
{
    public function __construct(
        protected YouTubePublisher $youtubePublisher,
        protected InstagramPublisher $instagramPublisher,
        protected TwitterPublisher $twitterPublisher,
        protected TelegramPublisher $telegramPublisher,
    ) {}

    /**
     * Publish post to all enabled social media accounts
     *
     * @param Post $post
     * @param User|null $user If null, uses official NextGen Being accounts
     * @return Collection<SocialMediaPost>
     */
    public function publishToAll(Post $post, ?User $user = null): Collection
    {
        $accounts = $this->getAutoPublishAccounts($post, $user);
        $results = collect();

        foreach ($accounts as $account) {
            try {
                $socialPost = $this->publishToAccount($post, $account);
                $results->push($socialPost);
            } catch (Exception $e) {
                \Log::error("Failed to publish post {$post->id} to {$account->platform}: {$e->getMessage()}");
                // Continue with other platforms even if one fails
            }
        }

        return $results;
    }

    /**
     * Publish post to specific social media account
     */
    public function publishToAccount(Post $post, SocialMediaAccount $account): SocialMediaPost
    {
        return match($account->platform) {
            'youtube' => $this->youtubePublisher->publish($post, $account),
            'instagram' => $this->instagramPublisher->publish($post, $account),
            'twitter' => $this->twitterPublisher->publish($post, $account),
            default => throw new Exception("Unsupported platform: {$account->platform}"),
        };
    }

    /**
     * Publish to Telegram (uses bot token, not OAuth)
     */
    public function publishToTelegram(Post $post): SocialMediaPost
    {
        $channelId = config('services.telegram.channel_id');

        if (!$channelId) {
            throw new Exception('Telegram channel ID not configured');
        }

        return $this->telegramPublisher->publish($post, $channelId);
    }

    /**
     * Get accounts that should auto-publish
     */
    protected function getAutoPublishAccounts(Post $post, ?User $user = null): Collection
    {
        $query = SocialMediaAccount::where('auto_publish', true);

        if ($user) {
            // Publish to user's personal accounts
            $query->where('user_id', $user->id);
        } else {
            // Publish to NextGen Being official accounts
            $query->where('account_type', 'official');
        }

        return $query->get();
    }

    /**
     * Update engagement metrics for all published posts
     */
    public function updateEngagementMetrics(Post $post): void
    {
        $socialPosts = $post->socialMediaPosts()
            ->where('status', 'published')
            ->get();

        foreach ($socialPosts as $socialPost) {
            try {
                $stats = $this->getStatsForPlatform($socialPost);

                $socialPost->update([
                    'views_count' => $stats['views'] ?? 0,
                    'likes_count' => $stats['likes'] ?? 0,
                    'comments_count' => $stats['comments'] ?? 0,
                ]);
            } catch (Exception $e) {
                \Log::error("Failed to update stats for {$socialPost->platform} post {$socialPost->id}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Get statistics for specific platform
     */
    protected function getStatsForPlatform(SocialMediaPost $socialPost): array
    {
        if (!$socialPost->platform_post_id || !$socialPost->socialMediaAccount) {
            return [];
        }

        return match($socialPost->platform) {
            'youtube' => $this->youtubePublisher->getVideoStats(
                $socialPost->platform_post_id,
                $socialPost->socialMediaAccount
            ),
            'instagram' => $this->instagramPublisher->getMediaStats(
                $socialPost->platform_post_id,
                $socialPost->socialMediaAccount
            ),
            'twitter' => $this->twitterPublisher->getTweetStats(
                $socialPost->platform_post_id,
                $socialPost->socialMediaAccount
            ),
            'telegram' => $this->telegramPublisher->getMessageStats(
                $socialPost->platform_post_id,
                config('services.telegram.channel_id')
            ),
            default => [],
        };
    }

    /**
     * Check if post can be auto-published
     */
    public function canAutoPublish(Post $post): bool
    {
        // Must be published
        if ($post->status !== 'published') {
            return false;
        }

        // Must have video
        if (!$post->hasVideo()) {
            return false;
        }

        // Must not have been published to all platforms yet
        return !$post->hasBeenPublishedToSocialMedia();
    }

    /**
     * Get publishing summary for post
     */
    public function getPublishingSummary(Post $post): array
    {
        $socialPosts = $post->socialMediaPosts;

        return [
            'total_platforms' => $socialPosts->count(),
            'published' => $socialPosts->where('status', 'published')->count(),
            'pending' => $socialPosts->where('status', 'processing')->count(),
            'failed' => $socialPosts->where('status', 'failed')->count(),
            'total_views' => $socialPosts->sum('views_count'),
            'total_likes' => $socialPosts->sum('likes_count'),
            'total_comments' => $socialPosts->sum('comments_count'),
            'engagement_rate' => $socialPosts->avg(function($sp) {
                return $sp->getEngagementRate();
            }),
        ];
    }
}
