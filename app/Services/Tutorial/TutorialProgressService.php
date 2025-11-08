<?php

namespace App\Services\Tutorial;

use App\Models\Achievement;
use App\Models\Post;
use App\Models\TutorialProgress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TutorialProgressService
{
    /**
     * Track when a user reads a tutorial part
     */
    public function trackReading(User $user, Post $post): TutorialProgress
    {
        $progress = TutorialProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ],
            [
                'series_slug' => $post->series_slug,
                'series_part' => $post->series_part ?? 0,
            ]
        );

        $progress->markAsRead();

        // Check for achievements after reading
        $this->checkAndAwardAchievements($user);

        return $progress;
    }

    /**
     * Mark a tutorial part as completed
     */
    public function markAsCompleted(User $user, Post $post): TutorialProgress
    {
        $progress = TutorialProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ],
            [
                'series_slug' => $post->series_slug,
                'series_part' => $post->series_part ?? 0,
            ]
        );

        $progress->markAsCompleted();

        // Check for achievements after completing
        $this->checkAndAwardAchievements($user);

        return $progress;
    }

    /**
     * Get user's progress in a series
     */
    public function getSeriesProgress(User $user, string $seriesSlug): array
    {
        $progress = TutorialProgress::forUser($user->id)
            ->inSeries($seriesSlug)
            ->get();

        $completedCount = $progress->filter(fn($p) => $p->completed)->count();
        $totalCount = $progress->count();

        return [
            'completed' => $completedCount,
            'total' => $totalCount,
            'percentage' => $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0,
            'is_complete' => $completedCount === $totalCount && $totalCount > 0,
        ];
    }

    /**
     * Get all user statistics
     */
    public function getUserStats(User $user): array
    {
        $progress = TutorialProgress::forUser($user->id)->get();

        $completedParts = $progress->filter(fn($p) => $p->completed)->count();
        $totalParts = $progress->count();
        $totalTimeMinutes = $progress->sum('time_spent_minutes');
        $totalSeries = $progress->groupBy('series_slug')->count();
        $completedSeries = collect();

        // Count completed series
        foreach ($progress->groupBy('series_slug') as $seriesSlug => $seriesParts) {
            $seriesCompleted = $seriesParts->filter(fn($p) => $p->completed)->count();
            $seriesTotal = $seriesParts->count();
            if ($seriesCompleted === $seriesTotal && $seriesTotal > 0) {
                $completedSeries->push($seriesSlug);
            }
        }

        $totalAchievements = $user->achievements->count();
        $totalPoints = $user->achievements->sum('points');

        return [
            'total_parts_read' => $totalParts,
            'total_parts_completed' => $completedParts,
            'completion_percentage' => $totalParts > 0 ? ($completedParts / $totalParts) * 100 : 0,
            'total_hours_spent' => round($totalTimeMinutes / 60, 1),
            'total_minutes_spent' => $totalTimeMinutes,
            'total_series_started' => $totalSeries,
            'total_series_completed' => $completedSeries->count(),
            'total_achievements' => $totalAchievements,
            'total_points' => $totalPoints,
            'recent_activity' => $progress->sortByDesc('completed_at')->take(5),
        ];
    }

    /**
     * Check and award achievements to a user
     */
    public function checkAndAwardAchievements(User $user): Collection
        {
        $awardedAchievements = collect();
        $stats = $this->getUserStats($user);

        // Achievement conditions to check
        $achievements = Achievement::all();

        foreach ($achievements as $achievement) {
            // Skip if already earned
            if ($user->achievements->contains($achievement)) {
                continue;
            }

            $earned = false;
            $conditions = $achievement->conditions;

            // Check different achievement conditions
            if (isset($conditions['completed_parts']) && $stats['total_parts_completed'] >= $conditions['completed_parts']) {
                $earned = true;
            } elseif (isset($conditions['completed_series']) && $stats['total_series_completed'] >= $conditions['completed_series']) {
                $earned = true;
            } elseif (isset($conditions['total_hours']) && $stats['total_hours_spent'] >= $conditions['total_hours']) {
                $earned = true;
            } elseif (isset($conditions['parts_same_day'])) {
                // Check if user completed X parts in same day
                $today = Carbon::now()->startOfDay();
                $todayCompleted = TutorialProgress::forUser($user->id)
                    ->where('completed', true)
                    ->whereDate('completed_at', $today)
                    ->count();

                if ($todayCompleted >= $conditions['parts_same_day']) {
                    $earned = true;
                }
            }

            if ($earned) {
                $user->achievements()->attach($achievement, ['earned_at' => now()]);
                $awardedAchievements->push($achievement);
            }
        }

        return $awardedAchievements;
    }

    /**
     * Get user's leaderboard position
     */
    public function getUserLeaderboardPosition(User $user): ?int
    {
        $userPoints = $user->achievements->sum('points');

        $position = User::whereHas('achievements')
            ->selectRaw('users.*, SUM(achievements.points) as total_points')
            ->join('user_achievements', 'users.id', '=', 'user_achievements.user_id')
            ->join('achievements', 'achievements.id', '=', 'user_achievements.achievement_id')
            ->groupBy('users.id')
            ->havingRaw('SUM(achievements.points) > ?', [$userPoints])
            ->count() + 1;

        return $position;
    }

    /**
     * Get top learners
     */
    public function getTopLearners(int $limit = 10): Collection
    {
        return User::whereHas('achievements')
            ->selectRaw('users.*, COUNT(user_achievements.id) as achievement_count, SUM(achievements.points) as total_points')
            ->join('user_achievements', 'users.id', '=', 'user_achievements.user_id')
            ->join('achievements', 'achievements.id', '=', 'user_achievements.achievement_id')
            ->groupBy('users.id')
            ->orderByDesc('total_points')
            ->take($limit)
            ->get();
    }
}
