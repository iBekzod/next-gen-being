<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\ReaderTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReaderTrackingController extends Controller
{
    protected ReaderTrackingService $readerTrackingService;

    public function __construct(ReaderTrackingService $readerTrackingService)
    {
        $this->readerTrackingService = $readerTrackingService;
    }

    /**
     * Record reader activity (keep them marked as active)
     */
    public function recordActivity(Post $post, Request $request): JsonResponse
    {
        try {
            $sessionId = $request->session()?->get('reader_session_id');

            $this->readerTrackingService->recordActivity(
                $post->id,
                Auth::user(),
                $sessionId
            );

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get live reader count for a post
     */
    public function getLiveCount(Post $post): JsonResponse
    {
        $count = $this->readerTrackingService->getActiveReaderCount($post->id);
        $breakdown = $this->readerTrackingService->getReaderBreakdown($post->id);

        return response()->json([
            'count' => $count,
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Get live readers list
     */
    public function getLiveReadersList(Post $post): JsonResponse
    {
        $readers = $this->readerTrackingService->getLiveReadersList($post->id, 20);

        return response()->json(['readers' => $readers]);
    }

    /**
     * Get reader locations for map
     */
    public function getReaderLocations(Post $post): JsonResponse
    {
        $mapData = $this->readerTrackingService->getReaderMapData($post->id);

        return response()->json($mapData);
    }

    /**
     * Get top countries
     */
    public function getTopCountries(Post $post): JsonResponse
    {
        $countries = $this->readerTrackingService->getTopCountries($post->id, 10);

        return response()->json(['countries' => $countries]);
    }

    /**
     * Get reader analytics
     */
    public function getAnalytics(Post $post): JsonResponse
    {
        $analytics = $this->readerTrackingService->getReaderAnalytics($post->id);

        return response()->json($analytics);
    }

    /**
     * Cleanup inactive readers (admin only)
     */
    public function cleanupInactive(): JsonResponse
    {
        $this->authorize('admin');

        $count = $this->readerTrackingService->cleanupInactiveReaders();

        return response()->json(['cleaned' => $count]);
    }
}
