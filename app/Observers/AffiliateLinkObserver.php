<?php

namespace App\Observers;

use App\Models\AffiliateLink;

class AffiliateLinkObserver
{
    /**
     * Handle the AffiliateLink "created" event.
     */
    public function created(AffiliateLink $affiliateLink): void
    {
        \Log::info('Affiliate link created', [
            'link_id' => $affiliateLink->id,
            'user_id' => $affiliateLink->user_id,
            'product_name' => $affiliateLink->product_name,
        ]);
    }

    /**
     * Handle the AffiliateLink "updated" event.
     */
    public function updated(AffiliateLink $affiliateLink): void
    {
        // Track click count updates
        if ($affiliateLink->isDirty('clicks')) {
            \Log::info('Affiliate link clicked', [
                'link_id' => $affiliateLink->id,
                'total_clicks' => $affiliateLink->clicks,
            ]);
        }

        // Track conversion updates
        if ($affiliateLink->isDirty('conversions')) {
            \Log::info('Affiliate conversion recorded', [
                'link_id' => $affiliateLink->id,
                'total_conversions' => $affiliateLink->conversions,
                'conversion_rate' => $affiliateLink->clicks > 0
                    ? ($affiliateLink->conversions / $affiliateLink->clicks * 100)
                    : 0,
            ]);
        }

        // Track earnings updates
        if ($affiliateLink->isDirty('earnings')) {
            \Log::info('Affiliate earnings updated', [
                'link_id' => $affiliateLink->id,
                'total_earnings' => $affiliateLink->earnings,
            ]);
        }
    }

    /**
     * Handle the AffiliateLink "deleted" event.
     */
    public function deleted(AffiliateLink $affiliateLink): void
    {
        \Log::info('Affiliate link deleted', [
            'link_id' => $affiliateLink->id,
            'final_clicks' => $affiliateLink->clicks,
            'final_earnings' => $affiliateLink->earnings,
        ]);
    }

    /**
     * Handle the AffiliateLink "restored" event.
     */
    public function restored(AffiliateLink $affiliateLink): void
    {
        \Log::info('Affiliate link restored', ['link_id' => $affiliateLink->id]);
    }

    /**
     * Handle the AffiliateLink "force deleted" event.
     */
    public function forceDeleted(AffiliateLink $affiliateLink): void
    {
        \Log::info('Affiliate link force deleted', ['link_id' => $affiliateLink->id]);
    }
}
