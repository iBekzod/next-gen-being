<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\StreakService;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    public function __construct(private StreakService $streakService) {}

    /**
     * Get streak statistics for a user
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);
        $stats = $this->streakService->getUserStreakStats($user);

        return response()->json($stats);
    }

    /**
     * Get reading streak leaderboard
     */
    public function readingLeaderboard(Request $request)
    {
        $limit = $request->get('limit', 20);
        $days = $request->get('days', 30);
        $leaderboard = $this->streakService->getReadingStreakLeaderboard($limit, $days);

        return response()->json($leaderboard);
    }

    /**
     * Get writing streak leaderboard
     */
    public function writingLeaderboard(Request $request)
    {
        $limit = $request->get('limit', 20);
        $days = $request->get('days', 30);
        $leaderboard = $this->streakService->getWritingStreakLeaderboard($limit, $days);

        return response()->json($leaderboard);
    }

    /**
     * Get my streak statistics
     */
    public function myStreaks()
    {
        $stats = $this->streakService->getUserStreakStats(auth()->user());

        return response()->json($stats);
    }

    /**
     * Record reading activity
     */
    public function recordReading()
    {
        $this->streakService->recordReadingActivity(auth()->user());

        return response()->json(['success' => true, 'message' => 'Reading recorded']);
    }

    /**
     * Record writing activity
     */
    public function recordWriting(Request $request)
    {
        $post = \App\Models\Post::find($request->get('post_id'));

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $this->streakService->recordWritingActivity(auth()->user(), $post);

        return response()->json(['success' => true, 'message' => 'Writing recorded']);
    }
}
