<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\ContentView;
use Illuminate\Support\Facades\Log;

class ContentMeteringService
{
    const FREE_ARTICLE_LIMIT = 3;

    /**
     * Check if user can view a free premium article
     */
    public function canViewFreeArticle(?User $user, Post $post): bool
    {
        // Not premium content - always allowed
        if (!$post->is_premium) {
            return true;
        }

        // Authenticated with active subscription or trial - always allowed
        if ($user && ($user->subscribed() || $user->onTrial())) {
            return true;
        }

        // Check free article quota
        $used = $this->getFreeArticlesUsed($user);

        return $used < self::FREE_ARTICLE_LIMIT;
    }

    /**
     * Get number of free articles used this month
     */
    public function getFreeArticlesUsed(?User $user): int
    {
        if (!$user) {
            // Anonymous user - check session
            return session('free_articles_used', 0);
        }

        // Check if quota needs reset (monthly)
        if ($user->free_articles_reset_at && now()->isAfter($user->free_articles_reset_at)) {
            $user->update([
                'free_articles_used' => 0,
                'free_articles_reset_at' => now()->addMonth(),
            ]);

            Log::info('Free article quota reset', ['user_id' => $user->id]);

            return 0;
        }

        return $user->free_articles_used ?? 0;
    }

    /**
     * Increment free article count
     */
    public function incrementFreeArticleCount(?User $user, Post $post): void
    {
        if (!$user) {
            // Anonymous user - use session
            session()->increment('free_articles_used');

            Log::info('Anonymous free article count incremented', [
                'session_id' => session()->getId(),
                'post_id' => $post->id,
                'count' => session('free_articles_used'),
            ]);

            return;
        }

        $user->increment('free_articles_used');

        if (!$user->free_articles_reset_at) {
            $user->update(['free_articles_reset_at' => now()->addMonth()]);
        }

        Log::info('User free article count incremented', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'count' => $user->free_articles_used,
        ]);
    }

    /**
     * Get remaining free articles for this month
     */
    public function getRemainingFreeArticles(?User $user): int
    {
        $used = $this->getFreeArticlesUsed($user);
        return max(0, self::FREE_ARTICLE_LIMIT - $used);
    }

    /**
     * Track content view
     */
    public function trackContentView(Post $post, ?User $user = null): ContentView
    {
        return ContentView::create([
            'user_id' => $user?->id,
            'post_id' => $post->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'is_premium_content' => $post->is_premium,
            'viewed_as_trial' => $user?->onTrial() ?? false,
            'referrer' => request()->header('referer'),
            'viewed_at' => now(),
        ]);
    }

    /**
     * Check if user should see paywall
     */
    public function shouldShowPaywall(Post $post, ?User $user): bool
    {
        // Not premium - no paywall
        if (!$post->is_premium) {
            return false;
        }

        // User has active subscription or trial - no paywall
        if ($user && ($user->subscribed() || $user->onTrial())) {
            return false;
        }

        // Check if user has free articles remaining
        return !$this->canViewFreeArticle($user, $post);
    }

    /**
     * Get paywall type for a post
     */
    public function getPaywallType(Post $post, ?User $user): string
    {
        $remaining = $this->getRemainingFreeArticles($user);

        if ($remaining === 0) {
            return 'hard'; // No preview, immediate upgrade required
        }

        if ($remaining === 1) {
            return 'soft'; // Show preview with urgent CTA
        }

        return 'preview'; // Show partial content
    }

    /**
     * Reset free article count for a user
     */
    public function resetFreeArticleCount(?User $user): void
    {
        if (!$user) {
            session()->forget('free_articles_used');
            return;
        }

        $user->update([
            'free_articles_used' => 0,
            'free_articles_reset_at' => now()->addMonth(),
        ]);

        Log::info('Free article count manually reset', ['user_id' => $user->id]);
    }

    /**
     * Get free article usage statistics
     */
    public function getUsageStats(?User $user): array
    {
        $used = $this->getFreeArticlesUsed($user);
        $remaining = $this->getRemainingFreeArticles($user);
        $resetDate = $user?->free_articles_reset_at ?? now()->addMonth();

        return [
            'limit' => self::FREE_ARTICLE_LIMIT,
            'used' => $used,
            'remaining' => $remaining,
            'reset_date' => $resetDate,
            'percentage_used' => ($used / self::FREE_ARTICLE_LIMIT) * 100,
        ];
    }
}
