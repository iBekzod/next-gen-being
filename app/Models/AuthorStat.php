<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthorStat extends Model
{
    protected $fillable = [
        'author_id',
        'total_posts',
        'total_views',
        'total_likes',
        'total_comments',
        'total_followers',
        'total_earnings',
        'avg_post_views',
        'engagement_rate',
        'last_post_date',
        'top_topics',
        'monthly_growth',
    ];

    protected $casts = [
        'last_post_date' => 'date',
        'top_topics' => 'array',
        'monthly_growth' => 'array',
    ];

    /**
     * Get the author this stat belongs to
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get total engagement count
     */
    public function getTotalEngagementAttribute(): int
    {
        return $this->total_likes + $this->total_comments;
    }

    /**
     * Get formatted earnings
     */
    public function getFormattedEarningsAttribute(): string
    {
        return '$' . number_format($this->total_earnings / 100, 2);
    }

    /**
     * Scope to get top authors by views
     */
    public function scopeTopByViews($query, $limit = 10)
    {
        return $query->orderByDesc('total_views')->limit($limit);
    }

    /**
     * Scope to get top authors by engagement
     */
    public function scopeTopByEngagement($query, $limit = 10)
    {
        return $query->orderByDesc('engagement_rate')->limit($limit);
    }

    /**
     * Scope to get active authors (posted in last 30 days)
     */
    public function scopeActive($query)
    {
        return $query->where('last_post_date', '>=', now()->subDays(30));
    }
}
