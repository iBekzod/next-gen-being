<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ChallengeService
{
    /**
     * Get active challenges
     */
    public function getActiveChallenges($limit = 20): array
    {
        return Challenge::active()
            ->withCount(['participants', 'participants as completed_count' => function ($q) {
                $q->where('is_completed', true);
            }])
            ->orderBy('ends_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn($challenge) => [
                'id' => $challenge->id,
                'name' => $challenge->name,
                'description' => $challenge->description,
                'type' => $challenge->type,
                'target_value' => $challenge->target_value,
                'reward_points' => $challenge->reward_points,
                'difficulty' => $challenge->difficulty,
                'icon' => $challenge->icon,
                'participants_count' => $challenge->participants_count,
                'completed_count' => $challenge->completed_count,
                'completion_percentage' => $challenge->participants_count > 0
                    ? round(($challenge->completed_count / $challenge->participants_count) * 100, 1)
                    : 0,
                'days_remaining' => $challenge->getDaysRemainingAttribute(),
                'progress_percentage' => $challenge->getProgressPercentageAttribute(),
            ]);
    }

    /**
     * Get upcoming challenges
     */
    public function getUpcomingChallenges($limit = 10): array
    {
        return Challenge::upcoming()
            ->orderBy('starts_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn($challenge) => [
                'id' => $challenge->id,
                'name' => $challenge->name,
                'description' => $challenge->description,
                'type' => $challenge->type,
                'difficulty' => $challenge->difficulty,
                'icon' => $challenge->icon,
                'starts_at' => $challenge->starts_at->diffForHumans(),
                'reward_points' => $challenge->reward_points,
            ]);
    }

    /**
     * Get user's challenge participation and progress
     */
    public function getUserChallenges(User $user): array
    {
        $active = ChallengeParticipant::byUser($user)
            ->whereHas('challenge', fn($q) => $q->active())
            ->with('challenge:id,name,type,target_value,reward_points,icon,difficulty,ends_at')
            ->get()
            ->map(fn($participant) => [
                'challenge' => [
                    'id' => $participant->challenge->id,
                    'name' => $participant->challenge->name,
                    'type' => $participant->challenge->type,
                    'target_value' => $participant->challenge->target_value,
                    'reward_points' => $participant->challenge->reward_points,
                    'icon' => $participant->challenge->icon,
                    'difficulty' => $participant->challenge->difficulty,
                ],
                'progress' => $participant->progress,
                'progress_percentage' => $participant->getProgressPercentageAttribute(),
                'is_completed' => $participant->is_completed,
                'completed_at' => $participant->completed_at?->diffForHumans(),
                'reward_claimed' => $participant->reward_claimed,
            ]);

        $completed = ChallengeParticipant::byUser($user)
            ->completed()
            ->whereHas('challenge', fn($q) => $q->ended())
            ->with('challenge:id,name,type,reward_points,icon')
            ->limit(10)
            ->get()
            ->map(fn($participant) => [
                'challenge' => [
                    'id' => $participant->challenge->id,
                    'name' => $participant->challenge->name,
                    'type' => $participant->challenge->type,
                    'reward_points' => $participant->challenge->reward_points,
                    'icon' => $participant->challenge->icon,
                ],
                'completed_at' => $participant->completed_at->diffForHumans(),
                'reward_claimed' => $participant->reward_claimed,
            ]);

        return [
            'active_challenges' => $active,
            'completed_challenges' => $completed,
            'total_challenges_completed' => ChallengeParticipant::byUser($user)->completed()->count(),
            'total_rewards_earned' => ChallengeParticipant::byUser($user)->rewardClaimed()->count(),
        ];
    }

    /**
     * Join a user to a challenge
     */
    public function joinChallenge(User $user, Challenge $challenge): array
    {
        try {
            if (!$challenge->isActive()) {
                return [
                    'success' => false,
                    'message' => 'Challenge is not active',
                ];
            }

            $participant = ChallengeParticipant::getOrCreate($challenge, $user);

            Log::info('User joined challenge', [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
            ]);

            return [
                'success' => true,
                'message' => 'Successfully joined challenge',
                'participant_id' => $participant->id,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to join challenge', [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update user's progress on a challenge
     */
    public function updateProgress(User $user, Challenge $challenge, $amount = 1): array
    {
        try {
            $participant = ChallengeParticipant::byChallenge($challenge)
                ->byUser($user)
                ->first();

            if (!$participant) {
                // Auto-join if not already participating
                $this->joinChallenge($user, $challenge);
                $participant = ChallengeParticipant::byChallenge($challenge)
                    ->byUser($user)
                    ->first();
            }

            $wasCompleted = $participant->is_completed;
            $participant->incrementProgress($amount);

            Log::info('Challenge progress updated', [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'progress' => $participant->progress,
                'is_completed' => $participant->is_completed,
            ]);

            $response = [
                'success' => true,
                'progress' => $participant->progress,
                'progress_percentage' => $participant->getProgressPercentageAttribute(),
                'is_completed' => $participant->is_completed,
            ];

            // If just completed, notify user
            if (!$wasCompleted && $participant->is_completed) {
                try {
                    NotificationService::notifyChallengeCompleted($user, $challenge);
                } catch (\Exception $e) {
                    Log::warning('Failed to notify challenge completion', ['error' => $e->getMessage()]);
                }

                $response['newly_completed'] = true;
                $response['reward_points'] = $challenge->reward_points;
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to update challenge progress', [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Claim reward for completed challenge
     */
    public function claimReward(User $user, Challenge $challenge): array
    {
        try {
            $participant = ChallengeParticipant::byChallenge($challenge)
                ->byUser($user)
                ->first();

            if (!$participant || !$participant->is_completed) {
                return [
                    'success' => false,
                    'message' => 'Challenge not completed',
                ];
            }

            if ($participant->reward_claimed) {
                return [
                    'success' => false,
                    'message' => 'Reward already claimed',
                ];
            }

            $participant->claimReward();

            Log::info('Challenge reward claimed', [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'reward_points' => $challenge->reward_points,
            ]);

            return [
                'success' => true,
                'message' => 'Reward claimed successfully',
                'reward_points' => $challenge->reward_points,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to claim reward', [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get challenge leaderboard
     */
    public function getChallengeLeaderboard(Challenge $challenge, $limit = 50): array
    {
        return ChallengeParticipant::byChallenge($challenge)
            ->with('user:id,name,username,profile_image_url')
            ->orderBy('is_completed', 'desc')
            ->orderBy('progress', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($participant, $index) => [
                'rank' => $index + 1,
                'user' => $participant->user,
                'progress' => $participant->progress,
                'progress_percentage' => $participant->getProgressPercentageAttribute(),
                'is_completed' => $participant->is_completed,
                'completed_at' => $participant->completed_at?->diffForHumans(),
            ]);
    }

    /**
     * Get challenge stats
     */
    public function getChallengeStats(Challenge $challenge): array
    {
        $total = $challenge->participants()->count();
        $completed = $challenge->participants()->completed()->count();
        $rewardsClaimed = $challenge->participants()->rewardClaimed()->count();

        return [
            'challenge_id' => $challenge->id,
            'total_participants' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'rewards_claimed' => $rewardsClaimed,
            'total_rewards_value' => $rewardsClaimed * $challenge->reward_points,
        ];
    }
}
