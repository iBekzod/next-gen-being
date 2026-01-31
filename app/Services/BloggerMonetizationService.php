<?php

namespace App\Services;

use App\Models\User;
use App\Models\BloggerEarning;
use App\Notifications\MilestoneAchievedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BloggerMonetizationService
{
    /**
     * Follower milestone rewards (lower, sustainable amounts)
     */
    const FOLLOWER_MILESTONES = [
        10 => 2.00,
        25 => 5.00,
        50 => 10.00,
        100 => 25.00,
        250 => 50.00,
        500 => 100.00,
        1000 => 250.00,
        2500 => 500.00,
        5000 => 1000.00,
        10000 => 2500.00,
    ];

    /**
     * Check and award follower milestones for a blogger
     */
    public function checkFollowerMilestones(User $blogger): array
    {
        $currentFollowerCount = $blogger->followers()->count();
        $awarded = [];

        foreach (self::FOLLOWER_MILESTONES as $milestone => $amount) {
            // Check if blogger has reached this milestone
            if ($currentFollowerCount >= $milestone) {
                // Check if already awarded
                $alreadyAwarded = BloggerEarning::where('user_id', $blogger->id)
                    ->where('type', 'follower_milestone')
                    ->where('milestone_value', $milestone)
                    ->exists();

                if (!$alreadyAwarded) {
                    // Award the milestone
                    $earning = BloggerEarning::createFollowerMilestone(
                        $blogger,
                        $milestone,
                        $amount
                    );

                    $awarded[] = [
                        'milestone' => $milestone,
                        'amount' => $amount,
                        'earning_id' => $earning->id,
                    ];

                    Log::info('Follower milestone awarded', [
                        'blogger_id' => $blogger->id,
                        'blogger_name' => $blogger->name,
                        'milestone' => $milestone,
                        'amount' => $amount,
                    ]);

                    // Send notification to blogger
                    Notification::send($blogger, new MilestoneAchievedNotification(
                        milestone: $milestone,
                        amount: $amount,
                        earning: $earning
                    ));
                }
            }
        }

        return $awarded;
    }

    /**
     * Get next milestone for a blogger
     */
    public function getNextMilestone(User $blogger): ?array
    {
        $currentFollowerCount = $blogger->followers()->count();

        foreach (self::FOLLOWER_MILESTONES as $milestone => $amount) {
            if ($currentFollowerCount < $milestone) {
                return [
                    'milestone' => $milestone,
                    'amount' => $amount,
                    'remaining' => $milestone - $currentFollowerCount,
                    'progress_percentage' => round(($currentFollowerCount / $milestone) * 100, 1),
                ];
            }
        }

        return null; // Reached all milestones
    }

    /**
     * Get all achieved milestones for a blogger
     */
    public function getAchievedMilestones(User $blogger): array
    {
        return BloggerEarning::where('user_id', $blogger->id)
            ->where('type', 'follower_milestone')
            ->orderBy('milestone_value')
            ->get()
            ->map(fn($earning) => [
                'milestone' => $earning->milestone_value,
                'amount' => $earning->amount,
                'status' => $earning->status,
                'awarded_at' => $earning->created_at,
                'paid_at' => $earning->paid_at,
            ])
            ->toArray();
    }

    /**
     * Calculate total earnings for a blogger
     */
    public function getTotalEarnings(User $blogger): array
    {
        $earnings = BloggerEarning::where('user_id', $blogger->id);

        return [
            'total' => $earnings->sum('amount'),
            'pending' => $earnings->where('status', 'pending')->sum('amount'),
            'paid' => $earnings->where('status', 'paid')->sum('amount'),
            'by_type' => [
                'follower_milestone' => $earnings->where('type', 'follower_milestone')->sum('amount'),
                'premium_content' => $earnings->where('type', 'premium_content')->sum('amount'),
                'engagement_bonus' => $earnings->where('type', 'engagement_bonus')->sum('amount'),
            ],
        ];
    }

    /**
     * Check if blogger is eligible for payout
     */
    public function isEligibleForPayout(User $blogger, float $minimumThreshold = 50.00): bool
    {
        $pendingAmount = BloggerEarning::where('user_id', $blogger->id)
            ->where('status', 'pending')
            ->sum('amount');

        return $pendingAmount >= $minimumThreshold;
    }

    /**
     * Get blogger stats for dashboard
     */
    public function getBloggerStats(User $blogger): array
    {
        $earnings = $this->getTotalEarnings($blogger);
        $nextMilestone = $this->getNextMilestone($blogger);

        return [
            'followers' => [
                'count' => $blogger->followers()->count(),
                'next_milestone' => $nextMilestone,
            ],
            'posts' => [
                'total' => $blogger->posts()->count(),
                'published' => $blogger->posts()->where('status', 'published')->count(),
                'draft' => $blogger->posts()->where('status', 'draft')->count(),
                'premium' => $blogger->posts()->where('is_premium', true)->count(),
            ],
            'earnings' => $earnings,
            'eligible_for_payout' => $this->isEligibleForPayout($blogger),
        ];
    }

    /**
     * Award engagement bonus based on metrics
     */
    public function checkEngagementBonuses(User $blogger): array
    {
        $awarded = [];

        // Get total views across all posts
        $totalViews = $blogger->posts()->sum('views');

        // View milestones
        $viewMilestones = [
            10000 => 10.00,
            50000 => 50.00,
            100000 => 100.00,
            500000 => 500.00,
            1000000 => 1000.00,
        ];

        foreach ($viewMilestones as $milestone => $amount) {
            if ($totalViews >= $milestone) {
                // Check if already awarded
                $alreadyAwarded = BloggerEarning::where('user_id', $blogger->id)
                    ->where('type', 'engagement_bonus')
                    ->where('metadata->views_milestone', $milestone)
                    ->exists();

                if (!$alreadyAwarded) {
                    $earning = BloggerEarning::createEngagementBonus(
                        $blogger,
                        $amount,
                        [
                            'views_milestone' => $milestone,
                            'actual_views' => $totalViews,
                        ]
                    );

                    $awarded[] = [
                        'type' => 'views',
                        'milestone' => $milestone,
                        'amount' => $amount,
                    ];

                    Log::info('Engagement bonus awarded', [
                        'blogger_id' => $blogger->id,
                        'type' => 'views',
                        'milestone' => $milestone,
                        'amount' => $amount,
                    ]);
                }
            }
        }

        return $awarded;
    }

    /**
     * Process premium content revenue share
     * Called when a user purchases premium content access
     */
    public function processPremiumContentPurchase(User $blogger, $post, float $purchaseAmount, float $revenueShare = 0.70): BloggerEarning
    {
        $bloggerAmount = $purchaseAmount * $revenueShare;

        $earning = BloggerEarning::createPremiumContentEarning(
            $blogger,
            $post,
            $bloggerAmount
        );

        Log::info('Premium content revenue share created', [
            'blogger_id' => $blogger->id,
            'post_id' => $post->id,
            'purchase_amount' => $purchaseAmount,
            'blogger_amount' => $bloggerAmount,
            'revenue_share' => $revenueShare,
        ]);

        return $earning;
    }

    /**
     * Record digital product sale earnings
     */
    public function recordDigitalProductSale(User $blogger, $product, float $amount): BloggerEarning
    {
        $earning = BloggerEarning::create([
            'user_id' => $blogger->id,
            'type' => 'digital_product_sale',
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'pending',
            'metadata' => [
                'product_id' => $product->id,
                'product_title' => $product->title,
                'product_type' => $product->type,
                'revenue_share' => $product->revenue_share_percentage,
            ],
        ]);

        Log::info('Digital product sale recorded', [
            'blogger_id' => $blogger->id,
            'product_id' => $product->id,
            'amount' => $amount,
        ]);

        return $earning;
    }

    /**
     * Get earnings summary for admin dashboard
     */
    public function getPlatformEarningsSummary(): array
    {
        $totalBloggers = User::whereHas('roles', fn($q) => $q->where('slug', 'blogger'))->count();
        $bloggersWithEarnings = BloggerEarning::distinct('user_id')->count('user_id');

        return [
            'total_bloggers' => $totalBloggers,
            'bloggers_with_earnings' => $bloggersWithEarnings,
            'total_earnings' => [
                'all_time' => BloggerEarning::sum('amount'),
                'pending' => BloggerEarning::where('status', 'pending')->sum('amount'),
                'paid' => BloggerEarning::where('status', 'paid')->sum('amount'),
            ],
            'by_type' => [
                'follower_milestones' => BloggerEarning::where('type', 'follower_milestone')->sum('amount'),
                'premium_content' => BloggerEarning::where('type', 'premium_content')->sum('amount'),
                'engagement_bonuses' => BloggerEarning::where('type', 'engagement_bonus')->sum('amount'),
            ],
            'pending_payouts' => BloggerEarning::where('status', 'pending')
                ->selectRaw('user_id, SUM(amount) as total')
                ->groupBy('user_id')
                ->having('total', '>=', 50)
                ->count(),
        ];
    }
}
