<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ContentView;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Evaluates achievement criteria stored in achievements.requirements JSON
 * and grants any unearned achievements to a user.
 *
 * requirements shape: ['type' => 'posts_read|series_completed|comments_posted|bookmarks|newsletter|profile_complete|premium|joined_year|streak', 'value' => int]
 */
class AchievementService
{
    /**
     * Check ALL achievements for a user and award any newly-earned ones.
     * Returns array of newly-awarded Achievement objects.
     */
    public function evaluateAll(User $user): array
    {
        // Cheap cache: skip re-eval within 5 min
        $key = "ach:eval:{$user->id}";
        if (Cache::has($key)) return [];
        Cache::put($key, true, 300);

        $stats = $this->collectUserStats($user);
        $earnedIds = $user->achievements()->pluck('achievements.id')->all();
        $awarded = [];

        foreach (Achievement::where('is_active', true)->get() as $a) {
            if (in_array($a->id, $earnedIds, true)) continue;
            if ($this->satisfies($a->requirements ?? [], $stats)) {
                $user->achievements()->attach($a->id, ['achieved_at' => now()]);
                $awarded[] = $a;
            }
        }
        return $awarded;
    }

    protected function collectUserStats(User $user): array
    {
        return [
            'posts_read' => ContentView::where('user_id', $user->id)->distinct('post_id')->count('post_id'),
            'series_completed' => DB::table('tutorial_progress')
                ->where('user_id', $user->id)
                ->where('completed', true)
                ->select('series_slug')->distinct()->count('series_slug'),
            'comments_posted' => DB::table('comments')->where('user_id', $user->id)->count(),
            'bookmarks' => DB::table('post_interactions')
                ->where('user_id', $user->id)->where('type', 'bookmark')->count(),
            'newsletter' => (int) DB::table('newsletter_subscriptions')
                ->where('user_id', $user->id)->where('is_active', true)->exists(),
            'profile_complete' => (int) (!empty($user->bio) && !empty($user->avatar)),
            'premium' => (int) (method_exists($user, 'subscription') && $user->subscription() && $user->subscription()->valid()),
            'joined_year' => (int) ($user->created_at?->year ?? 0),
            'streak' => $this->currentReadingStreak($user),
        ];
    }

    protected function currentReadingStreak(User $user): int
    {
        $days = ContentView::where('user_id', $user->id)
            ->where('viewed_at', '>=', now()->subDays(60))
            ->selectRaw('DATE(viewed_at) as d')
            ->distinct()
            ->orderByDesc('d')
            ->pluck('d')
            ->map(fn($d) => (string) $d)
            ->all();

        if (empty($days)) return 0;
        $streak = 0;
        $cursor = now()->startOfDay();
        foreach ($days as $d) {
            if ($d === $cursor->toDateString()) {
                $streak++;
                $cursor = $cursor->subDay();
            } elseif ($d === $cursor->subDay()->toDateString()) {
                // gap of one — broken
                break;
            } else {
                break;
            }
        }
        return $streak;
    }

    protected function satisfies(array $req, array $stats): bool
    {
        if (empty($req['type']) || !isset($req['value'])) return false;
        $type = $req['type'];
        $value = (int) $req['value'];
        if ($type === 'joined_year') {
            return ($stats['joined_year'] ?? 0) <= $value;
        }
        return (int) ($stats[$type] ?? 0) >= $value;
    }
}
