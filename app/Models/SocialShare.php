<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialShare extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'platform',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referrer',
        'ip_address',
        'user_agent',
        'metadata',
        'shared_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'shared_at' => 'datetime',
    ];

    /**
     * Get the user who shared
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that was shared
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Scope: Filter by platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope: Recent shares (last N days)
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('shared_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Viral shares (posts with high share velocity)
     */
    public function scopeViral($query, int $threshold = 50, int $hours = 24)
    {
        return $query->selectRaw('post_id, COUNT(*) as share_count')
            ->where('shared_at', '>=', now()->subHours($hours))
            ->groupBy('post_id')
            ->having('share_count', '>=', $threshold)
            ->orderBy('share_count', 'desc');
    }

    /**
     * Get share velocity (shares per hour)
     */
    public static function getShareVelocity(int $postId, int $hours = 24): float
    {
        $shareCount = static::where('post_id', $postId)
            ->where('shared_at', '>=', now()->subHours($hours))
            ->count();

        return $shareCount / $hours;
    }

    /**
     * Get platform breakdown for a post
     */
    public static function getPlatformBreakdown(int $postId): array
    {
        return static::where('post_id', $postId)
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();
    }

    /**
     * Get top shared posts
     */
    public static function getTopSharedPosts(int $limit = 10, int $days = 30)
    {
        return static::selectRaw('post_id, COUNT(*) as share_count')
            ->where('shared_at', '>=', now()->subDays($days))
            ->groupBy('post_id')
            ->orderBy('share_count', 'desc')
            ->limit($limit)
            ->with('post')
            ->get();
    }
}
