<?php

namespace App\Console\Commands;

use App\Jobs\GenerateVideoJob;
use App\Jobs\PublishToSocialMediaJob;
use App\Models\VideoGeneration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:process-scheduled
                            {--limit=10 : Maximum number of videos to process}
                            {--priority=all : Process only specific priority (low, normal, high, urgent, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled videos that are ready for generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $priority = $this->option('priority');

        $this->info('🎬 Processing scheduled videos...');

        // Get videos ready to process
        $query = VideoGeneration::readyToProcess();

        // Filter by priority if specified
        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        // Order by priority and scheduled time
        $videos = $query
            ->orderByRaw("CASE
                WHEN priority = 'urgent' THEN 1
                WHEN priority = 'high' THEN 2
                WHEN priority = 'normal' THEN 3
                WHEN priority = 'low' THEN 4
                ELSE 5 END")
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        if ($videos->isEmpty()) {
            $this->info('No scheduled videos ready to process.');
            return Command::SUCCESS;
        }

        $this->info("Found {$videos->count()} videos ready to process.");

        $processed = 0;
        $failed = 0;

        foreach ($videos as $video) {
            try {
                $this->line("Processing video #{$video->id} for post: {$video->post->title}");
                $this->line("  Type: {$video->video_type} | Priority: {$video->priority}");

                // Update status to queued
                $video->update(['status' => 'queued']);

                // Dispatch the video generation job
                GenerateVideoJob::dispatch($video->post, $video->video_type)
                    ->onQueue($video->priority === 'urgent' ? 'high' : 'default');

                // If auto-publish is enabled, schedule the publishing job
                if ($video->auto_publish && !empty($video->publish_platforms)) {
                    $this->line("  Will auto-publish to: " . implode(', ', $video->publish_platforms));

                    // This job will wait for video generation to complete
                    PublishToSocialMediaJob::dispatch($video->post, $video->publish_platforms)
                        ->delay(now()->addMinutes(30)) // Give video generation time to complete
                        ->onQueue('social-media');
                }

                $processed++;
                $this->info("  ✅ Video queued for processing");

            } catch (\Exception $e) {
                $failed++;
                $this->error("  ❌ Failed to process video #{$video->id}: {$e->getMessage()}");

                // Mark as failed
                $video->markAsFailed($e->getMessage());

                Log::error('Failed to process scheduled video', [
                    'video_id' => $video->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Process any failed videos that should be retried
        $this->processFailedVideos();

        $this->newLine();
        $this->info("✨ Processing complete!");
        $this->info("  Processed: {$processed}");
        if ($failed > 0) {
            $this->warn("  Failed: {$failed}");
        }

        return Command::SUCCESS;
    }

    /**
     * Process failed videos that should be retried
     */
    protected function processFailedVideos(): void
    {
        $failedVideos = VideoGeneration::failed()
            ->where('retry_count', '<', 3)
            ->where(function ($query) {
                $query->whereNull('last_retry_at')
                    ->orWhere('last_retry_at', '<', now()->subHours(2));
            })
            ->limit(5)
            ->get();

        if ($failedVideos->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->info("🔄 Retrying {$failedVideos->count()} failed videos...");

        foreach ($failedVideos as $video) {
            if (!$video->shouldRetry()) {
                continue;
            }

            $this->line("  Retrying video #{$video->id} (attempt " . ($video->retry_count + 1) . " of 3)");

            // Reset status and increment retry count
            $video->update(['status' => 'queued']);
            $video->incrementRetryCount();

            // Dispatch the job
            GenerateVideoJob::dispatch($video->post, $video->video_type)
                ->onQueue('default')
                ->delay(now()->addMinutes($video->retry_count * 5)); // Exponential backoff
        }
    }
}
