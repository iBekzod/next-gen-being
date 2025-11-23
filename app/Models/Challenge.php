<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type', // 'reading', 'writing', 'engagement', 'community'
        'target_value', // e.g., "read 10 posts", "publish 7 articles"
        'reward_points',
        'reward_description',
        'starts_at',
        'ends_at',
        'icon',
        'difficulty', // 'easy', 'medium', 'hard'
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'reward_points' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function participants(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeEnded($query)
    {
        return $query->where('ends_at', '<', now());
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active && $this->starts_at <= now() && $this->ends_at >= now();
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->isActive()) {
            return null;
        }

        return now()->diffInDays($this->ends_at);
    }

    public function getProgressPercentageAttribute(): float
    {
        $totalParticipants = $this->participants()->count();
        $completedParticipants = $this->participants()->where('is_completed', true)->count();

        if ($totalParticipants === 0) {
            return 0;
        }

        return ($completedParticipants / $totalParticipants) * 100;
    }
}
