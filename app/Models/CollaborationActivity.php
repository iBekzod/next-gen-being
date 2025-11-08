<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollaborationActivity extends Model
{
    protected $table = 'collaboration_activities';

    protected $fillable = [
        'post_id',
        'user_id',
        'action',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
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
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query)
    {
        return $query->latest('created_at');
    }

    public function scopeForPost($query, int $postId)
    {
        return $query->where('post_id', $postId);
    }

    // Methods
    public static function logActivity(Post $post, User $user, string $action, ?string $description = null, ?array $metadata = null): self
    {
        return self::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function getActionLabel(): string
    {
        return match($this->action) {
            'invited' => 'Invited',
            'joined' => 'Joined',
            'left' => 'Left',
            'role_changed' => 'Role changed',
            'content_edited' => 'Edited content',
            'comment_added' => 'Added comment',
            'comment_resolved' => 'Resolved comment',
            'version_created' => 'Created version',
            'published' => 'Published',
            default => 'Activity',
        };
    }

    public function getActionIcon(): string
    {
        return match($this->action) {
            'invited' => '📧',
            'joined' => '✅',
            'left' => '👋',
            'role_changed' => '🔄',
            'content_edited' => '✏️',
            'comment_added' => '💬',
            'comment_resolved' => '✓',
            'version_created' => '📝',
            'published' => '🚀',
            default => '•',
        };
    }

    public function getFullDescription(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $action = $this->getActionLabel();
        $userName = $this->user->name ?? 'Unknown user';

        return "{$userName} {$action}";
    }
}
