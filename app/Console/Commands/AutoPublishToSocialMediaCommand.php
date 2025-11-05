<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\SocialMedia\SocialMediaPublishingService;
use Illuminate\Console\Command;
use Exception;

class AutoPublishToSocialMediaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social:auto-publish
                            {--post_id= : Specific post ID to publish}
                            {--platform= : Specific platform to publish to}
                            {--dry-run : Show what would be published without actually publishing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-publish approved posts with videos to social media platforms';

    /**
     * Execute the console command.
     */
    public function handle(SocialMediaPublishingService $publishingService): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No posts will be published');
            $this->newLine();
        }

        // Get posts to publish
        $posts = $this->getPostsToPublish();

        if ($posts->isEmpty()) {
            $this->info('✓ No posts ready for auto-publishing');
            return self::SUCCESS;
        }

        $this->info("Found {$posts->count()} post(s) ready for publishing");
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;

        foreach ($posts as $post) {
            $this->info("📝 Processing: {$post->title}");

            if ($dryRun) {
                $this->showDryRunInfo($post, $publishingService);
                continue;
            }

            try {
                // Queue the publishing job for background processing
                \App\Jobs\PublishToSocialMediaJob::dispatch($post);

                $this->line("   ✅ Publishing job queued for background processing");
                $successCount++;

                $this->newLine();

            } catch (Exception $e) {
                $this->error("   ✗ Failed to queue: {$e->getMessage()}");
                $failureCount++;
                $this->newLine();
            }
        }

        // Summary
        $this->newLine();
        $this->info("📊 Publishing Summary:");
        $this->info("   Posts processed: {$posts->count()}");
        $this->info("   Jobs queued: {$successCount}");

        if ($failureCount > 0) {
            $this->warn("   Failed to queue: {$failureCount}");
        }

        $this->newLine();
        $this->info("💡 Monitor job progress at: /admin/job-statuses");

        return self::SUCCESS;
    }

    /**
     * Get posts that are ready to be published
     */
    protected function getPostsToPublish()
    {
        $query = Post::query()
            ->where('status', 'published')
            ->whereNotNull('video_url')
            ->whereDoesntHave('socialMediaPosts', function($q) {
                $q->where('status', 'published');
            });

        // Filter by specific post ID
        if ($postId = $this->option('post_id')) {
            $query->where('id', $postId);
        }

        // Only get recent posts (within last 7 days)
        $query->where('published_at', '>=', now()->subDays(7));

        return $query->get();
    }

    /**
     * Show dry run information
     */
    protected function showDryRunInfo(Post $post, SocialMediaPublishingService $publishingService): void
    {
        // Get user's auto-publish accounts
        $accounts = $post->user->socialMediaAccounts()
            ->where('auto_publish', true)
            ->get();

        $this->line("   Would publish to:");

        foreach ($accounts as $account) {
            $platform = ucfirst($account->platform);
            $username = $account->platform_username;
            $this->line("   → {$platform} (@{$username})");
        }

        // Check Telegram
        if (config('services.telegram.channel_id')) {
            $this->line("   → Telegram (Official Channel)");
        }

        if ($accounts->isEmpty() && !config('services.telegram.channel_id')) {
            $this->warn("   ⚠️  No platforms configured for auto-publish");
        }

        $this->newLine();
    }
}
