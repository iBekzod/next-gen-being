<?php

namespace App\Services;

use App\Models\User;
use App\Models\AffiliateLink;
use App\Models\AffiliateClick;
use App\Models\AffiliateConversion;
use App\Models\BloggerEarning;
use Illuminate\Support\Facades\Log;

class AffiliateService
{
    const DEFAULT_COMMISSION_RATE = 15; // 15% commission on signups
    const SIGNUP_COMMISSION_VALUE = 10; // $10 per signup
    const SUBSCRIPTION_COMMISSION_RATE = 10; // 10% of subscription value

    /**
     * Create affiliate link for creator
     */
    public function createAffiliateLink(User $creator, array $data): array
    {
        try {
            $link = AffiliateLink::createForCreator($creator, [
                'affiliate_url' => $data['affiliate_url'] ?? route('home'),
                'commission_rate' => $data['commission_rate'] ?? self::DEFAULT_COMMISSION_RATE,
                'description' => $data['description'] ?? 'Affiliate Link',
            ]);

            Log::info('Affiliate link created', [
                'creator_id' => $creator->id,
                'link_id' => $link->id,
                'code' => $link->referral_code,
            ]);

            return [
                'success' => true,
                'link' => [
                    'id' => $link->id,
                    'referral_code' => $link->referral_code,
                    'shareable_url' => $link->getShareableUrl(),
                    'affiliate_url' => $link->affiliate_url,
                    'commission_rate' => $link->commission_rate,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create affiliate link', [
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track affiliate click
     */
    public function trackClick(AffiliateLink $link, ?User $user = null, string $ipAddress = '', string $userAgent = ''): AffiliateClick
    {
        return AffiliateClick::create([
            'affiliate_link_id' => $link->id,
            'user_id' => $user?->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referrer' => request()->header('Referer'),
            'converted' => false,
        ]);
    }

    /**
     * Record affiliate conversion (signup)
     */
    public function recordSignupConversion(AffiliateClick $click, User $newUser): array
    {
        try {
            $commissionAmount = self::SIGNUP_COMMISSION_VALUE * ($click->link->commission_rate / 100);

            $conversion = AffiliateConversion::create([
                'affiliate_link_id' => $click->affiliate_link_id,
                'click_id' => $click->id,
                'user_id' => $newUser->id,
                'conversion_type' => 'signup',
                'conversion_value' => self::SIGNUP_COMMISSION_VALUE,
                'commission_rate' => $click->link->commission_rate,
                'commission_amount' => $commissionAmount,
                'status' => AffiliateConversion::STATUS_COMPLETED,
            ]);

            // Mark click as converted
            $click->update(['converted' => true]);

            // Create earnings record for creator
            BloggerEarning::create([
                'user_id' => $click->link->creator_id,
                'type' => 'affiliate_commission',
                'amount' => $commissionAmount,
                'currency' => 'USD',
                'status' => 'pending',
                'metadata' => [
                    'conversion_id' => $conversion->id,
                    'new_user_id' => $newUser->id,
                    'commission_rate' => $click->link->commission_rate,
                ],
            ]);

            Log::info('Signup conversion recorded', [
                'conversion_id' => $conversion->id,
                'creator_id' => $click->link->creator_id,
                'commission_amount' => $commissionAmount,
            ]);

            return [
                'success' => true,
                'conversion_id' => $conversion->id,
                'commission_amount' => $commissionAmount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to record signup conversion', [
                'click_id' => $click->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Record subscription affiliate conversion
     */
    public function recordSubscriptionConversion(AffiliateClick $click, User $subscriber, float $subscriptionValue): array
    {
        try {
            $commissionAmount = $subscriptionValue * (self::SUBSCRIPTION_COMMISSION_RATE / 100);

            $conversion = AffiliateConversion::create([
                'affiliate_link_id' => $click->affiliate_link_id,
                'click_id' => $click->id,
                'user_id' => $subscriber->id,
                'conversion_type' => 'subscription',
                'conversion_value' => $subscriptionValue,
                'commission_rate' => self::SUBSCRIPTION_COMMISSION_RATE,
                'commission_amount' => $commissionAmount,
                'status' => AffiliateConversion::STATUS_COMPLETED,
            ]);

            // Mark click as converted
            $click->update(['converted' => true]);

            // Create earnings record
            BloggerEarning::create([
                'user_id' => $click->link->creator_id,
                'type' => 'affiliate_commission',
                'amount' => $commissionAmount,
                'currency' => 'USD',
                'status' => 'pending',
                'metadata' => [
                    'conversion_id' => $conversion->id,
                    'subscription_value' => $subscriptionValue,
                    'subscriber_id' => $subscriber->id,
                ],
            ]);

            return [
                'success' => true,
                'conversion_id' => $conversion->id,
                'commission_amount' => $commissionAmount,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to record subscription conversion', [
                'click_id' => $click->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get affiliate link statistics
     */
    public function getLinkStats(AffiliateLink $link, $days = 30): array
    {
        $clicks = $link->clicks()->recent($days)->get();
        $conversions = $link->conversions()->recent($days)->completed()->get();

        $totalClicks = $clicks->count();
        $convertedClicks = $clicks->where('converted', true)->count();
        $conversionRate = $totalClicks > 0 ? round(($convertedClicks / $totalClicks) * 100, 2) : 0;

        $totalCommissions = (float) $conversions->sum('commission_amount');
        $avgCommission = $conversions->count() > 0
            ? round($totalCommissions / $conversions->count(), 2)
            : 0;

        return [
            'link_id' => $link->id,
            'referral_code' => $link->referral_code,
            'clicks_total' => $totalClicks,
            'clicks_converted' => $convertedClicks,
            'conversion_rate' => $conversionRate,
            'conversions_total' => $conversions->count(),
            'total_commissions' => round($totalCommissions, 2),
            'avg_commission' => $avgCommission,
            'by_type' => $this->getConversionsByType($link, $days),
        ];
    }

    /**
     * Get creator's affiliate earnings
     */
    public function getCreatorAffiliateEarnings(User $creator, $days = 30): array
    {
        $links = AffiliateLink::byCreator($creator)->get();

        $totalEarnings = 0;
        $linkStats = [];

        foreach ($links as $link) {
            $stats = $this->getLinkStats($link, $days);
            $linkStats[] = $stats;
            $totalEarnings += $stats['total_commissions'];
        }

        // Get pending earnings
        $pendingEarnings = (float) BloggerEarning::where('user_id', $creator->id)
            ->where('type', 'affiliate_commission')
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('amount');

        return [
            'total_earnings' => round($totalEarnings, 2),
            'pending_earnings' => round($pendingEarnings, 2),
            'affiliate_links_count' => $links->count(),
            'links' => $linkStats,
        ];
    }

    /**
     * Get top affiliate creators (leaderboard)
     */
    public function getTopAffiliateCreators($limit = 20, $days = 30): array
    {
        return User::selectRaw('
                users.*,
                COALESCE(SUM(affiliate_conversions.commission_amount), 0) as total_commissions
            ')
            ->join('affiliate_links', 'users.id', '=', 'affiliate_links.creator_id')
            ->join('affiliate_conversions', 'affiliate_links.id', '=', 'affiliate_conversions.affiliate_link_id')
            ->where('affiliate_conversions.status', 'completed')
            ->where('affiliate_conversions.created_at', '>=', now()->subDays($days))
            ->groupBy('users.id')
            ->orderByRaw('SUM(affiliate_conversions.commission_amount) DESC')
            ->limit($limit)
            ->get()
            ->map(fn($user) => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'profile_image_url' => $user->profile_image_url,
                ],
                'total_commissions' => (float) $user->total_commissions,
            ])
            ->toArray();
    }

    /**
     * Get conversions by type
     */
    private function getConversionsByType(AffiliateLink $link, $days): array
    {
        $signups = $link->conversions()
            ->where('conversion_type', 'signup')
            ->completed()
            ->recent($days)
            ->count();

        $subscriptions = $link->conversions()
            ->where('conversion_type', 'subscription')
            ->completed()
            ->recent($days)
            ->count();

        return [
            'signups' => $signups,
            'subscriptions' => $subscriptions,
        ];
    }
}
