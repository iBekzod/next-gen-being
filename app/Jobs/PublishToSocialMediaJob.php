<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Services\SocialMedia\SocialMediaPublishingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class PublishToSocialMediaJob implements ShouldQueue
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
    public $timeout = 300; // 5 minutes

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [120, 600, 1800]; // 2 min, 10 min, 30 min
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post,
        public ?array $platformFilters = null
    ) {
        $this->onQueue('social');
    }

    /**
     * Execute the job.
     */
    public function handle(SocialMediaPublishingService $publishingService): void
    {
        Log::info("Starting social media publishing job", [
            'post_id' => $this->post->id,
            'platforms' => $this->platformFilters,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Verify post has video
            if (!$this->post->hasVideo()) {
                Log::warning("Post has no video, skipping social media publish", [
                    'post_id' => $this->post->id,
                ]);
                return;
            }

            // Get accounts to publish to
            $accounts = $this->getAccountsToPublish();

            if ($accounts->isEmpty()) {
                Log::info("No accounts configured for auto-publish", [
                    'post_id' => $this->post->id,
                ]);
                return;
            }

            $successCount = 0;
            $failureCount = 0;

            // Publish to each platform
            foreach ($accounts as $account) {
                try {
                    // Dispatch individual platform job
                    PublishToPlatformJob::dispatch($this->post, $account)
                        ->onQueue('social')
                        ->delay($this->calculateDelay($account->platform));

                    $successCount++;

                } catch (Exception $e) {
                    Log::error("Failed to dispatch platform job", [
                        'post_id' => $this->post->id,
                        'platform' => $account->platform,
                        'error' => $e->getMessage(),
                    ]);
                    $failureCount++;
                }
            }

            // Publish to Telegram if configured
            try {
                if (config('services.telegram.channel_id')) {
                    PublishToTelegramJob::dispatch($this->post)
                        ->onQueue('social')
                        ->delay(now()->addSeconds(30));
                    $successCount++;
                }
            } catch (Exception $e) {
                Log::error("Failed to dispatch Telegram job", [
                    'post_id' => $this->post->id,
                    'error' => $e->getMessage(),
                ]);
                $failureCount++;
            }

            Log::info("Social media publishing jobs dispatched", [
                'post_id' => $this->post->id,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
            ]);

        } catch (Exception $e) {
            Log::error("Social media publishing job failed", [
                'post_id' => $this->post->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get accounts that should receive this post
     */
    protected function getAccountsToPublish()
    {
        $query = $this->post->user->socialMediaAccounts()
            ->where('auto_publish', true);

        // Apply platform filters if specified
        if ($this->platformFilters) {
            $query->whereIn('platform', $this->platformFilters);
        }

        return $query->get();
    }

    /**
     * Calculate delay based on platform rate limits
     */
    protected function calculateDelay(string $platform): \DateTimeInterface
    {
        // Stagger requests to avoid rate limits
        $delays = [
            'youtube' => 30,    // 30 seconds
            'instagram' => 60,  // 1 minute
            'twitter' => 15,    // 15 seconds
            'facebook' => 30,   // 30 seconds
            'linkedin' => 45,   // 45 seconds
        ];

        $seconds = $delays[$platform] ?? 0;
        return now()->addSeconds($seconds);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Social media publishing job failed permanently", [
            'post_id' => $this->post->id,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'social-media',
            'post:' . $this->post->id,
        ];
    }
}
