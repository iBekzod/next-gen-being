<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'video_type',
        'duration_seconds',
        'resolution',
        'script',
        'voiceover_url',
        'video_clips',
        'captions_url',
        'video_url',
        'thumbnail_url',
        'file_size_mb',
        'ai_credits_used',
        'generation_cost',
        'status',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'video_clips' => 'array',
        'duration_seconds' => 'integer',
        'file_size_mb' => 'decimal:2',
        'ai_credits_used' => 'integer',
        'generation_cost' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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

    // Helper methods
    public function isQueued(): bool
    {
        return $this->status === 'queued';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(string $videoUrl, string $thumbnailUrl, float $fileSizeMb): void
    {
        $this->update([
            'status' => 'completed',
            'video_url' => $videoUrl,
            'thumbnail_url' => $thumbnailUrl,
            'file_size_mb' => $fileSizeMb,
            'completed_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    public function getProcessingTime(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    public function getVideoTypeName(): string
    {
        return match($this->video_type) {
            'youtube' => 'YouTube Video',
            'tiktok' => 'TikTok',
            'reel' => 'Instagram Reel',
            'short' => 'YouTube Short',
            default => ucfirst($this->video_type),
        };
    }

    public function getFormattedDuration(): string
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    // Scopes
    public function scopeQueued($query)
    {
        return $query->where('status', 'queued');
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

    public function scopeVideoType($query, string $type)
    {
        return $query->where('video_type', $type);
    }
}
