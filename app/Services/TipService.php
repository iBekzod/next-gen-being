<?php

namespace App\Services;

use App\Models\Tip;
use App\Models\User;
use App\Models\Post;
use App\Models\BloggerEarning;
use Illuminate\Support\Facades\Log;
use Exception;

class TipService
{
    private const PLATFORM_FEE_PERCENTAGE = 5; // 5% platform fee from tips
    private const LEMON_SQUEEZY_STORE = 'lemon_squeezy.store';
    private const TIP_VARIANT_ID = 'lemon_squeezy.tip_variant_id'; // Config key for tip variant

    private LemonSqueezyService $lemonSqueezy;

    public function __construct()
    {
        $this->lemonSqueezy = new LemonSqueezyService();
    }

    /**
     * Create a tip and generate LemonSqueezy checkout
     */
    public function initiateTip(
        User $fromUser,
        User $toUser,
        float $amount,
        string $currency = 'USD',
        ?Post $post = null,
        string $message = '',
        bool $isAnonymous = false
    ): array {
        try {
            // Validate amount
            if ($amount <= 0 || $amount < 1) {
                throw new Exception('Tip amount must be at least $1');
            }

            // Don't allow tipping yourself
            if ($fromUser->id === $toUser->id) {
                throw new Exception('You cannot tip yourself');
            }

            // Create tip record
            $tip = Tip::create([
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'post_id' => $post?->id,
                'amount' => $amount,
                'currency' => $currency,
                'message' => $message,
                'is_anonymous' => $isAnonymous,
                'status' => Tip::STATUS_PENDING,
            ]);

            // Create LemonSqueezy checkout
            $checkoutUrl = $this->createLemonSqueezyCheckout(
                $tip,
                $fromUser,
                $toUser,
                $amount,
                $currency
            );

            if (!$checkoutUrl) {
                $tip->markAsFailed();
                throw new Exception('Failed to create payment checkout');
            }

            Log::info('Tip initiated', [
                'tip_id' => $tip->id,
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'tip_id' => $tip->id,
                'checkout_url' => $checkoutUrl,
                'amount' => $amount,
                'currency' => $currency,
            ];
        } catch (Exception $e) {
            Log::error('Tip initiation failed', [
                'error' => $e->getMessage(),
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'amount' => $amount,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process a completed tip payment (called from webhook)
     */
    public function processTipPayment(Tip $tip): bool
    {
        try {
            // Mark tip as completed
            $tip->markAsCompleted();

            // Create earning record for the blogger
            $platformFee = $this->calculatePlatformFee($tip->amount);
            $bloggerAmount = $tip->amount - $platformFee;

            BloggerEarning::create([
                'user_id' => $tip->to_user_id,
                'type' => 'tip',
                'amount' => $bloggerAmount,
                'currency' => $tip->currency,
                'status' => 'pending',
                'metadata' => [
                    'tip_id' => $tip->id,
                    'from_user_id' => $tip->from_user_id,
                    'post_id' => $tip->post_id,
                    'platform_fee' => $platformFee,
                ],
            ]);

            // Create notification for recipient
            try {
                NotificationService::notifyTipReceived(
                    $tip->toUser,
                    $tip->fromUser,
                    $tip->amount,
                    $tip->post
                );
            } catch (Exception $e) {
                Log::warning('Failed to send tip notification', ['error' => $e->getMessage()]);
            }

            Log::info('Tip payment processed', [
                'tip_id' => $tip->id,
                'amount' => $tip->amount,
                'blogger_amount' => $bloggerAmount,
                'platform_fee' => $platformFee,
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Tip payment processing failed', [
                'tip_id' => $tip->id,
                'error' => $e->getMessage(),
            ]);

            $tip->markAsFailed();
            return false;
        }
    }

    /**
     * Get tips for a user with analytics
     */
    public function getUserTipsAnalytics(User $user, $days = 30): array
    {
        $completedTips = Tip::byRecipient($user)
            ->completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $totalAmount = (float) $completedTips->sum('amount');
        $tipCount = $completedTips->count();
        $averageAmount = $tipCount > 0 ? $totalAmount / $tipCount : 0;

        // Top tippers
        $topTippers = Tip::byRecipient($user)
            ->completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('from_user_id, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('from_user_id')
            ->orderByRaw('SUM(amount) DESC')
            ->limit(10)
            ->with('fromUser:id,name,username')
            ->get();

        // Tips by post
        $tipsByPost = Tip::byRecipient($user)
            ->completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('post_id')
            ->selectRaw('post_id, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('post_id')
            ->orderByRaw('SUM(amount) DESC')
            ->limit(10)
            ->with('post:id,title,slug')
            ->get();

        return [
            'total_amount' => $totalAmount,
            'tip_count' => $tipCount,
            'average_amount' => round($averageAmount, 2),
            'top_tippers' => $topTippers,
            'tips_by_post' => $tipsByPost,
        ];
    }

    /**
     * Get leaderboard of top tipped users
     */
    public function getTopTippedLeaderboard($limit = 20, $days = 30): array
    {
        return Tip::completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('to_user_id, SUM(amount) as total_tips, COUNT(*) as tip_count')
            ->groupBy('to_user_id')
            ->orderByRaw('SUM(amount) DESC')
            ->limit($limit)
            ->with('toUser:id,name,username,profile_image_url')
            ->get()
            ->map(fn($item) => [
                'user' => $item->toUser,
                'total_tips' => (float) $item->total_tips,
                'tip_count' => $item->tip_count,
                'rank' => null, // Will be set by caller
            ]);
    }

    /**
     * Get recent tips for a post
     */
    public function getRecentPostTips(Post $post, $limit = 10): array
    {
        return Tip::byPost($post)
            ->completed()
            ->with('fromUser:id,name,username,profile_image_url')
            ->recent()
            ->limit($limit)
            ->get()
            ->map(fn($tip) => [
                'id' => $tip->id,
                'amount' => $tip->amount,
                'currency' => $tip->currency,
                'message' => $tip->message,
                'from_user' => $tip->getDisplayNameAttribute(),
                'is_anonymous' => $tip->is_anonymous,
                'created_at' => $tip->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Get post tips statistics
     */
    public function getPostTipsStats(Post $post): array
    {
        $completedTips = Tip::byPost($post)->completed()->get();

        return [
            'total_tips_count' => $completedTips->count(),
            'total_tips_amount' => (float) $completedTips->sum('amount'),
            'average_tip' => $completedTips->count() > 0
                ? round((float) $completedTips->sum('amount') / $completedTips->count(), 2)
                : 0,
        ];
    }

    /**
     * Get recent tips for a user (received)
     */
    public function getRecentUserTips(User $user, $limit = 20): array
    {
        return Tip::byRecipient($user)
            ->completed()
            ->with('fromUser:id,name,username,profile_image_url')
            ->with('post:id,title,slug')
            ->recent()
            ->limit($limit)
            ->get()
            ->map(fn($tip) => [
                'id' => $tip->id,
                'amount' => $tip->amount,
                'from_user' => $tip->getDisplayNameAttribute(),
                'message' => $tip->message,
                'post_title' => $tip->post?->title,
                'created_at' => $tip->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    // Private helper methods

    private function createLemonSqueezyCheckout(
        Tip $tip,
        User $fromUser,
        User $toUser,
        float $amount,
        string $currency
    ): ?string {
        try {
            // Get or create the tip variant (this should be a fixed variant for tips)
            // For now, we'll use a generic digital product variant
            $variantId = config('lemon-squeezy.tip_variant_id');

            if (!$variantId) {
                Log::error('Tip variant ID not configured in lemon-squeezy config');
                return null;
            }

            $checkoutUrl = $this->lemonSqueezy->createCheckout([
                'product_id' => config('lemon-squeezy.store'),
                'variant_id' => $variantId,
                'custom_price' => (int) ($amount * 100), // Convert to cents
                'customer_email' => $fromUser->email,
                'custom_data' => [
                    'tip_id' => $tip->id,
                    'to_user_id' => $toUser->id,
                    'from_user_id' => $fromUser->id,
                    'post_id' => $tip->post_id,
                ],
                'checkout_data' => [
                    'email' => $fromUser->email,
                    'name' => $fromUser->name,
                ],
                'preview' => false,
                'expires_at' => now()->addDay()->toIso8601String(),
            ]);

            return $checkoutUrl;
        } catch (Exception $e) {
            Log::error('LemonSqueezy checkout creation failed', [
                'error' => $e->getMessage(),
                'tip_id' => $tip->id,
            ]);

            return null;
        }
    }

    private function calculatePlatformFee(float $amount): float
    {
        return $amount * self::PLATFORM_FEE_PERCENTAGE / 100;
    }
}
