<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'progress',
        'is_completed',
        'completed_at',
        'reward_claimed',
        'claimed_at',
        'metadata',
    ];

    protected $casts = [
        'progress' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'reward_claimed' => 'boolean',
        'claimed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeRewardClaimed($query)
    {
        return $query->where('reward_claimed', true);
    }

    public function scopeByChallenge($query, Challenge $challenge)
    {
        return $query->where('challenge_id', $challenge->id);
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    // Helper methods
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->challenge || $this->challenge->target_value === 0) {
            return 0;
        }

        return min(($this->progress / $this->challenge->target_value) * 100, 100);
    }

    public function incrementProgress($amount = 1): void
    {
        $this->progress += $amount;

        // Check if challenge is completed
        if ($this->progress >= $this->challenge->target_value) {
            $this->is_completed = true;
            $this->completed_at = now();
        }

        $this->save();
    }

    public function claimReward(): bool
    {
        if (!$this->is_completed || $this->reward_claimed) {
            return false;
        }

        $this->reward_claimed = true;
        $this->claimed_at = now();
        $this->save();

        return true;
    }

    public static function getOrCreate(Challenge $challenge, User $user): self
    {
        return self::firstOrCreate(
            [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
            ],
            [
                'progress' => 0,
                'is_completed' => false,
                'reward_claimed' => false,
            ]
        );
    }
}
