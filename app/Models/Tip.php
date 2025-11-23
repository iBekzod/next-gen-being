<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tip extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'post_id',
        'amount',
        'currency',
        'message',
        'stripe_payment_intent_id',
        'status',
        'is_anonymous',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    // Relationships
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByRecipient($query, User $user)
    {
        return $query->where('to_user_id', $user->id);
    }

    public function scopeByPost($query, Post $post)
    {
        return $query->where('post_id', $post->id);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeForLeaderboard($query, $days = 30)
    {
        return $query
            ->completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByRaw('SUM(amount) DESC')
            ->groupBy('to_user_id');
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    public function refund(): void
    {
        $this->update(['status' => self::STATUS_REFUNDED]);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->is_anonymous
            ? 'Anonymous Supporter'
            : ($this->fromUser->name ?? 'Unknown');
    }

    // Get total tips for a user
    public static function totalTipsForUser(User $user, $days = null): float
    {
        $query = self::byRecipient($user)->completed();

        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        return (float) $query->sum('amount');
    }

    // Get tip count for a post
    public static function countForPost(Post $post): int
    {
        return self::byPost($post)->completed()->count();
    }

    // Get total tips on a post
    public static function totalForPost(Post $post): float
    {
        return (float) self::byPost($post)->completed()->sum('amount');
    }
}
