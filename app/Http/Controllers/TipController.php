<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Services\TipService;
use Illuminate\Http\Request;

class TipController extends Controller
{
    public function __construct(private TipService $tipService) {}

    /**
     * Initiate a tip to a creator
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'post_id' => 'nullable|exists:posts,id',
            'message' => 'nullable|string|max:500',
            'is_anonymous' => 'boolean',
        ]);

        $result = $this->tipService->initiateTip(
            fromUser: auth()->user(),
            toUser: User::find($validated['to_user_id']),
            amount: $validated['amount'],
            post: $validated['post_id'] ? Post::find($validated['post_id']) : null,
            message: $validated['message'] ?? '',
            isAnonymous: $validated['is_anonymous'] ?? false
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get tip statistics for a user
     */
    public function stats($userId)
    {
        $user = User::findOrFail($userId);
        $stats = $this->tipService->getUserTipsAnalytics($user);

        return response()->json($stats);
    }

    /**
     * Get top tipped creators leaderboard
     */
    public function leaderboard(Request $request)
    {
        $limit = $request->get('limit', 20);
        $leaderboard = $this->tipService->getTopTippedLeaderboard($limit);

        return response()->json($leaderboard);
    }

    /**
     * Webhook handler for tip completion
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();

        $tip = \App\Models\Tip::where(
            'stripe_payment_intent_id',
            $payload['data']['payment_intent_id'] ?? null
        )->first();

        if ($tip) {
            $this->tipService->processTipPayment($tip);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get tips received by authenticated user
     */
    public function myTipsReceived()
    {
        $tips = auth()->user()->tipsReceived()->latest()->paginate(20);

        return response()->json($tips);
    }

    /**
     * Get tips sent by authenticated user
     */
    public function myTipsSent()
    {
        $tips = auth()->user()->tipsSent()->latest()->paginate(20);

        return response()->json($tips);
    }
}
