<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'posts_published',
        'posts_views',
        'posts_likes',
        'posts_comments',
        'posts_shares',
        'followers_gained',
        'followers_lost',
        'tips_received',
        'tips_amount',
        'subscription_revenue',
        'total_engagement',
        'engagement_rate',
        'average_read_time',
        'bounce_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'tips_amount' => 'decimal:2',
        'subscription_revenue' => 'decimal:2',
        'engagement_rate' => 'decimal:4',
        'bounce_rate' => 'decimal:4',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days)->toDateString());
    }

    // Helper methods
    public function getTotalRevenueAttribute(): float
    {
        return (float) ($this->tips_amount + $this->subscription_revenue);
    }

    public function getEngagementScoreAttribute(): float
    {
        return ($this->posts_likes + $this->posts_comments * 2 + $this->posts_shares * 3) / max($this->posts_views, 1);
    }
}
