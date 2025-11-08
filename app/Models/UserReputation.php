<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReputation extends Model
{
    protected $table = 'user_reputation';

    protected $fillable = [
        'user_id',
        'points',
        'posts_published',
        'posts_liked',
        'comments_received',
        'followers_count',
        'engagement_score',
        'level',
        'level_progress',
    ];

    protected $casts = [
        'points' => 'integer',
        'posts_published' => 'integer',
        'posts_liked' => 'integer',
        'comments_received' => 'integer',
        'followers_count' => 'integer',
        'engagement_score' => 'integer',
        'level_progress' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get reputation level configuration
     */
    public static function getLevelConfig(): array
    {
        return [
            'beginner' => ['min' => 0, 'max' => 100, 'color' => 'gray'],
            'intermediate' => ['min' => 100, 'max' => 500, 'color' => 'blue'],
            'advanced' => ['min' => 500, 'max' => 1500, 'color' => 'purple'],
            'expert' => ['min' => 1500, 'max' => 5000, 'color' => 'orange'],
            'legend' => ['min' => 5000, 'max' => null, 'color' => 'red'],
        ];
    }

    /**
     * Calculate and update user's reputation level
     */
    public function updateLevel(): void
    {
        $config = self::getLevelConfig();
        $prevLevel = $this->level;
        $newLevel = 'beginner';

        foreach ($config as $level => $range) {
            if ($this->points >= $range['min'] && ($range['max'] === null || $this->points < $range['max'])) {
                $newLevel = $level;
                $levelRange = $range['max'] - $range['min'];
                $progress = $levelRange > 0
                    ? (($this->points - $range['min']) / $levelRange) * 100
                    : 100;
                $this->level_progress = (int) min($progress, 100);
                break;
            }
        }

        $this->level = $newLevel;
        $this->save();

        // Trigger level up event if level changed
        if ($prevLevel !== $newLevel) {
            $this->user->notifyLevelUp($newLevel);
        }
    }

    /**
     * Add points to user's reputation
     */
    public function addPoints(int $points): void
    {
        $this->points += $points;
        $this->updateLevel();
    }

    /**
     * Update engagement score (based on recent activity)
     */
    public function updateEngagementScore(): void
    {
        // Score is based on recent interactions (last 30 days)
        $recentLikes = $this->user->posts()
            ->where('created_at', '>=', now()->subDays(30))
            ->withCount('likes')
            ->sum('likes_count');

        $recentComments = $this->user->comments()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $this->engagement_score = ($recentLikes * 2) + $recentComments;
        $this->save();
    }

    /**
     * Get badge earning progress
     */
    public function getBadgeProgress(): array
    {
        $badges = Badge::active()->get();
        $userBadges = $this->user->badges()->pluck('badge_id')->toArray();

        return $badges->map(function ($badge) use ($userBadges) {
            return [
                'badge' => $badge,
                'earned' => in_array($badge->id, $userBadges),
                'progress' => $badge->getProgressForUser($this->user),
                'requirements' => $badge->requirements,
            ];
        })->toArray();
    }
}
