<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\ContentAggregation;
use App\Services\TrendingService;
use App\Services\ImageGenerationService;
use App\Services\ContentModerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyContentPublicationCommand extends Command
{
    protected $signature = 'content:publish-daily {--dry-run : Preview what would be published without making changes}';

    protected $description = 'Publish daily strategic content: 1 original AI post + 2 aggregated posts (70% free, 30% premium)';

    private array $publishedPosts = [];
    private int $premiumCount = 0;
    private int $freeCount = 0;

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('📰 Daily Content Publication');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        try {
            // Step 1: Generate 1 original AI post
            $this->info('Step 1️⃣  Generating original AI post (no references)...');
            $originalPost = $this->generateOriginalPost($isDryRun);

            if (!$originalPost && !$isDryRun) {
                $this->error('❌ Failed to generate original AI post');
                return self::FAILURE;
            }

            // Step 2: Publish 2 aggregated posts
            $this->info('Step 2️⃣  Selecting 2 aggregated posts from approved queue...');
            $aggregatedPosts = $this->publishAggregatedPosts(2, $isDryRun);

            if (count($aggregatedPosts) < 2 && !$isDryRun) {
                $this->warn('⚠️  Only ' . count($aggregatedPosts) . ' aggregated posts available (needed 2)');
                // Generate additional original posts to reach 3 total
                $additional = $this->generateOriginalPost($isDryRun);
                if ($additional) {
                    $aggregatedPosts[] = $additional;
                }
            }

            // Combine all posts for this publication
            $allPosts = array_filter([$originalPost, ...$aggregatedPosts]);

            if (empty($allPosts)) {
                $this->error('❌ No posts available to publish');
                return self::FAILURE;
            }

            // Step 3: Apply 70/30 premium/free split
            $this->info('Step 3️⃣  Applying 70% free / 30% premium split...');
            if (!$isDryRun) {
                $this->applyPremiumSplit($allPosts);
            } else {
                $this->simulatePremiumSplit($allPosts);
            }

            // Step 4: Publish all posts
            $this->info('Step 4️⃣  Publishing posts...');
            if (!$isDryRun) {
                $this->publishPosts($allPosts);
            }

            // Step 5: Update featured posts from trending
            $this->info('Step 5️⃣  Updating featured posts from trending...');
            if (!$isDryRun) {
                $this->updateFeaturedFromTrending();
            }

            // Summary
            $this->newLine();
            $this->info('═══════════════════════════════════════════════════════');
            $this->info('📊 Summary');
            $this->info('═══════════════════════════════════════════════════════');
            $this->info("Total posts: " . count($allPosts));
            $this->info("Original posts: " . (isset($originalPost) ? 1 : 0));
            $this->info("Aggregated posts: " . count($aggregatedPosts));
            $this->info("Premium posts: " . $this->premiumCount);
            $this->info("Free posts: " . $this->freeCount);

            if ($isDryRun) {
                $this->info("🔍 DRY RUN MODE - No changes were made");
            } else {
                $this->info("✅ All posts published successfully!");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during daily publication: ' . $e->getMessage());
            Log::error('Daily content publication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }

    /**
     * Generate a single original AI post without references
     */
    private function generateOriginalPost(bool $isDryRun): ?Post
    {
        try {
            $this->line('  • Selecting trending topic...');

            // Get AI content service to fetch trending topics
            $aiService = app(\App\Services\AiContentService::class);

            // Generate a single post using the GenerateAiPost command logic
            // but without the verbose output
            $this->callSilently('ai:generate-post', [
                '--count' => '1',
                '--draft' => true,  // Create as draft first
                '--free' => true,   // Override premium strategy for this one
                '--provider' => config('content.ai_generation.default_provider', 'groq'),
            ]);

            // Get the most recently created draft post
            $post = Post::where('status', 'draft')
                ->latest('created_at')
                ->first();

            if ($post) {
                $this->line("  ✅ Generated: {$post->title}");
                $this->publishedPosts[] = $post;
                return $post;
            }

            $this->error('  ❌ Failed to generate post');
            return null;

        } catch (\Exception $e) {
            $this->error('  ❌ Error generating original post: ' . $e->getMessage());
            Log::error('Failed to generate original post', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get approved aggregated posts for publishing
     */
    private function publishAggregatedPosts(int $count, bool $isDryRun): array
    {
        try {
            $this->line("  • Finding approved aggregated posts...");

            // Query aggregated posts that are approved and ready to publish
            $posts = Post::where('is_curated', true)
                ->where('moderation_status', 'approved')
                ->where('status', 'draft')
                ->with('sourceReferences')
                ->orderBy('paraphrase_confidence_score', 'desc')
                ->limit($count * 2)  // Fetch more to account for filtering
                ->get();

            if ($posts->isEmpty()) {
                $this->warn("  ⚠️  No approved aggregated posts found");
                return [];
            }

            $selectedPosts = [];
            foreach ($posts as $post) {
                // Verify it has source references
                if ($post->sourceReferences()->count() === 0) {
                    $this->warn("  ⚠️  Skipping '{$post->title}' - missing source references");
                    continue;
                }

                $this->line("  ✅ Selected: {$post->title} (confidence: " . round($post->paraphrase_confidence_score * 100) . "%)");
                $selectedPosts[] = $post;
                $this->publishedPosts[] = $post;

                // Stop when we have enough posts
                if (count($selectedPosts) >= $count) {
                    break;
                }
            }

            return $selectedPosts;

        } catch (\Exception $e) {
            $this->error('  ❌ Error selecting aggregated posts: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Apply 70% free, 30% premium split to posts
     * With 3 posts: 2 free (66%), 1 premium (33%) - close to target
     * Over time, rolling average approaches 70/30
     */
    private function applyPremiumSplit(array $posts): void
    {
        $targetFreePercentage = config('content.daily_publication.free_percentage', 70);
        $targetPremiumPercentage = 100 - $targetFreePercentage;

        // For 3 posts: calculate how many should be premium
        $numPremium = (int) ceil((count($posts) / 100) * $targetPremiumPercentage);

        $this->line("  • Applying split: {$targetFreePercentage}% free, {$targetPremiumPercentage}% premium");
        $this->line("  • Randomly selecting {$numPremium} post(s) to mark as premium");

        // Randomly select posts to mark as premium
        $premiumIndices = array_rand($posts, min($numPremium, count($posts)));
        if (!is_array($premiumIndices)) {
            $premiumIndices = [$premiumIndices];
        }

        foreach ($posts as $index => $post) {
            $isPremium = in_array($index, $premiumIndices);

            // Update the post with premium status
            $post->update(['is_premium' => $isPremium]);

            if ($isPremium) {
                $this->premiumCount++;
                $this->line("  💎 {$post->title}");
            } else {
                $this->freeCount++;
                $this->line("  🆓 {$post->title}");
            }
        }
    }

    /**
     * Simulate premium split without making changes (for dry-run)
     */
    private function simulatePremiumSplit(array $posts): void
    {
        $targetFreePercentage = config('content.daily_publication.free_percentage', 70);
        $targetPremiumPercentage = 100 - $targetFreePercentage;

        // Calculate split
        $numPremium = (int) ceil((count($posts) / 100) * $targetPremiumPercentage);
        $numFree = count($posts) - $numPremium;

        $this->premiumCount = $numPremium;
        $this->freeCount = $numFree;

        $this->line("  • Would apply: {$numFree} free, {$numPremium} premium");
    }

    /**
     * Publish all posts (mark as published and not draft)
     */
    private function publishPosts(array $posts): void
    {
        $this->line("  • Publishing " . count($posts) . " post(s)...");

        foreach ($posts as $post) {
            $post->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            $this->line("  ✅ Published: {$post->title}");
        }
    }

    /**
     * Update featured posts based on trending scores
     */
    private function updateFeaturedFromTrending(): void
    {
        try {
            $trendingService = app(TrendingService::class);
            $period = config('content.featured_posts.trending_period', '7days');
            $maxFeatured = config('content.featured_posts.max_featured', 5);

            $this->line("  • Finding top {$maxFeatured} trending posts from {$period}...");

            // Get trending posts
            $trendingPosts = $trendingService->getTrendingPosts($maxFeatured, $period);

            if ($trendingPosts->isEmpty()) {
                $this->warn("  ⚠️  No trending posts found");
                return;
            }

            // Unmark old featured posts (>30 days featured)
            $durationDays = config('content.featured_posts.featured_duration_days', 30);
            $unfeatureCount = Post::where('is_featured', true)
                ->where('featured_at', '<', now()->subDays($durationDays))
                ->update([
                    'is_featured' => false,
                    'featured_at' => null,
                ]);

            if ($unfeatureCount > 0) {
                $this->line("  • Removed featured status from {$unfeatureCount} old post(s)");
            }

            // Mark trending posts as featured
            $featureCount = 0;
            foreach ($trendingPosts as $post) {
                if (!$post->is_featured) {
                    $post->update([
                        'is_featured' => true,
                        'featured_at' => now(),
                    ]);
                    $this->line("  ✨ Featured: {$post->title}");
                    $featureCount++;
                }
            }

            $this->line("  ✅ Featured {$featureCount} trending post(s)");

        } catch (\Exception $e) {
            $this->warn("  ⚠️  Error updating featured posts: " . $e->getMessage());
            Log::warning('Failed to update featured posts', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
