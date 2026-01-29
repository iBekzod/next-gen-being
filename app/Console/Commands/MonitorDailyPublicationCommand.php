<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class MonitorDailyPublicationCommand extends Command
{
    protected $signature = 'content:monitor-publication {--refresh=5 : Refresh interval in seconds}';

    protected $description = 'Monitor daily content publication statistics and quotas';

    public function handle(): int
    {
        $refreshInterval = (int) $this->option('refresh');

        do {
            $this->clearScreen();
            $this->displayStats();
            
            if (!$this->confirm('Continue monitoring? (yes/no)', true)) {
                break;
            }

            sleep($refreshInterval);
        } while (true);

        return self::SUCCESS;
    }

    private function displayStats(): void
    {
        $today = now()->startOfDay();
        
        // Today's posts
        $todaysPosts = Post::where('published_at', '>=', $today)
            ->where('status', 'published')
            ->get();

        $todaysOriginal = $todaysPosts->where('is_curated', false)->count();
        $todaysAggregated = $todaysPosts->where('is_curated', true)->count();
        $todaysPremium = $todaysPosts->where('is_premium', true)->count();
        $todaysFree = $todaysPosts->where('is_premium', false)->count();

        // Pending posts
        $draftPosts = Post::where('status', 'draft')->get();
        $pendingAggregated = $draftPosts->where('is_curated', true)
            ->where('moderation_status', 'approved')
            ->count();
        $pendingReview = $draftPosts->where('moderation_status', 'pending')->count();

        // Featured posts
        $featuredPosts = Post::where('is_featured', true)->count();
        $recentlyFeatured = Post::recentlyFeatured()->count();

        // Trending posts
        $thisWeekPosts = Post::where('published_at', '>=', now()->subDays(7))
            ->where('status', 'published')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        // Display stats
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('📊 Daily Publication Monitor');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Today's stats
        $this->info('📅 Today\'s Publication Stats');
        $this->info('─────────────────────────────────────────────────────');
        $this->line("  Total Published: {$todaysPosts->count()}/3 (Target: 3)");
        $this->line("  ├─ Original (AI): {$todaysOriginal}/1");
        $this->line("  └─ Aggregated: {$todaysAggregated}/2");
        $this->newLine();

        $this->line("  Premium/Free Split: {$todaysPremium} premium, {$todaysFree} free");
        if ($todaysPosts->count() > 0) {
            $premiumPercent = round(($todaysPremium / $todaysPosts->count()) * 100, 1);
            $this->line("  └─ Premium: {$premiumPercent}% (Target: ~30%)");
        }
        $this->newLine();

        // Pending posts
        $this->info('⏳ Pending Posts');
        $this->info('─────────────────────────────────────────────────────');
        $this->line("  Approved (Ready to Publish): {$pendingAggregated}");
        $this->line("  Awaiting Review: {$pendingReview}");
        $this->newLine();

        // Featured posts
        $this->info('⭐ Featured Posts');
        $this->info('─────────────────────────────────────────────────────');
        $this->line("  Currently Featured: {$featuredPosts}");
        $this->line("  Featured in Last 30 Days: {$recentlyFeatured}");
        $this->newLine();

        // Trending posts
        if ($thisWeekPosts->isNotEmpty()) {
            $this->info('🔥 Top Trending Posts (Last 7 Days)');
            $this->info('─────────────────────────────────────────────────────');
            foreach ($thisWeekPosts as $index => $post) {
                $score = round(
                    ($post->likes_count * 5) + ($post->comments_count * 3) + ($post->views_count * 0.5),
                    2
                );
                $this->line("  " . ($index + 1) . ". {$post->title}");
                $this->line("     Views: {$post->views_count} | Likes: {$post->likes_count} | Score: {$score}");
            }
            $this->newLine();
        }

        // Status indicator
        $this->info('═══════════════════════════════════════════════════════');
        
        if ($todaysPosts->count() >= 3 && $todaysOriginal >= 1 && $todaysAggregated >= 2) {
            $this->info('✅ Daily quota met for today');
        } elseif ($todaysPosts->count() > 0) {
            $this->warn('⏳ Daily quota in progress');
        } else {
            $this->warn('❌ No posts published today yet');
        }

        $this->newLine();
        $this->info('Last Updated: ' . now()->format('H:i:s'));
        $this->info('═══════════════════════════════════════════════════════');
    }

    private function clearScreen(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            system('cls');
        } else {
            system('clear');
        }
    }
}
