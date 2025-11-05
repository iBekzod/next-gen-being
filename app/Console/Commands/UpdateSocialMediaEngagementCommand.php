<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\SocialMedia\SocialMediaPublishingService;
use Illuminate\Console\Command;

class UpdateSocialMediaEngagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:update-engagement
                            {--post_id= : Update metrics for specific post only}
                            {--days=7 : How many days back to update (default: 7)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update engagement metrics (views, likes, comments) from social media platforms';

    /**
     * Execute the console command.
     */
    public function handle(SocialMediaPublishingService $publishingService): int
    {
        $days = (int)$this->option('days');

        $this->info("📊 Updating social media engagement metrics...");
        $this->newLine();

        // Get posts with published social media posts
        $posts = $this->getPostsToUpdate($days);

        if ($posts->isEmpty()) {
            $this->info('✓ No posts with social media engagement to update');
            return self::SUCCESS;
        }

        $this->info("Found {$posts->count()} post(s) with social media activity");
        $this->newLine();

        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        $totalViews = 0;
        $totalLikes = 0;
        $totalComments = 0;

        foreach ($posts as $post) {
            try {
                // Get metrics before update
                $beforeSummary = $publishingService->getPublishingSummary($post);

                // Update engagement metrics
                $publishingService->updateEngagementMetrics($post);

                // Get metrics after update
                $afterSummary = $publishingService->getPublishingSummary($post);

                $totalViews += $afterSummary['total_views'];
                $totalLikes += $afterSummary['total_likes'];
                $totalComments += $afterSummary['total_comments'];

                // Show significant changes
                if ($this->output->isVerbose()) {
                    $viewsChange = $afterSummary['total_views'] - $beforeSummary['total_views'];
                    if ($viewsChange > 0) {
                        $this->newLine();
                        $this->line("   {$post->title}: +{$viewsChange} views");
                    }
                }

            } catch (\Exception $e) {
                if ($this->output->isVerbose()) {
                    $this->newLine();
                    $this->error("   Failed to update {$post->title}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display summary
        $this->info("✓ Engagement metrics updated successfully");
        $this->newLine();

        $this->table(
            ['Metric', 'Total'],
            [
                ['Total Views', number_format($totalViews)],
                ['Total Likes', number_format($totalLikes)],
                ['Total Comments', number_format($totalComments)],
                ['Posts Updated', $posts->count()],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Get posts to update
     */
    protected function getPostsToUpdate(int $days)
    {
        $query = Post::query()
            ->whereHas('socialMediaPosts', function($q) {
                $q->where('status', 'published');
            });

        // Filter by specific post ID
        if ($postId = $this->option('post_id')) {
            $query->where('id', $postId);
        } else {
            // Only update recent posts
            $query->where('published_at', '>=', now()->subDays($days));
        }

        return $query->get();
    }
}
