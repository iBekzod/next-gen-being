<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostAnalytic extends Model
{
    protected $fillable = [
        'post_id',
        'date',
        'views',
        'likes',
        'comments',
        'shares',
        'unique_readers',
        'avg_read_time',
        'scroll_depth',
        'traffic_sources',
        'top_referrers',
        'device_breakdown',
        'geo_data',
    ];

    protected $casts = [
        'date' => 'date',
        'traffic_sources' => 'array',
        'top_referrers' => 'array',
        'device_breakdown' => 'array',
        'geo_data' => 'array',
    ];

    /**
     * Get the post this analytic belongs to
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get engagement rate for this day
     */
    public function getEngagementRateAttribute(): float
    {
        if ($this->views == 0) {
            return 0;
        }
        $engagement = $this->likes + $this->comments;
        return round(($engagement / $this->views) * 100, 2);
    }

    /**
     * Scope to get analytics for last N days
     */
    public function scopeLastDays($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    /**
     * Scope to get analytics for a specific month
     */
    public function scopeForMonth($query, $month, $year = null)
    {
        $year = $year ?? now()->year;
        return $query->whereMonth('date', $month)
            ->whereYear('date', $year);
    }
}
