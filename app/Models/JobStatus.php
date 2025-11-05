<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JobStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'type',
        'queue',
        'status',
        'user_id',
        'trackable_type',
        'trackable_id',
        'progress',
        'progress_message',
        'metadata',
        'attempts',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'progress' => 'integer',
        'attempts' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $metadata = []): void
    {
        $this->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], $metadata),
        ]);
    }

    public function markAsFailed(string $errorMessage, int $attempts = 1): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'attempts' => $attempts,
            'completed_at' => now(),
        ]);
    }

    public function updateProgress(int $progress, ?string $message = null): void
    {
        $data = ['progress' => min(100, max(0, $progress))];

        if ($message) {
            $data['progress_message'] = $message;
        }

        $this->update($data);
    }

    public function getDurationInSeconds(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    public function getFormattedDuration(): ?string
    {
        $seconds = $this->getDurationInSeconds();

        if ($seconds === null) {
            return null;
        }

        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return $minutes . 'm ' . $remainingSeconds . 's';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForUser(int $userId)
    {
        return $this->where('user_id', $userId);
    }

    public function scopeOfType(string $type)
    {
        return $this->where('type', $type);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
