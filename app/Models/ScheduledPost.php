<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledPost extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'title',
        'content',
        'excerpt',
        'featured_image_url',
        'status',
        'scheduled_for',
        'published_at',
        'category_id',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'published_at' => 'datetime',
        'tags' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the author
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the published post (if published)
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scopes
     */
    public function scopeByAuthor($query, User $author)
    {
        return $query->where('user_id', $author->id);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->scheduled()
            ->where('scheduled_for', '>', now())
            ->where('scheduled_for', '<=', now()->addDays($days));
    }

    public function scopeOverdue($query)
    {
        return $query->scheduled()
            ->where('scheduled_for', '<=', now());
    }

    /**
     * Helper Methods
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isOverdue(): bool
    {
        return $this->isScheduled() && $this->scheduled_for <= now();
    }

    public function publish(): bool
    {
        if ($this->post_id) {
            return false; // Already published
        }

        try {
            // Create the post from scheduled post data
            $post = Post::create([
                'user_id' => $this->user_id,
                'title' => $this->title,
                'content' => $this->content,
                'excerpt' => $this->excerpt,
                'featured_image_url' => $this->featured_image_url,
                'category_id' => $this->category_id,
                'published_at' => now(),
            ]);

            if ($this->tags) {
                $post->syncTags($this->tags);
            }

            $this->update([
                'post_id' => $post->id,
                'status' => 'published',
                'published_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->update([
                'status' => 'failed',
                'metadata' => array_merge($this->metadata ?? [], [
                    'error' => $e->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);

            return false;
        }
    }

    public function reschedule($newDateTime): bool
    {
        if (!$this->isScheduled()) {
            return false;
        }

        $this->update(['scheduled_for' => $newDateTime]);
        return true;
    }

    /**
     * Get word count
     */
    public function getWordCountAttribute(): int
    {
        return str_word_count(strip_tags($this->content));
    }

    /**
     * Estimate read time (200 words per minute)
     */
    public function getEstimatedReadTimeAttribute(): int
    {
        return ceil($this->word_count / 200);
    }
}
