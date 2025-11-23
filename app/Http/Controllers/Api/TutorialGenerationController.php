<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class TutorialGenerationController extends Controller
{
    /**
     * Get scheduled tutorial generation status
     */
    public function status(): JsonResponse
    {
        $lastGeneration = Cache::get('tutorials:last_generation');
        $rotationIndex = Cache::get('tutorials:rotation_index', 0);
        $totalTopics = 10; // Number of topics in rotation

        $draftTutorials = Post::where('status', 'draft')
            ->whereNotNull('series_title')
            ->orderBy('created_at', 'desc')
            ->get();

        $publishedSeries = Post::where('status', 'published')
            ->whereNotNull('series_title')
            ->groupBy('series_title')
            ->selectRaw('series_title, COUNT(*) as parts_published, MAX(published_at) as last_published')
            ->get();

        return response()->json([
            'status' => 'operational',
            'last_generation' => $lastGeneration,
            'rotation' => [
                'current_index' => $rotationIndex,
                'total_topics' => $totalTopics,
                'progress' => round(($rotationIndex / $totalTopics) * 100) . '%',
            ],
            'statistics' => [
                'draft_tutorials' => $draftTutorials->count(),
                'published_series' => $publishedSeries->count(),
                'total_tutorial_parts' => Post::whereNotNull('series_title')->count(),
            ],
            'drafts' => $draftTutorials->map(fn($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'series' => $post->series_title,
                'part' => $post->series_part,
                'created_at' => $post->created_at,
                'url' => route('posts.edit', $post->id),
            ]),
            'schedule' => [
                'frequency' => 'weekly (Monday 9:00 AM)',
                'timezone' => config('app.timezone'),
                'next_run' => $this->calculateNextRun(),
            ],
        ]);
    }

    /**
     * Manually trigger tutorial generation
     */
    public function trigger(Request $request): JsonResponse
    {
        // Authorization check
        if (!auth()->user()?->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $skipCache = $request->boolean('skip_cache', false);

        try {
            $exitCode = Artisan::call('tutorials:scheduled', [
                '--skip-cache' => $skipCache,
            ]);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tutorial generation started successfully',
                    'timestamp' => now(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tutorial generation failed',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error triggering generation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get generation history
     */
    public function history(): JsonResponse
    {
        $history = Post::whereNotNull('series_title')
            ->selectRaw('series_title, COUNT(*) as parts, MAX(created_at) as generated_at')
            ->groupBy('series_title')
            ->orderBy('generated_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($series) => [
                'series' => $series->series_title,
                'parts' => $series->parts,
                'generated_at' => $series->generated_at,
            ]);

        return response()->json($history);
    }

    /**
     * Publish all draft tutorials in a series
     */
    public function publishSeries(Request $request): JsonResponse
    {
        if (!auth()->user()?->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $seriesTitle = $request->input('series_title');

        if (!$seriesTitle) {
            return response()->json(['error' => 'series_title required'], 400);
        }

        $updated = Post::where('series_title', $seriesTitle)
            ->where('status', 'draft')
            ->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "Published {$updated} tutorial parts",
            'updated' => $updated,
        ]);
    }

    /**
     * Get configuration
     */
    public function configuration(): JsonResponse
    {
        return response()->json([
            'schedule' => [
                'enabled' => true,
                'frequency' => 'weekly',
                'day' => 'Monday',
                'time' => '9:00 AM',
                'timezone' => config('app.timezone'),
            ],
            'topics_in_rotation' => [
                'E-Commerce Platform (8 parts)',
                'Advanced REST APIs (8 parts)',
                'Real-Time Applications (5 parts)',
                'Microservices Architecture (8 parts)',
                'Advanced Testing (5 parts)',
                'Security Hardening (5 parts)',
                'Performance Optimization (5 parts)',
                'SaaS Platform (8 parts)',
                'AI Integration (5 parts)',
                'DevOps Mastery (8 parts)',
            ],
            'api_endpoints' => [
                'GET /api/tutorials/status' => 'Current status and statistics',
                'POST /api/tutorials/trigger' => 'Manually trigger generation',
                'GET /api/tutorials/history' => 'Generation history',
                'POST /api/tutorials/publish' => 'Publish a series',
                'GET /api/tutorials/config' => 'Configuration details',
            ],
            'dashboard_url' => '/admin',
            'documentation_url' => '/TUTORIAL_GENERATOR_DOCS.md',
        ]);
    }

    /**
     * Calculate next run time
     */
    private function calculateNextRun(): string
    {
        $now = now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.

        if ($dayOfWeek < 1) {
            // Current week hasn't reached Monday yet
            $nextRun = $now->next('Monday')->setTime(9, 0);
        } elseif ($dayOfWeek === 1 && $now->hour < 9) {
            // Today is Monday and time hasn't passed 9 AM
            $nextRun = $now->setTime(9, 0);
        } else {
            // Next Monday
            $nextRun = $now->next('Monday')->setTime(9, 0);
        }

        return $nextRun->toIso8601String();
    }
}
