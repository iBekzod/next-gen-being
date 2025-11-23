<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Services\ChallengeService;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function __construct(private ChallengeService $challengeService) {}

    public function index(Request $request)
    {
        $status = $request->get('status', 'active');
        $challenges = Challenge::where('status', $status)->paginate(20);

        return response()->json($challenges);
    }

    public function show(Challenge $challenge)
    {
        return response()->json($challenge);
    }

    public function join(Request $request, Challenge $challenge)
    {
        $result = $this->challengeService->joinChallenge(auth()->user(), $challenge);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function updateProgress(Request $request, Challenge $challenge)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $result = $this->challengeService->updateProgress(
            auth()->user(),
            $challenge,
            $validated['amount']
        );

        return response()->json($result);
    }

    public function claimReward(Challenge $challenge)
    {
        $result = $this->challengeService->claimReward(auth()->user(), $challenge);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function leaderboard(Request $request, Challenge $challenge)
    {
        $limit = $request->get('limit', 20);
        $leaderboard = $this->challengeService->getChallengeLeaderboard($challenge, $limit);

        return response()->json($leaderboard);
    }

    public function stats(Challenge $challenge)
    {
        $stats = $this->challengeService->getChallengeStats($challenge);

        return response()->json($stats);
    }

    public function myChallenges()
    {
        $challenges = $this->challengeService->getUserChallenges(auth()->user());

        return response()->json($challenges);
    }
}
