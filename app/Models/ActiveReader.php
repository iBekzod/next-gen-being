<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveReader extends Model
{
    protected $table = 'active_readers';

    protected $fillable = [
        'post_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'started_viewing_at',
        'last_activity_at',
        'left_at',
    ];

    protected $casts = [
        'started_viewing_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('left_at')
                    ->where('last_activity_at', '>', now()->subMinutes(5)); // 5 min inactivity threshold
    }

    public function scopeForPost($query, int $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeAnonymous($query)
    {
        return $query->whereNull('user_id');
    }

    // Methods
    public function isAuthenticated(): bool
    {
        return $this->user_id !== null;
    }

    public function isAnonymous(): bool
    {
        return $this->user_id === null;
    }

    public function isActive(): bool
    {
        return $this->left_at === null && $this->last_activity_at->isAfter(now()->subMinutes(5));
    }

    public function getReadingDurationSeconds(): int
    {
        $endTime = $this->left_at ?? now();
        return (int) $this->started_viewing_at->diffInSeconds($endTime);
    }

    public function getReadingDuration(): string
    {
        $seconds = $this->getReadingDurationSeconds();

        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return "{$minutes}m";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m";
    }

    public function recordActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function markAsLeft(): void
    {
        $this->update(['left_at' => now()]);
    }
}
