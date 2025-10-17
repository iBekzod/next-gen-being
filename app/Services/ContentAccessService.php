<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\PaywallInteraction;
use Illuminate\Support\Facades\Log;

class ContentAccessService
{
    /**
     * Check if user can access post based on tier
     */
    public function canUserAccessPost(Post $post, ?User $user): bool
    {
        // Not published - only author can access
        if (!$post->isPublished()) {
            return $user && $user->id === $post->author_id;
        }

        // Free content - everyone can access
        if (!$post->is_premium) {
            return true;
        }

        // No user - cannot access premium content
        if (!$user) {
            return false;
        }

        // Check tier-based access
        return $this->hasRequiredTier($post, $user);
    }

    /**
     * Check if user has required subscription tier
     */
    public function hasRequiredTier(Post $post, User $user): bool
    {
        // User has active subscription or trial
        if (!$user->subscribed() && !$user->onTrial()) {
            return false;
        }

        // No tier specified - any premium subscription works
        if ($post->premium_tier === null) {
            return true;
        }

        // Get user's current tier
        $userTier = $this->getUserTier($user);

        // Check if user's tier meets requirement
        return $this->tierMeetsRequirement($userTier, $post->premium_tier);
    }

    /**
     * Get user's subscription tier
     */
    public function getUserTier(User $user): ?string
    {
        if (!$user->subscribed() && !$user->onTrial()) {
            return null;
        }

        // Get subscription type from LemonSqueezy
        $subscription = $user->subscription();

        return $subscription?->type; // 'basic', 'pro', or 'team'
    }

    /**
     * Check if user tier meets post requirement
     */
    protected function tierMeetsRequirement(?string $userTier, string $requiredTier): bool
    {
        if (!$userTier) {
            return false;
        }

        $tierHierarchy = [
            'basic' => 1,
            'pro' => 2,
            'team' => 3,
        ];

        $userLevel = $tierHierarchy[$userTier] ?? 0;
        $requiredLevel = $tierHierarchy[$requiredTier] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Track paywall interaction
     */
    public function trackPaywallInteraction(
        Post $post,
        string $interactionType,
        string $paywallType,
        ?User $user = null,
        ?array $metadata = null
    ): PaywallInteraction {
        return PaywallInteraction::create([
            'user_id' => $user?->id,
            'post_id' => $post->id,
            'session_id' => session()->getId(),
            'interaction_type' => $interactionType,
            'paywall_type' => $paywallType,
            'converted' => false,
            'metadata' => $metadata,
            'interacted_at' => now(),
        ]);
    }

    /**
     * Mark paywall interaction as converted
     */
    public function markAsConverted(int $interactionId): void
    {
        PaywallInteraction::where('id', $interactionId)
            ->update(['converted' => true]);

        Log::info('Paywall interaction marked as converted', [
            'interaction_id' => $interactionId,
        ]);
    }

    /**
     * Get conversion rate for a post
     */
    public function getConversionRate(Post $post): float
    {
        $total = PaywallInteraction::where('post_id', $post->id)
            ->where('interaction_type', 'view')
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $converted = PaywallInteraction::where('post_id', $post->id)
            ->where('converted', true)
            ->count();

        return ($converted / $total) * 100;
    }

    /**
     * Get paywall performance metrics
     */
    public function getPaywallMetrics(Post $post): array
    {
        $views = PaywallInteraction::where('post_id', $post->id)
            ->where('interaction_type', 'view')
            ->count();

        $upgrades = PaywallInteraction::where('post_id', $post->id)
            ->where('interaction_type', 'click_upgrade')
            ->count();

        $dismissals = PaywallInteraction::where('post_id', $post->id)
            ->where('interaction_type', 'dismiss')
            ->count();

        $converted = PaywallInteraction::where('post_id', $post->id)
            ->where('converted', true)
            ->count();

        return [
            'paywall_views' => $views,
            'upgrade_clicks' => $upgrades,
            'dismissals' => $dismissals,
            'conversions' => $converted,
            'click_through_rate' => $views > 0 ? ($upgrades / $views) * 100 : 0,
            'conversion_rate' => $views > 0 ? ($converted / $views) * 100 : 0,
            'dismiss_rate' => $views > 0 ? ($dismissals / $views) * 100 : 0,
        ];
    }

    /**
     * Get content preview HTML (for progressive paywall)
     */
    public function getContentPreview(Post $post, int $percentage = 30): string
    {
        $content = $post->content;
        $wordCount = str_word_count(strip_tags($content));
        $previewWords = (int) ($wordCount * ($percentage / 100));

        // Get first N words
        $words = str_word_count($content, 2, '0123456789');
        $previewContent = '';
        $count = 0;

        foreach ($words as $position => $word) {
            if ($count >= $previewWords) {
                break;
            }

            $previewContent .= substr($content, $position, strlen($word));
            $count++;
        }

        return $previewContent . '...';
    }

    /**
     * Get required tier display name
     */
    public function getTierDisplayName(?string $tier): string
    {
        return match($tier) {
            'basic' => 'Basic',
            'pro' => 'Pro',
            'team' => 'Team',
            default => 'Premium',
        };
    }

    /**
     * Get minimum plan price for tier
     */
    public function getTierPrice(string $tier): string
    {
        return match($tier) {
            'basic' => '$9.99',
            'pro' => '$19.99',
            'team' => '$49.99',
            default => '$9.99',
        };
    }

    /**
     * Check if user should see upgrade prompt
     */
    public function shouldShowUpgradePrompt(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Don't show to subscribed users
        if ($user->subscribed()) {
            return false;
        }

        // Trial users close to expiry
        if ($user->onTrial()) {
            $daysLeft = now()->diffInDays($user->trial_ends_at);
            return $daysLeft <= 3;
        }

        // Check if we recently showed prompt (don't spam)
        if ($user->last_upgrade_prompt_at) {
            $hoursSinceLastPrompt = now()->diffInHours($user->last_upgrade_prompt_at);
            return $hoursSinceLastPrompt >= 24; // Show once per day max
        }

        return true;
    }

    /**
     * Mark upgrade prompt as shown
     */
    public function markUpgradePromptShown(?User $user): void
    {
        if (!$user) {
            return;
        }

        $user->update(['last_upgrade_prompt_at' => now()]);
    }
}
