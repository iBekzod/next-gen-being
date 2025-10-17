<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\SocialShareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialShareController extends Controller
{
    protected SocialShareService $socialShareService;

    public function __construct(SocialShareService $socialShareService)
    {
        $this->socialShareService = $socialShareService;
    }

    /**
     * Track a social share
     */
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|in:twitter,linkedin,facebook,whatsapp,telegram,email,copy',
            'post_id' => 'required|exists:posts,id',
            'url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = auth()->check() ? auth()->id() : null;

        $share = $this->socialShareService->trackShare(
            postId: $request->post_id,
            platform: $request->platform,
            userId: $userId,
            referrer: $request->header('referer'),
            metadata: [
                'url' => $request->url,
                'user_agent' => $request->userAgent(),
            ]
        );

        // Get updated share count
        $shareCount = $this->socialShareService->getShareCount($request->post_id, false);

        return response()->json([
            'success' => true,
            'share_id' => $share->id,
            'total_shares' => $shareCount,
        ]);
    }

    /**
     * Get share count for a post
     */
    public function getShareCount(int $postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $analytics = $this->socialShareService->getShareAnalytics($postId);

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get platform breakdown for a post
     */
    public function getPlatformBreakdown(int $postId)
    {
        $breakdown = $this->socialShareService->getPlatformBreakdown($postId);

        return response()->json([
            'success' => true,
            'data' => $breakdown,
        ]);
    }

    /**
     * Get top shared posts
     */
    public function getTopShared(Request $request)
    {
        $limit = $request->input('limit', 10);
        $days = $request->input('days', 30);

        $topPosts = $this->socialShareService->getTopSharedPosts($limit, $days);

        return response()->json([
            'success' => true,
            'data' => $topPosts,
        ]);
    }

    /**
     * Generate UTM URL for sharing
     */
    public function generateUtmUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'platform' => 'required|in:twitter,linkedin,facebook,whatsapp,telegram,email',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $utmUrl = $this->socialShareService->generateUtmUrl(
            $request->url,
            $request->platform,
            $request->post_id
        );

        return response()->json([
            'success' => true,
            'utm_url' => $utmUrl,
        ]);
    }
}
