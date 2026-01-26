<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\TrendingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateFeaturedPostsCommand extends Command
{
    protected $signature = 'content:update-featured {--limit=5 : Maximum number of posts to feature} {--period=7days : Time period for trending (3days, 7days, 14days, 30days)}';

    protected $description = 'Auto-feature trending posts based on engagement (views, likes, comments)';

    public function handle(TrendingService $trendingService): int
    {
        $limit = $this->option('limit');
        $period = $this->option('period');
        $durationDays = config('content.featured_posts.featured_duration_days', 30);

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('🌟 Updating Featured Posts');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        try {
            $this->info("📊 Finding top {$limit} trending posts from {$period}...");
            $this->newLine();

            // Get trending posts
            $trendingPosts = $trendingService->getTrendingPosts($limit, $period);

            if ($trendingPosts->isEmpty()) {
                $this->warn('⚠️  No trending posts found for the specified period');
                return self::SUCCESS;
            }

            // Unmark old featured posts (featured longer than duration_days)
            $this->info("🔄 Removing old featured posts (featured > {$durationDays} days ago)...");
            $unfeatureCount = Post::where('is_featured', true)
                ->where('featured_at', '<', now()->subDays($durationDays))
                ->update([
                    'is_featured' => false,
                    'featured_at' => null,
                ]);

            if ($unfeatureCount > 0) {
                $this->line("   ✓ Removed featured status from {$unfeatureCount} old post(s)");
            } else {
                $this->line('   ✓ No old featured posts to remove');
            }

            $this->newLine();

            // Mark new featured posts
            $this->info("✨ Marking trending posts as featured...");
            $featureCount = 0;

            foreach ($trendingPosts as $post) {
                if (!$post->is_featured) {
                    $post->update([
                        'is_featured' => true,
                        'featured_at' => now(),
                    ]);

                    $trendingScore = round(
                        ($post->likes_count * 5) + ($post->comments_count * 3) + ($post->views_count * 0.5),
                        2
                    );

                    $this->line("   ✅ {$post->title}");
                    $this->line("      Views: {$post->views_count} | Likes: {$post->likes_count} | Comments: {$post->comments_count}");
                    $this->line("      Score: {$trendingScore}");
                    $this->newLine();

                    $featureCount++;
                } else {
                    $this->line("   ⏭️  {$post->title} (already featured)");
                }
            }

            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════');
            $this->info('📋 Summary');
            $this->info('═══════════════════════════════════════════════════════');
            $this->info("Newly featured posts: {$featureCount}");
            $this->info("Previously featured removed: {$unfeatureCount}");
            $this->info("Total featured posts: " . Post::where('is_featured', true)->count());

            Log::info('Featured posts updated successfully', [
                'newly_featured' => $featureCount,
                'removed' => $unfeatureCount,
                'period' => $period,
                'limit' => $limit,
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error updating featured posts: ' . $e->getMessage());
            Log::error('Failed to update featured posts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }
}
