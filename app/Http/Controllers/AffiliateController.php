<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Services\AffiliateService;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}

    /**
     * Create new affiliate link
     */
    public function createLink(Request $request)
    {
        $validated = $request->validate([
            'commission_rate' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $result = $this->affiliateService->createAffiliateLink(auth()->user(), $validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * List creator's affiliate links
     */
    public function listLinks()
    {
        $links = AffiliateLink::where('creator_id', auth()->id())->get();

        return response()->json($links);
    }

    /**
     * Get statistics for a link
     */
    public function stats(AffiliateLink $link, Request $request)
    {
        if ($link->creator_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $days = $request->get('days', 30);
        $stats = $this->affiliateService->getLinkStats($link, $days);

        return response()->json($stats);
    }

    /**
     * Get creator's affiliate earnings
     */
    public function earnings(Request $request)
    {
        $days = $request->get('days', 30);
        $earnings = $this->affiliateService->getCreatorAffiliateEarnings(auth()->user(), $days);

        return response()->json($earnings);
    }

    /**
     * Get top affiliate creators leaderboard
     */
    public function leaderboard(Request $request)
    {
        $limit = $request->get('limit', 20);
        $days = $request->get('days', 30);

        $leaderboard = $this->affiliateService->getTopAffiliateCreators($limit, $days);

        return response()->json($leaderboard);
    }

    /**
     * Track affiliate click (webhook/public endpoint)
     */
    public function trackClick($code)
    {
        $link = AffiliateLink::where('referral_code', $code)->first();

        if (!$link) {
            return response()->json(['error' => 'Link not found'], 404);
        }

        $click = $this->affiliateService->trackClick(
            $link,
            auth()->user(),
            request()->ip(),
            request()->userAgent()
        );

        // Redirect to affiliate URL with tracking
        return redirect($link->affiliate_url);
    }
}
