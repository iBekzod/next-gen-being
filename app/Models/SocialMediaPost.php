<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMediaPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'social_media_account_id',
        'platform',
        'platform_post_id',
        'platform_post_url',
        'content_text',
        'content_media_url',
        'content_type',
        'caption',
        'hashtags',
        'mentions',
        'likes_count',
        'comments_count',
        'shares_count',
        'views_count',
        'status',
        'scheduled_at',
        'published_at',
        'error_message',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'mentions' => 'array',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'views_count' => 'integer',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function socialMediaAccount(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class);
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsPublished(string $platformPostId, string $platformPostUrl): void
    {
        $this->update([
            'status' => 'published',
            'platform_post_id' => $platformPostId,
            'platform_post_url' => $platformPostUrl,
            'published_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function getTotalEngagement(): int
    {
        return $this->likes_count + $this->comments_count + $this->shares_count;
    }

    public function getEngagementRate(): float
    {
        if ($this->views_count === 0) {
            return 0;
        }

        return ($this->getTotalEngagement() / $this->views_count) * 100;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeDueForPublishing($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '<=', now());
    }
}
