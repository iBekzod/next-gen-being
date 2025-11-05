<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Video\VideoGenerationService;
use Illuminate\Console\Command;
use Exception;

class GenerateVideoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:generate
                            {post_id : The ID of the post to convert to video}
                            {type=tiktok : Video type (youtube, tiktok, reel, short)}
                            {--queue : Queue the video generation job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a video from a blog post using AI';

    /**
     * Execute the console command.
     */
    public function handle(VideoGenerationService $videoService): int
    {
        $postId = $this->argument('post_id');
        $type = $this->argument('type');
        $queue = $this->option('queue');

        // Validate video type
        $validTypes = ['youtube', 'tiktok', 'reel', 'short'];
        if (!in_array($type, $validTypes)) {
            $this->error("Invalid video type. Must be one of: " . implode(', ', $validTypes));
            return self::FAILURE;
        }

        // Find the post
        $post = Post::find($postId);
        if (!$post) {
            $this->error("Post with ID {$postId} not found.");
            return self::FAILURE;
        }

        // Check if post is published
        if ($post->status !== 'published') {
            $this->error("Post must be published before generating video.");
            return self::FAILURE;
        }

        // Check user's video tier
        $user = $post->user;
        if (!$user->canGenerateVideo()) {
            $this->error("User has reached their monthly video generation limit.");
            $this->info("Current tier: {$user->video_tier}");
            $this->info("Videos generated this month: {$user->videos_generated}");
            return self::FAILURE;
        }

        $this->info("Generating {$type} video for post: {$post->title}");
        $this->newLine();

        if ($queue) {
            // Queue the job for background processing
            $this->info("Queuing video generation job...");

            \App\Jobs\GenerateVideoJob::dispatch($post, $type, $user->id);

            $this->info("✅ Video generation job queued successfully!");
            $this->info("Monitor progress at: /admin/job-statuses");
            $this->newLine();

            return self::SUCCESS;
        }

        try {
            // Step 1: Script Generation
            $this->info("📝 Step 1/5: Generating video script...");
            $bar = $this->output->createProgressBar(5);
            $bar->start();

            $videoGeneration = $videoService->generateFromPost($post, $type);

            $bar->advance();
            $this->newLine();
            $this->info("✅ Script generated: " . strlen($videoGeneration->script) . " characters");
            $this->newLine();

            // Step 2: Voiceover Generation
            $this->info("🎙️ Step 2/5: Generating voiceover...");
            $bar->advance();
            $this->newLine();
            $this->info("✅ Voiceover generated");
            $this->newLine();

            // Step 3: Stock Footage
            $this->info("🎬 Step 3/5: Fetching stock footage...");
            $bar->advance();
            $this->newLine();
            $this->info("✅ Stock footage retrieved: " . count($videoGeneration->video_clips ?? []) . " clips");
            $this->newLine();

            // Step 4: Caption Generation
            $this->info("💬 Step 4/5: Generating captions...");
            $bar->advance();
            $this->newLine();
            $this->info("✅ Captions generated");
            $this->newLine();

            // Step 5: Video Assembly
            $this->info("🎞️ Step 5/5: Assembling final video...");
            $bar->advance();
            $bar->finish();
            $this->newLine(2);

            // Display results
            $this->info("✨ Video generation complete!");
            $this->newLine();

            $this->table(
                ['Property', 'Value'],
                [
                    ['Video ID', $videoGeneration->id],
                    ['Type', strtoupper($videoGeneration->video_type)],
                    ['Duration', $videoGeneration->getFormattedDuration()],
                    ['Status', strtoupper($videoGeneration->status)],
                    ['Video URL', $videoGeneration->video_url ?? 'N/A'],
                    ['Thumbnail URL', $videoGeneration->thumbnail_url ?? 'N/A'],
                    ['File Size', $videoGeneration->file_size_mb ? $videoGeneration->file_size_mb . ' MB' : 'N/A'],
                    ['Cost', '$' . number_format($videoGeneration->generation_cost, 2)],
                ]
            );

            $this->newLine();
            $this->info("📊 User Stats:");
            $this->info("   Videos generated this month: " . ($user->videos_generated + 1));
            $this->info("   Remaining this month: " . ($user->monthly_video_limit - $user->videos_generated - 1 ?: 'unlimited'));

            return self::SUCCESS;

        } catch (Exception $e) {
            $this->newLine(2);
            $this->error("❌ Video generation failed: " . $e->getMessage());
            $this->newLine();

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            } else {
                $this->info("Run with -v for detailed error information");
            }

            return self::FAILURE;
        }
    }
}
