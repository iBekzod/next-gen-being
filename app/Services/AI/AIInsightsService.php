<?php

namespace App\Services\AI;

use App\Models\LearningPath;
use App\Models\TutorialProgress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AIInsightsService
{
    /**
     * Get comprehensive learning insights for a user
     */
    public function getUserInsights(User $user): array
    {
        $progress = TutorialProgress::forUser($user->id)->get();

        return [
            'learning_streak' => $this->calculateLearningStreak($user),
            'productivity_score' => $this->calculateProductivityScore($progress),
            'consistency_score' => $this->calculateConsistencyScore($user),
            'learning_velocity' => $this->calculateLearningVelocity($progress),
            'skill_progression' => $this->analyzeSkillProgression($progress),
            'learning_patterns' => $this->identifyLearningPatterns($progress),
            'recommendations_summary' => $this->getRecommendationsSummary($user),
            'next_milestones' => $this->getNextMilestones($user),
        ];
    }

    /**
     * Calculate learning streak (consecutive days of activity)
     */
    public function calculateLearningStreak(User $user): array
    {
        $progress = TutorialProgress::forUser($user->id)
            ->where('completed', true)
            ->orderByDesc('completed_at')
            ->get();

        if ($progress->isEmpty()) {
            return ['current' => 0, 'best' => 0, 'status' => 'No streak yet'];
        }

        $currentStreak = 1;
        $bestStreak = 1;
        $tempStreak = 1;
        $today = Carbon::now()->startOfDay();

        $dates = $progress->map(fn($p) => $p->completed_at->startOfDay())
            ->unique()
            ->sortByDesc(fn($d) => $d->timestamp)
            ->values();

        // Check if streak includes today or yesterday
        $isCurrentlyActive = $dates->first()?->diffInDays($today) <= 1;

        for ($i = 0; $i < count($dates) - 1; $i++) {
            if ($dates[$i]->diffInDays($dates[$i + 1]) === 1) {
                $tempStreak++;
                if ($tempStreak > $bestStreak) {
                    $bestStreak = $tempStreak;
                }
            } else {
                $tempStreak = 1;
            }
        }

        $currentStreak = $isCurrentlyActive ? $dates->count() : 0;

        return [
            'current' => $currentStreak,
            'best' => $bestStreak,
            'status' => match(true) {
                $currentStreak >= 30 => '🔥 On Fire!',
                $currentStreak >= 14 => '✨ Excellent',
                $currentStreak >= 7 => '⭐ Great',
                $currentStreak >= 1 => '👍 Keep Going',
                default => '⏸️ Ready to Start',
            },
        ];
    }

    /**
     * Calculate productivity score (0-100)
     */
    public function calculateProductivityScore(Collection $progress): int
    {
        if ($progress->isEmpty()) {
            return 0;
        }

        $completionRate = ($progress->where('completed', true)->count() / $progress->count()) * 40;
        $consistencyBonus = min($progress->groupBy(function ($item) {
            return $item->completed_at?->format('Y-m-d') ?? 'none';
        })->count() / 10 * 30, 30);
        $timeSpentBonus = min($progress->sum('time_spent_minutes') / 600 * 30, 30);

        return intval(min($completionRate + $consistencyBonus + $timeSpentBonus, 100));
    }

    /**
     * Calculate consistency score (0-100)
     */
    public function calculateConsistencyScore(User $user): int
    {
        $lastThirtyDays = TutorialProgress::forUser($user->id)
            ->where('completed', true)
            ->where('completed_at', '>=', Carbon::now()->subDays(30))
            ->get();

        if ($lastThirtyDays->isEmpty()) {
            return 0;
        }

        $activeDays = $lastThirtyDays->groupBy(function ($item) {
            return $item->completed_at->format('Y-m-d');
        })->count();

        $consistency = ($activeDays / 30) * 100;

        return intval(min($consistency, 100));
    }

    /**
     * Calculate learning velocity (parts completed per week)
     */
    public function calculateLearningVelocity(Collection $progress): array
    {
        $lastWeek = $progress->where('completed', true)
            ->where('completed_at', '>=', Carbon::now()->subWeek())
            ->count();

        $lastMonth = $progress->where('completed', true)
            ->where('completed_at', '>=', Carbon::now()->subMonth())
            ->count();

        $trend = $lastWeek >= 5 ? 'accelerating' : ($lastWeek >= 2 ? 'stable' : 'needs_boost');

        return [
            'this_week' => $lastWeek,
            'this_month' => $lastMonth,
            'average_per_week' => round($lastMonth / 4, 1),
            'trend' => $trend,
        ];
    }

    /**
     * Analyze skill progression over time
     */
    public function analyzeSkillProgression(Collection $progress): array
    {
        $byDifficulty = $progress->where('completed', true)
            ->groupBy('post.difficulty')
            ->map(fn($items) => $items->count());

        $skillLevels = ['beginner' => 0, 'intermediate' => 0, 'advanced' => 0, 'expert' => 0];
        foreach ($byDifficulty as $level => $count) {
            if (array_key_exists($level, $skillLevels)) {
                $skillLevels[$level] = $count;
            }
        }

        $currentLevel = match(true) {
            $skillLevels['expert'] > 0 => 'Expert',
            $skillLevels['advanced'] >= 5 => 'Advanced',
            $skillLevels['intermediate'] >= 5 => 'Intermediate',
            $skillLevels['beginner'] > 0 => 'Beginner',
            default => 'Getting Started',
        };

        return [
            'distribution' => $skillLevels,
            'current_level' => $currentLevel,
            'progression_path' => array_keys(array_filter($skillLevels)),
        ];
    }

    /**
     * Identify learning patterns
     */
    public function identifyLearningPatterns(Collection $progress): array
    {
        // Get hour of day distribution
        $hourDistribution = $progress->groupBy(function ($item) {
            return $item->completed_at?->hour ?? 0;
        })->map(fn($items) => $items->count());

        $preferredHour = $hourDistribution->keys()->max();
        $peakTime = match($preferredHour) {
            0, 1, 2, 3, 4, 5 => 'Early Morning',
            6, 7, 8, 9 => 'Morning',
            10, 11 => 'Late Morning',
            12, 13 => 'Afternoon',
            14, 15, 16, 17 => 'Afternoon',
            18, 19, 20 => 'Evening',
            default => 'Night',
        };

        // Get day of week distribution
        $dayDistribution = $progress->groupBy(function ($item) {
            return $item->completed_at?->format('l') ?? 'Unknown';
        })->map(fn($items) => $items->count());

        $preferredDay = $dayDistribution->keys()->first();
        $avgSessionDuration = $progress->avg('time_spent_minutes') ?? 0;

        return [
            'preferred_time_of_day' => $peakTime,
            'preferred_day' => $preferredDay,
            'average_session_duration_minutes' => round($avgSessionDuration, 1),
            'optimal_study_time' => $this->getOptimalStudyTime($progress),
        ];
    }

    /**
     * Get optimal study time recommendation
     */
    protected function getOptimalStudyTime(Collection $progress): string
    {
        $hourDistribution = $progress->groupBy(function ($item) {
            return $item->completed_at?->hour ?? 0;
        })->map(fn($items) => $items->count());

        if ($hourDistribution->isEmpty()) {
            return 'Try studying in the morning or evening';
        }

        $maxHour = $hourDistribution->keys()->max();
        $count = $hourDistribution->max();

        return "You're most productive around " . ($maxHour % 12 ?: 12) . ":00 " . ($maxHour < 12 ? 'AM' : 'PM') . " ({$count} sessions)";
    }

    /**
     * Get recommendations summary
     */
    public function getRecommendationsSummary(User $user): array
    {
        $recommendations = $user->aiRecommendations()
            ->active()
            ->count();

        $actedOn = $user->aiRecommendations()
            ->actedOn()
            ->count();

        $engagement = $recommendations > 0 ? round(($actedOn / $recommendations) * 100, 1) : 0;

        return [
            'total_active' => $recommendations,
            'acted_on' => $actedOn,
            'engagement_rate' => $engagement . '%',
        ];
    }

    /**
     * Get next milestones
     */
    public function getNextMilestones(User $user): array
    {
        $progress = TutorialProgress::forUser($user->id)->get();
        $completedCount = $progress->where('completed', true)->count();

        $milestones = [];

        $targets = [5 => '5 Parts', 10 => '10 Parts', 25 => '25 Parts', 50 => '50 Parts', 100 => '100 Parts'];

        foreach ($targets as $target => $label) {
            if ($completedCount < $target) {
                $remaining = $target - $completedCount;
                $milestones[] = [
                    'name' => $label,
                    'target' => $target,
                    'completed' => $completedCount,
                    'remaining' => $remaining,
                    'progress' => round(($completedCount / $target) * 100, 1),
                ];
            }
        }

        return $milestones;
    }

    /**
     * Get series analysis
     */
    public function getSeriesAnalysis(User $user): Collection
    {
        $progress = TutorialProgress::forUser($user->id)->get();

        return $progress->groupBy('series_slug')
            ->map(function ($series, $slug) {
                $completed = $series->where('completed', true)->count();
                $total = $series->count();

                return [
                    'series' => $slug,
                    'completed' => $completed,
                    'total' => $total,
                    'percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                    'status' => $completed === $total && $total > 0 ? 'Completed' : 'In Progress',
                ];
            })
            ->sortByDesc('percentage');
    }

    /**
     * Get learning path analysis
     */
    public function getLearningPathAnalysis(User $user): array
    {
        $activePath = $user->learningPaths()->where('status', 'active')->latest()->first();
        $completedPaths = $user->learningPaths()->where('status', 'completed')->count();
        $totalPaths = $user->learningPaths()->count();

        return [
            'active_path' => $activePath ? [
                'name' => $activePath->name,
                'progress' => $activePath->getProgressPercentage(),
                'items_completed' => $activePath->getCompletedItemsCount(),
                'total_items' => $activePath->getTotalItemsCount(),
            ] : null,
            'completed_paths' => $completedPaths,
            'total_paths' => $totalPaths,
        ];
    }
}
