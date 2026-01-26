<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorDailyPublicationCommand extends Command
{
    protected $signature = 'monitor:daily-publication {--refresh=5 : Refresh interval in seconds}';

    protected $description = 'Monitor daily content publication stats and metrics';

    public function handle(): int
    {
        $refreshInterval = (int) $this->option('refresh');

        // Clear screen and show header
        system('clear || cls');

        while (true) {
            $this->displayDashboard();

            if ($refreshInterval > 0) {
                $this->info("⏳ Refreshing in {$refreshInterval} seconds... (Press Ctrl+C to exit)");
                sleep($refreshInterval);
                system('clear || cls');
            } else {
                break;
            }
        }

        return self::SUCCESS;
    }

    private function displayDashboard(): void
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Today's stats
        $todayPosts = Post::where('published_at', '>=', $today)->count();
        $todayOriginal = Post::where('published_at', '>=', $today)
            ->where('is_curated', false)
            ->count();
        $todayAggregated = Post::where('published_at', '>=', $today)
            ->where('is_curated', true)
            ->count();
        $todayPremium = Post::where('published_at', '>=', $today)
            ->where('is_premium', true)
            ->count();
        $todayFree = Post::where('published_at', '>=', $today)
            ->where('is_premium', false)
            ->count();

        // Weekly stats
        $weekPosts = Post::where('published_at', '>=', $thisWeek)->count();
        $weekAggregated = Post::where('published_at', '>=', $thisWeek)
            ->where('is_curated', true)
            ->count();

        // Monthly stats
        $monthPosts = Post::where('published_at', '>=', $thisMonth)->count();
        $monthPremium = Post::where('published_at', '>=', $thisMonth)
            ->where('is_premium', true)
            ->count();

        // Featured posts
        $featuredCount = Post::where('is_featured', true)->count();
        $recentlyFeatured = Post::whereHas('recently_featured')->count();

        // Pending content
        $pendingApproval = Post::where('moderation_status', 'pending')->count();
        $draftPosts = Post::where('status', 'draft')->count();

        // Trending posts
        $trending = Post::published()
            ->selectRaw('*, ((likes_count * 5) + (comments_count * 3) + (views_count * 0.5)) as trending_score')
            ->orderByDesc('trending_score')
            ->limit(5)
            ->get();

        // Display dashboard
        $this->displayHeader();
        $this->displayTodayStats($todayPosts, $todayOriginal, $todayAggregated, $todayPremium, $todayFree);
        $this->displayWeeklyStats($weekPosts, $weekAggregated);
        $this->displayFeaturedStats($featuredCount, $recentlyFeatured);
        $this->displayPendingStats($pendingApproval, $draftPosts);
        $this->displayTrendingPosts($trending);
    }

    private function displayHeader(): void
    {
        $this->info('');
        $this->line('═══════════════════════════════════════════════════════════════════════════════');
        $this->line('   📊 DAILY CONTENT PUBLICATION MONITOR');
        $this->line('═══════════════════════════════════════════════════════════════════════════════');
        $this->info("   Last Updated: " . now()->format('Y-m-d H:i:s'));
        $this->newLine();
    }

    private function displayTodayStats($total, $original, $aggregated, $premium, $free): void
    {
        $this->line('📅 TODAY\'S PUBLICATION STATUS');
        $this->line('───────────────────────────────────────────────────────────────────────────────');
        $this->info("  Total Posts Published:        {$total}");
        $this->info("  ├─ Original AI Posts:         {$original}");
        $this->info("  ├─ Aggregated Posts:          {$aggregated}");
        $this->info("  ├─ Premium Posts:             {$premium}");
        $this->info("  └─ Free Posts:                {$free}");

        if ($total > 0) {
            $premiumPercent = round(($premium / $total) * 100, 1);
            $freePercent = round(($free / $total) * 100, 1);
            $this->info("  Split: {$freePercent}% Free, {$premiumPercent}% Premium");
        }

        $target = config('content.daily_publication.total_posts', 3);
        $status = $total >= $target ? '✅ Target Met' : "❌ Behind (Target: {$target})";
        $this->line("  Status: {$status}");
        $this->newLine();
    }

    private function displayWeeklyStats($total, $aggregated): void
    {
        $this->line('📈 THIS WEEK\'S STATS');
        $this->line('───────────────────────────────────────────────────────────────────────────────');
        $this->info("  Total Posts Published:        {$total}");
        $this->info("  Aggregated Posts:             {$aggregated}");

        $targetWeekly = config('content.daily_publication.total_posts', 3) * 7;
        $status = $total >= $targetWeekly ? '✅ On Track' : "❌ Behind (Target: {$targetWeekly})";
        $this->line("  Status: {$status}");
        $this->newLine();
    }

    private function displayFeaturedStats($featured, $recentlyFeatured): void
    {
        $this->line('⭐ FEATURED POSTS');
        $this->line('───────────────────────────────────────────────────────────────────────────────');
        $this->info("  Currently Featured:           {$featured}");
        $this->info("  Recently Featured (30 days):  {$recentlyFeatured}");

        $max = config('content.featured_posts.max_featured', 5);
        $status = $featured <= $max ? '✅ Within Limit' : "⚠️  Exceeds Limit ({$max})";
        $this->line("  Status: {$status}");
        $this->newLine();
    }

    private function displayPendingStats($pending, $drafts): void
    {
        $this->line('⏳ PENDING CONTENT');
        $this->line('───────────────────────────────────────────────────────────────────────────────');
        $this->info("  Awaiting Moderation:          {$pending}");
        $this->info("  Draft Posts:                  {$drafts}");
        $this->newLine();
    }

    private function displayTrendingPosts($posts): void
    {
        $this->line('🔥 TOP TRENDING POSTS');
        $this->line('───────────────────────────────────────────────────────────────────────────────');

        if ($posts->isEmpty()) {
            $this->warn('  No trending posts yet');
        } else {
            foreach ($posts as $index => $post) {
                $rank = $index + 1;
                $views = $post->views_count ?? 0;
                $likes = $post->likes_count ?? 0;
                $comments = $post->comments_count ?? 0;

                $this->info("  {$rank}. {$post->title}");
                $this->line("     📊 Views: {$views} | ❤️  Likes: {$likes} | 💬 Comments: {$comments}");

                if ($post->is_featured) {
                    $this->line("     ⭐ FEATURED since {$post->featured_at?->format('Y-m-d H:i')}");
                }
            }
        }

        $this->newLine();
    }
}
