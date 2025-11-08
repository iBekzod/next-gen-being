<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\AsJson;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'icon',
        'color',
        'order',
        'is_active',
        'requirements',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requirements' => AsJson::class,
    ];

    /**
     * Get users who have earned this badge
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withTimestamps()
            ->withPivot('earned_at')
            ->orderByPivot('earned_at', 'desc');
    }

    /**
     * Scope to active badges only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a user meets the requirements for this badge
     */
    public function meetsRequirements(User $user): bool
    {
        if (!$this->requirements) {
            return true;
        }

        $reputation = $user->reputation;
        if (!$reputation) {
            return false;
        }

        foreach ($this->requirements as $key => $value) {
            $userValue = $reputation->{$key} ?? 0;
            if ($userValue < $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get progress percentage toward earning this badge
     */
    public function getProgressForUser(User $user): int
    {
        if (!$this->requirements) {
            return 100;
        }

        $reputation = $user->reputation;
        if (!$reputation) {
            return 0;
        }

        $total = 0;
        $current = 0;

        foreach ($this->requirements as $key => $requiredValue) {
            $total += $requiredValue;
            $userValue = $reputation->{$key} ?? 0;
            $current += min($userValue, $requiredValue);
        }

        return $total > 0 ? (int) (($current / $total) * 100) : 0;
    }
}
