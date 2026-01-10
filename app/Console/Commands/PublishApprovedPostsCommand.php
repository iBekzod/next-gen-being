<?php

namespace App\Console\Commands;

use App\Jobs\PublishToSocialMediaJob;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishApprovedPostsCommand extends Command
{
    protected $signature = 'content:publish-approved {--limit=10 : Max posts to publish} {--premium-percent=30 : Percentage of posts to mark as premium}';
    protected $description = 'Publish curated posts approved by admins';

    public function handle()
    {
        $this->info('📰 Publishing approved curated posts...');
        Log::info('PublishApprovedPostsCommand started');

        try {
            $limit = (int) $this->option('limit');
            $premiumPercent = (int) $this->option('premium-percent');

            // Get approved draft curated posts
            $approved = Post::where('is_curated', true)
                ->where('status', 'draft')
                ->where('moderation_status', 'approved')
                ->whereNull('published_at')
                ->latest('created_at')
                ->limit($limit)
                ->get();

            if ($approved->isEmpty()) {
                $this->info('No approved posts to publish');
                Log::info('PublishApprovedPostsCommand: No approved posts available');
                return 0;
            }

            $this->info("Found {$approved->count()} approved posts to publish");

            $count = 0;
            $premiumCount = 0;

            foreach ($approved as $post) {
                try {
                    // Determine if this post should be premium
                    $isPremium = rand(1, 100) <= $premiumPercent;

                    // Update post status and publish
                    $post->update([
                        'status' => 'published',
                        'published_at' => now(),
                        'is_premium' => $isPremium,
                        'premium_tier' => $isPremium ? 'basic' : null, // Minimum tier required
                    ]);

                    $this->line("  ✓ {$post->title} " . ($isPremium ? '(Premium)' : '(Free)'));

                    if ($isPremium) {
                        $premiumCount++;
                    }

                    // Dispatch to social media platforms if user has connected accounts
                    if ($post->author && $post->author->socialMediaAccounts()->where('is_active', true)->exists()) {
                        PublishToSocialMediaJob::dispatch($post)
                            ->onQueue('default')
                            ->delay(now()->addSeconds(rand(5, 15))); // Stagger publishing
                    }

                    $count++;

                } catch (\Exception $e) {
                    $this->error("  ✗ Failed: {$e->getMessage()}");
                    Log::error("Post publishing failed", [
                        'post_id' => $post->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("\n✓ Published {$count} posts ({$premiumCount} premium, " . ($count - $premiumCount) . " free)");
            Log::info('PublishApprovedPostsCommand completed', [
                'published' => $count,
                'premium' => $premiumCount,
                'free' => $count - $premiumCount
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('PublishApprovedPostsCommand failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
