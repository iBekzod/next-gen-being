<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\VideoGeneration;
use App\Services\Video\VideoGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1 min, 5 min, 15 min
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post,
        public string $type,
        public ?int $userId = null
    ) {
        $this->onQueue('video');
    }

    /**
     * Execute the job.
     */
    public function handle(VideoGenerationService $videoService): void
    {
        Log::info("Starting video generation job", [
            'post_id' => $this->post->id,
            'type' => $this->type,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Check if video generation already exists and is complete
            $existingVideo = VideoGeneration::where('post_id', $this->post->id)
                ->where('video_type', $this->type)
                ->where('status', 'completed')
                ->first();

            if ($existingVideo) {
                Log::info("Video already generated, skipping", [
                    'post_id' => $this->post->id,
                    'video_id' => $existingVideo->id,
                ]);
                return;
            }

            // Generate video
            $videoGeneration = $videoService->generateFromPost($this->post, $this->type);

            Log::info("Video generation completed successfully", [
                'post_id' => $this->post->id,
                'video_id' => $videoGeneration->id,
                'video_url' => $videoGeneration->video_url,
                'cost' => $videoGeneration->generation_cost,
            ]);

            // Dispatch social media publishing job if auto-publish is enabled
            if ($this->shouldAutoPublish()) {
                PublishToSocialMediaJob::dispatch($this->post)
                    ->delay(now()->addMinutes(2)); // Small delay to ensure video is fully processed
            }

        } catch (Exception $e) {
            Log::error("Video generation job failed", [
                'post_id' => $this->post->id,
                'type' => $this->type,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark video generation as failed
            VideoGeneration::where('post_id', $this->post->id)
                ->where('video_type', $this->type)
                ->where('status', 'processing')
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Video generation job failed permanently", [
            'post_id' => $this->post->id,
            'type' => $this->type,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        // Notify user (could send email, push notification, etc.)
        // NotifyUserOfFailedVideoGeneration::dispatch($this->post, $this->type, $exception->getMessage());
    }

    /**
     * Check if video should be auto-published after generation
     */
    protected function shouldAutoPublish(): bool
    {
        // Check if user has auto-publish enabled on any platform
        return $this->post->user->socialMediaAccounts()
            ->where('auto_publish', true)
            ->exists();
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'video-generation',
            'post:' . $this->post->id,
            'type:' . $this->type,
        ];
    }
}
