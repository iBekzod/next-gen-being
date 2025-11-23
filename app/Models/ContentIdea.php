<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentIdea extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'topic',
        'content_type',
        'target_audience',
        'keywords',
        'outline',
        'status',
        'source',
        'trending_score',
        'difficulty_score',
        'estimated_read_time',
        'priority',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'keywords' => 'array',
        'outline' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scopes
     */
    public function scopeByCreator($query, User $creator)
    {
        return $query->where('user_id', $creator->id);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'in_progress']);
    }

    public function scopeTrending($query)
    {
        return $query->orderByDesc('trending_score');
    }

    public function scopeByTopic($query, string $topic)
    {
        return $query->where('topic', $topic);
    }

    public function scopeByContentType($query, string $type)
    {
        return $query->where('content_type', $type);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Helper Methods
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'in_progress']);
    }

    public function markAsStarted(): void
    {
        $this->update(['status' => 'in_progress']);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsArchived(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Get difficulty level description
     */
    public function getDifficultyLevelAttribute(): string
    {
        if ($this->difficulty_score < 30) {
            return 'Easy';
        } elseif ($this->difficulty_score < 60) {
            return 'Medium';
        } else {
            return 'Hard';
        }
    }

    /**
     * Get trending status
     */
    public function isTrending(): bool
    {
        return $this->trending_score >= 70;
    }

    /**
     * Get estimated word count based on type
     */
    public function getEstimatedWordCountAttribute(): int
    {
        return match ($this->content_type) {
            'short_post' => 300,
            'medium_post' => 800,
            'long_form' => 2000,
            'tutorial' => 3000,
            'case_study' => 2500,
            'news' => 500,
            default => 1000,
        };
    }
}
