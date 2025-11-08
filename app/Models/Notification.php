<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Casts\AsJson;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'title',
        'message',
        'action_url',
        'data',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'data' => AsJson::class,
    ];

    /**
     * Get the user that this notification belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who triggered this notification
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the notifiable model (Post, Comment, etc.)
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return !$this->isRead();
    }

    /**
     * Get unread count for user
     */
    public static function unreadCountFor(User $user): int
    {
        return static::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get recent unread notifications
     */
    public static function getUnreadFor(User $user, int $limit = 10)
    {
        return static::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
