<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MonitorTutorials extends Command
{
    protected $signature = 'tutorials:monitor {--refresh=5}';
    protected $description = 'Monitor tutorial generation system in real-time';

    public function handle(): int
    {
        $refresh = (int) $this->option('refresh');

        while (true) {
            $this->displayStatus();
            sleep($refresh);
        }
    }

    private function displayStatus(): void
    {
        // Clear screen (works on most terminals)
        system('clear') || system('cls');

        $this->info("📊 Tutorial Generation Monitor");
        $this->info(str_repeat("=", 70) . "\n");

        // Display timestamp
        $this->line("Last Updated: " . now()->format('Y-m-d H:i:s'));

        // Generation Statistics
        $this->displayStatistics();

        // Rotation Status
        $this->displayRotation();

        // Recent Activity
        $this->displayRecentActivity();

        // Scheduled Info
        $this->displayScheduleInfo();

        // Queue Status
        $this->displayQueueStatus();

        $this->info("\n" . str_repeat("=", 70));
        $this->line("Refreshing in 5 seconds... (Ctrl+C to exit)\n");
    }

    private function displayStatistics(): void
    {
        $this->info("\n📈 Statistics:\n");

        $totalPosts = Post::count();
        $draftPosts = Post::where('status', 'draft')->count();
        $publishedPosts = Post::where('status', 'published')->count();

        $totalSeries = Post::whereNotNull('series_slug')
            ->distinct('series_slug')
            ->count();

        $totalParts = Post::whereNotNull('series_slug')->count();

        $avgViewsPerPart = Post::whereNotNull('series_slug')
            ->avg('views_count') ?? 0;

        $this->line("  Total Posts:        {$totalPosts}");
        $this->line("  Draft Posts:        {$draftPosts}");
        $this->line("  Published Posts:    {$publishedPosts}");
        $this->line("  Series Count:       {$totalSeries}");
        $this->line("  Total Parts:        {$totalParts}");
        $this->line("  Avg Views/Part:     " . round($avgViewsPerPart, 1));
    }

    private function displayRotation(): void
    {
        $this->info("\n🔄 Topic Rotation:\n");

        $topics = [
            'E-Commerce Platform (8 parts)',
            'REST APIs (8 parts)',
            'Real-Time Apps (5 parts)',
            'Microservices (8 parts)',
            'Testing Strategy (5 parts)',
            'Security Hardening (5 parts)',
            'Performance Optimization (5 parts)',
            'SaaS Platform (8 parts)',
            'AI Integration (5 parts)',
            'DevOps Mastery (8 parts)',
        ];

        $currentIndex = Cache::get('tutorials:rotation_index', 0);
        $currentIndex = $currentIndex % count($topics);

        $progress = round(($currentIndex / count($topics)) * 100);

        foreach ($topics as $index => $topic) {
            if ($index === $currentIndex) {
                $this->line("  ➤ [{$index}] {$topic} ← NEXT");
            } else {
                $marker = $index < $currentIndex ? '✓' : '-';
                $this->line("  {$marker} [{$index}] {$topic}");
            }
        }

        $this->line("\n  Progress: {$progress}% (" . ($currentIndex + 1) . "/" . count($topics) . ")\n");
    }

    private function displayRecentActivity(): void
    {
        $this->info("📝 Recent Activity (Last 5 Series):\n");

        $recentSeries = Post::whereNotNull('series_slug')
            ->select('series_slug', 'series_title', 'created_at', 'status')
            ->distinct('series_slug')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($recentSeries->isEmpty()) {
            $this->line("  No series generated yet\n");
        } else {
            foreach ($recentSeries as $series) {
                $date = $series->created_at->format('Y-m-d H:i');
                $status = $series->status === 'published' ? '📌' : '📋';

                $postCount = Post::where('series_slug', $series->series_slug)->count();

                $this->line("  {$status} [{$date}] {$series->series_title} ({$postCount} parts)");
            }
        }

        $this->line("");
    }

    private function displayScheduleInfo(): void
    {
        $this->info("⏱️  Schedule Information:\n");

        $lastGeneration = Post::whereNotNull('series_slug')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastGeneration) {
            $lastDate = $lastGeneration->created_at->format('Y-m-d H:i:s');
            $this->line("  Last Generation:    {$lastDate}");
        } else {
            $this->line("  Last Generation:    Never");
        }

        $this->line("  Frequency:          Weekly (Monday 9:00 AM)");
        $this->line("  Timezone:           UTC");

        // Calculate next Monday 9 AM
        $next = now()->next(\Carbon\Carbon::MONDAY)->setTime(9, 0);
        if (now()->isMonday() && now()->hour >= 9) {
            $next = $next->addWeek();
        }

        $nextDate = $next->format('Y-m-d H:i:s');
        $this->line("  Next Scheduled:     {$nextDate}");

        $this->line("");
    }

    private function displayQueueStatus(): void
    {
        $this->info("⚙️  Queue Status:\n");

        try {
            // Try to count failed jobs
            $failedCount = DB::table('failed_jobs')->count();
            $this->line("  Failed Jobs:        {$failedCount}");
        } catch (\Exception $e) {
            $this->line("  Failed Jobs:        N/A (table not found)");
        }

        // Show queue driver
        $queueDriver = config('queue.default');
        $this->line("  Queue Driver:       {$queueDriver}");

        if ($queueDriver === 'sync') {
            $this->line("  ⚠️  WARNING: Using SYNC queue (not recommended for production)");
        }

        $this->line("");
    }
}
