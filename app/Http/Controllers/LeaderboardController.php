<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(private LeaderboardService $leaderboardService) {}

    public function creators(Request $request)
    {
        $limit = $request->get('limit', 20);
        $days = $request->get('days', 30);
        $metric = $request->get('metric', 'engagement');

        $leaderboard = $this->leaderboardService->getTopCreators($limit, $days, $metric);

        return response()->json($leaderboard);
    }

    public function readers(Request $request)
    {
        $limit = $request->get('limit', 20);
        $metric = $request->get('metric', 'reading_streak');

        $leaderboard = $this->leaderboardService->getTopReaders($limit, $metric);

        return response()->json($leaderboard);
    }

    public function engagers(Request $request)
    {
        $limit = $request->get('limit', 20);
        $days = $request->get('days', 30);

        $leaderboard = $this->leaderboardService->getTopEngagers($limit, $days);

        return response()->json($leaderboard);
    }

    public function trending(Request $request)
    {
        $limit = $request->get('limit', 10);
        $days = $request->get('days', 7);

        $trending = $this->leaderboardService->getTrendingPosts($limit, $days);

        return response()->json($trending);
    }

    public function userRank(Request $request)
    {
        $userId = $request->get('user_id', auth()->id());
        $metric = $request->get('metric', 'engagement');
        $days = $request->get('days', 30);

        $rank = $this->leaderboardService->getUserRank(
            \App\Models\User::find($userId),
            $metric,
            $days
        );

        return response()->json(['rank' => $rank]);
    }
}
