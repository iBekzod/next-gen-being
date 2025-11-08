<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsJson;

class UserAchievement extends Model
{
    protected $fillable = [
        'user_id',
        'achievement_code',
        'description',
        'metadata',
    ];

    protected $casts = [
        'achieved_at' => 'datetime',
        'metadata' => AsJson::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get achievement list with descriptions
     */
    public static function getAchievements(): array
    {
        return [
            'first_post' => [
                'title' => 'First Post',
                'description' => 'Published your first blog post',
                'icon' => '✍️',
            ],
            'first_like_received' => [
                'title' => 'Loved',
                'description' => 'Your post received its first like',
                'icon' => '❤️',
            ],
            'ten_posts' => [
                'title' => 'Prolific Writer',
                'description' => 'Published 10 blog posts',
                'icon' => '📚',
            ],
            'hundred_followers' => [
                'title' => 'Popular',
                'description' => 'Reached 100 followers',
                'icon' => '👥',
            ],
            'thousand_views' => [
                'title' => 'Viral',
                'description' => 'Your posts received 1,000 views',
                'icon' => '🚀',
            ],
            'five_hundred_followers' => [
                'title' => 'Influencer',
                'description' => 'Reached 500 followers',
                'icon' => '⭐',
            ],
            'one_thousand_followers' => [
                'title' => 'Celebrity',
                'description' => 'Reached 1,000 followers',
                'icon' => '🌟',
            ],
            'helpful_commenter' => [
                'title' => 'Helpful',
                'description' => 'Your comment received 10 likes',
                'icon' => '💬',
            ],
            'consistent_contributor' => [
                'title' => 'Consistent',
                'description' => 'Posted every week for a month',
                'icon' => '📅',
            ],
            'knowledge_sharer' => [
                'title' => 'Knowledge Sharer',
                'description' => 'One of your posts reached 500 views',
                'icon' => '🎓',
            ],
        ];
    }

    /**
     * Check if achievement should be unlocked
     */
    public static function checkAndUnlock(User $user): void
    {
        $stats = [
            'posts' => $user->posts()->count(),
            'followers' => $user->followers()->count(),
            'totalViews' => $user->posts()->withCount('views')->sum('views_count'),
            'topCommentLikes' => $user->comments()->withCount('likes')->max('likes_count') ?? 0,
        ];

        $achievements = self::getAchievements();

        // First post
        if ($stats['posts'] === 1 && !$user->achievements()->where('achievement_code', 'first_post')->exists()) {
            self::unlock($user, 'first_post');
        }

        // First like received
        if ($stats['posts'] > 0 && !$user->achievements()->where('achievement_code', 'first_like_received')->exists()) {
            if ($user->posts()->withCount('likes')->sum('likes_count') > 0) {
                self::unlock($user, 'first_like_received');
            }
        }

        // Ten posts
        if ($stats['posts'] >= 10 && !$user->achievements()->where('achievement_code', 'ten_posts')->exists()) {
            self::unlock($user, 'ten_posts');
        }

        // 100 followers
        if ($stats['followers'] >= 100 && !$user->achievements()->where('achievement_code', 'hundred_followers')->exists()) {
            self::unlock($user, 'hundred_followers');
        }

        // 1000 views
        if ($stats['totalViews'] >= 1000 && !$user->achievements()->where('achievement_code', 'thousand_views')->exists()) {
            self::unlock($user, 'thousand_views');
        }

        // 500 followers
        if ($stats['followers'] >= 500 && !$user->achievements()->where('achievement_code', 'five_hundred_followers')->exists()) {
            self::unlock($user, 'five_hundred_followers');
        }

        // 1000 followers
        if ($stats['followers'] >= 1000 && !$user->achievements()->where('achievement_code', 'one_thousand_followers')->exists()) {
            self::unlock($user, 'one_thousand_followers');
        }

        // Helpful commenter
        if ($stats['topCommentLikes'] >= 10 && !$user->achievements()->where('achievement_code', 'helpful_commenter')->exists()) {
            self::unlock($user, 'helpful_commenter');
        }
    }

    /**
     * Unlock achievement for user
     */
    public static function unlock(User $user, string $code): void
    {
        $achievements = self::getAchievements();

        if (isset($achievements[$code])) {
            $achievement = $achievements[$code];
            $user->achievements()->create([
                'achievement_code' => $code,
                'description' => $achievement['description'],
            ]);

            // Add reputation points
            if ($user->reputation) {
                $user->reputation->addPoints(10);
            }
        }
    }
}
