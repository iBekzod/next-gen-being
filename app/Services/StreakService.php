<?php

namespace App\Services;

use App\Models\User;
use App\Models\Streak;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class StreakService
{
    /**
     * Record reading activity for a user
     * Called when user reads a post
     */
    public function recordReading(User $user): array
    {
        try {
            $streak = Streak::getOrCreateForUser($user, Streak::TYPE_READING);
            $activityRecorded = $streak->recordActivity();

            if ($activityRecorded) {
                // Send notification if streak reached milestone
                $this->notifyStreakMilestone($user, $streak);

                // Award achievement if applicable
                $this->awardStreakAchievements($user, $streak);

                Log::info('Reading activity recorded', [
                    'user_id' => $user->id,
                    'streak_count' => $streak->current_count,
                ]);

                return [
                    'success' => true,
                    'streak_count' => $streak->current_count,
                    'milestone_reached' => $this->isMilestone($streak->current_count),
                ];
            }

            return [
                'success' => false,
                'message' => 'Already recorded for today',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to record reading activity', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Record writing activity for a user
     * Called when user publishes a new post
     */
    public function recordWriting(User $user, Post $post): array
    {
        try {
            $streak = Streak::getOrCreateForUser($user, Streak::TYPE_WRITING);
            $activityRecorded = $streak->recordActivity();

            if ($activityRecorded) {
                // Send notification if streak reached milestone
                $this->notifyStreakMilestone($user, $streak);

                // Award achievement if applicable
                $this->awardStreakAchievements($user, $streak);

                Log::info('Writing activity recorded', [
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'streak_count' => $streak->current_count,
                ]);

                return [
                    'success' => true,
                    'streak_count' => $streak->current_count,
                    'milestone_reached' => $this->isMilestone($streak->current_count),
                ];
            }

            return [
                'success' => false,
                'message' => 'Already published today',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to record writing activity', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user's streak statistics
     */
    public function getUserStreakStats(User $user): array
    {
        $readingStreak = Streak::byUser($user)->reading()->first();
        $writingStreak = Streak::byUser($user)->writing()->first();

        return [
            'reading' => [
                'current' => $readingStreak?->current_count ?? 0,
                'longest' => $readingStreak?->longest_count ?? 0,
                'status' => $readingStreak?->getStreakStatusAttribute() ?? 'inactive',
                'days_until_broken' => $readingStreak?->getDaysUntilBrokenAttribute(),
                'last_activity' => $readingStreak?->last_activity_date?->diffForHumans(),
            ],
            'writing' => [
                'current' => $writingStreak?->current_count ?? 0,
                'longest' => $writingStreak?->longest_count ?? 0,
                'status' => $writingStreak?->getStreakStatusAttribute() ?? 'inactive',
                'days_until_broken' => $writingStreak?->getDaysUntilBrokenAttribute(),
                'last_activity' => $writingStreak?->last_activity_date?->diffForHumans(),
            ],
        ];
    }

    /**
     * Get leaderboard of users with longest reading streaks
     */
    public function getReadingStreakLeaderboard($limit = 20): array
    {
        return Streak::reading()
            ->active()
            ->orderBy('current_count', 'desc')
            ->limit($limit)
            ->with('user:id,name,username,profile_image_url')
            ->get()
            ->map(fn($streak) => [
                'user' => $streak->user,
                'current_streak' => $streak->current_count,
                'longest_streak' => $streak->longest_count,
                'status' => $streak->getStreakStatusAttribute(),
            ]);
    }

    /**
     * Get leaderboard of users with longest writing streaks
     */
    public function getWritingStreakLeaderboard($limit = 20): array
    {
        return Streak::writing()
            ->active()
            ->orderBy('current_count', 'desc')
            ->limit($limit)
            ->with('user:id,name,username,profile_image_url')
            ->get()
            ->map(fn($streak) => [
                'user' => $streak->user,
                'current_streak' => $streak->current_count,
                'longest_streak' => $streak->longest_count,
                'status' => $streak->getStreakStatusAttribute(),
            ]);
    }

    /**
     * Get users whose streaks are at risk (about to break)
     */
    public function getStreaksAtRisk($limit = 100): array
    {
        return Streak::active()
            ->where('current_count', '>', 0)
            ->where('last_activity_date', '=', now()->subDay()->toDateString())
            ->with('user:id,name,username,email')
            ->get()
            ->map(fn($streak) => [
                'user' => $streak->user,
                'type' => $streak->type,
                'current_streak' => $streak->current_count,
                'status' => $streak->getStreakStatusAttribute(),
            ]);
    }

    /**
     * Send reminder emails to users whose streaks are at risk
     */
    public function sendAtRiskNotifications(): void
    {
        $atRiskStreaks = $this->getStreaksAtRisk();

        foreach ($atRiskStreaks as $streak) {
            try {
                NotificationService::notifyStreakAtRisk(
                    $streak['user'],
                    $streak['type'],
                    $streak['current_streak']
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send at-risk streak notification', [
                    'user_id' => $streak['user']->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    // Private helpers

    private function isMilestone($count): bool
    {
        return in_array($count, [7, 14, 30, 60, 100, 365]);
    }

    private function notifyStreakMilestone(User $user, Streak $streak): void
    {
        if ($this->isMilestone($streak->current_count)) {
            try {
                NotificationService::notifyStreakMilestone(
                    $user,
                    $streak->type,
                    $streak->current_count
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send milestone notification', ['error' => $e->getMessage()]);
            }
        }
    }

    private function awardStreakAchievements(User $user, Streak $streak): void
    {
        $achievementMap = [
            'reading' => [
                7 => 'reading_week_warrior', // 7-day reading streak
                30 => 'reading_monthly_master', // 30-day reading streak
                100 => 'reading_century', // 100-day reading streak
            ],
            'writing' => [
                7 => 'writing_week_warrior', // 7-day writing streak
                30 => 'writing_monthly_master', // 30-day writing streak
                100 => 'writing_century', // 100-day writing streak
            ],
        ];

        $achievements = $achievementMap[$streak->type] ?? [];
        if (isset($achievements[$streak->current_count])) {
            try {
                // Award the achievement (integrate with existing achievement system)
                // This should call the existing achievements system in your platform
                Log::info('Streak achievement earned', [
                    'user_id' => $user->id,
                    'achievement' => $achievements[$streak->current_count],
                    'streak_count' => $streak->current_count,
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to award achievement', ['error' => $e->getMessage()]);
            }
        }
    }
}
