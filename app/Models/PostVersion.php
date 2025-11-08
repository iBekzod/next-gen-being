<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostVersion extends Model
{
    public $timestamps = false;

    protected $table = 'post_versions';

    protected $fillable = [
        'post_id',
        'edited_by',
        'title',
        'content',
        'content_json',
        'change_summary',
        'change_type',
        'changes_metadata',
        'created_at',
    ];

    protected $casts = [
        'changes_metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('change_type', $type);
    }

    public function scopeAutoSaves($query)
    {
        return $query->where('change_type', 'auto_save');
    }

    public function scopeManualSaves($query)
    {
        return $query->where('change_type', 'manual_save');
    }

    public function scopePublished($query)
    {
        return $query->where('change_type', 'published');
    }

    public function scopeRecent($query)
    {
        return $query->latest('created_at');
    }

    // Methods
    public function isAutoSave(): bool
    {
        return $this->change_type === 'auto_save';
    }

    public function isManualSave(): bool
    {
        return $this->change_type === 'manual_save';
    }

    public function isPublished(): bool
    {
        return $this->change_type === 'published';
    }

    public function getChanges(): array
    {
        return $this->changes_metadata ?? [];
    }

    public function getChangeSummary(): string
    {
        if ($this->change_summary) {
            return $this->change_summary;
        }

        return match($this->change_type) {
            'auto_save' => 'Auto-saved',
            'manual_save' => 'Manually saved',
            'published' => 'Published',
            'scheduled' => 'Scheduled',
            default => 'Saved',
        };
    }

    /**
     * Restore this version as the current post content
     */
    public function restore(): void
    {
        $this->post->update([
            'title' => $this->title,
            'content' => $this->content,
            'content_json' => $this->content_json,
        ]);
    }

    /**
     * Compare this version with another version
     */
    public function compareTo(PostVersion $other): array
    {
        return [
            'title_changed' => $this->title !== $other->title,
            'content_changed' => $this->content !== $other->content,
            'title_diff' => [
                'from' => $other->title,
                'to' => $this->title,
            ],
            'change_summary' => $this->change_summary,
        ];
    }
}
