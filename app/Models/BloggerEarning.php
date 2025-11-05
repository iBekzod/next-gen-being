<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloggerEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'currency',
        'milestone_value',
        'metadata',
        'status',
        'paid_at',
        'payout_method',
        'payout_reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'milestone_value' => 'integer',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function markAsPaid(string $method, string $reference): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payout_method' => $method,
            'payout_reference' => $reference,
        ]);
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Static helper to create earnings
    public static function createFollowerMilestone(User $user, int $milestone, float $amount): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'follower_milestone',
            'amount' => $amount,
            'milestone_value' => $milestone,
            'status' => 'pending',
        ]);
    }

    public static function createPremiumContentEarning(User $user, Post $post, float $amount): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'premium_content',
            'amount' => $amount,
            'metadata' => [
                'post_id' => $post->id,
                'post_title' => $post->title,
            ],
            'status' => 'pending',
        ]);
    }

    public static function createEngagementBonus(User $user, float $amount, array $metadata = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'engagement_bonus',
            'amount' => $amount,
            'metadata' => $metadata,
            'status' => 'pending',
        ]);
    }
}
