<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Streak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // 'reading' or 'writing'
        'current_count',
        'longest_count',
        'last_activity_date',
        'broken_at',
        'metadata',
    ];

    protected $casts = [
        'current_count' => 'integer',
        'longest_count' => 'integer',
        'last_activity_date' => 'date',
        'broken_at' => 'datetime',
        'metadata' => 'array',
    ];

    const TYPE_READING = 'reading';
    const TYPE_WRITING = 'writing';

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeReading($query)
    {
        return $query->where('type', self::TYPE_READING);
    }

    public function scopeWriting($query)
    {
        return $query->where('type', self::TYPE_WRITING);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('broken_at');
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    // Helper methods
    public function isActive(): bool
    {
        return is_null($this->broken_at);
    }

    public function recordActivity(): bool
    {
        $today = now()->toDateString();

        // If activity was already recorded today, don't increment
        if ($this->last_activity_date && $this->last_activity_date->toDateString() === $today) {
            return false;
        }

        // Check if yesterday was the last activity (maintain streak)
        $yesterday = now()->subDay()->toDateString();
        if (!$this->last_activity_date || $this->last_activity_date->toDateString() !== $yesterday) {
            // Streak is broken, reset
            if ($this->current_count > 0) {
                $this->broken_at = now();
            }
            $this->current_count = 1;
        } else {
            // Continue streak
            $this->current_count += 1;

            // Update longest count if current exceeds it
            if ($this->current_count > $this->longest_count) {
                $this->longest_count = $this->current_count;
            }
        }

        $this->last_activity_date = $today;
        $this->save();

        return true;
    }

    public function breakStreak(): void
    {
        if ($this->isActive()) {
            $this->broken_at = now();
            $this->save();
        }
    }

    public function reset(): void
    {
        $this->current_count = 0;
        $this->last_activity_date = null;
        $this->broken_at = null;
        $this->save();
    }

    public function getDaysUntilBrokenAttribute(): ?int
    {
        if (!$this->isActive()) {
            return null;
        }

        $lastActivity = $this->last_activity_date;
        $daysWithoutActivity = now()->diffInDays($lastActivity);

        // If no activity today and last activity was yesterday, streak is still safe
        if ($daysWithoutActivity <= 1) {
            return 2 - $daysWithoutActivity; // Days remaining
        }

        return null;
    }

    public function getStreakStatusAttribute(): string
    {
        if (!$this->isActive()) {
            return 'broken';
        }

        if ($this->getDaysUntilBrokenAttribute() === 1) {
            return 'at_risk';
        }

        return 'active';
    }

    // Static helpers
    public static function getOrCreateForUser(User $user, string $type): self
    {
        return self::firstOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            [
                'current_count' => 0,
                'longest_count' => 0,
                'last_activity_date' => null,
            ]
        );
    }

    public static function recordReadingActivity(User $user): bool
    {
        $streak = self::getOrCreateForUser($user, self::TYPE_READING);
        return $streak->recordActivity();
    }

    public static function recordWritingActivity(User $user): bool
    {
        $streak = self::getOrCreateForUser($user, self::TYPE_WRITING);
        return $streak->recordActivity();
    }
}
