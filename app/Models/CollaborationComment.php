<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollaborationComment extends Model
{
    protected $table = 'collaboration_comments';

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'section',
        'line_number',
        'status',
        'resolved_at',
        'resolved_by',
        'parent_comment_id',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
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

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(CollaborationComment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CollaborationComment::class, 'parent_comment_id');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeNeedsDiscussion($query)
    {
        return $query->where('status', 'needs_discussion');
    }

    public function scopeOnSection($query, string $section)
    {
        return $query->where('section', $section);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_comment_id');
    }

    // Methods
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function needsDiscussion(): bool
    {
        return $this->status === 'needs_discussion';
    }

    public function resolve(User $user): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $user->id,
        ]);
    }

    public function markNeedsDiscussion(): void
    {
        $this->update(['status' => 'needs_discussion']);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'resolved_by' => null,
        ]);
    }

    public function getThreadReplies(): array
    {
        return $this->replies()->with('user')->get()->toArray();
    }

    public function getSection(): ?string
    {
        return $this->section;
    }
}
