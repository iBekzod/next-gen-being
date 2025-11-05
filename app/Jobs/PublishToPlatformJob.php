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

class PublishToPlatformJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function backoff(): array
    {
        return [180, 900, 3600]; // 3 min, 15 min, 1 hour
    }

    public function __construct(
        public Post $post,
        public SocialMediaAccount $account
    ) {
        $this->onQueue('social');
    }

    public function handle(SocialMediaPublishingService $publishingService): void
    {
        Log::info("Publishing to platform", [
            'post_id' => $this->post->id,
            'platform' => $this->account->platform,
            'account_id' => $this->account->id,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Check if already published
            $existingPost = $this->post->socialMediaPosts()
                ->where('social_media_account_id', $this->account->id)
                ->where('status', 'published')
                ->first();

            if ($existingPost) {
                Log::info("Already published to platform, skipping", [
                    'post_id' => $this->post->id,
                    'platform' => $this->account->platform,
                ]);
                return;
            }

            // Check token validity
            if ($this->account->isTokenExpired()) {
                Log::warning("Token expired, skipping publish", [
                    'post_id' => $this->post->id,
                    'platform' => $this->account->platform,
                    'account_id' => $this->account->id,
                ]);

                // Don't retry if token is expired
                $this->delete();
                return;
            }

            // Publish to platform
            $socialPost = $publishingService->publishToAccount($this->post, $this->account);

            Log::info("Successfully published to platform", [
                'post_id' => $this->post->id,
                'platform' => $this->account->platform,
                'social_post_id' => $socialPost->id,
                'platform_post_url' => $socialPost->platform_post_url,
            ]);

            // Dispatch engagement tracking job (check metrics after 1 hour)
            UpdateEngagementMetricsJob::dispatch($socialPost)
                ->delay(now()->addHour())
                ->onQueue('low-priority');

        } catch (Exception $e) {
            Log::error("Failed to publish to platform", [
                'post_id' => $this->post->id,
                'platform' => $this->account->platform,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        Log::error("Platform publishing job failed permanently", [
            'post_id' => $this->post->id,
            'platform' => $this->account->platform,
            'error' => $exception->getMessage(),
        ]);

        // Mark as failed in database
        $this->post->socialMediaPosts()
            ->where('social_media_account_id', $this->account->id)
            ->where('status', 'processing')
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
    }

    public function tags(): array
    {
        return [
            'publish-platform',
            'post:' . $this->post->id,
            'platform:' . $this->account->platform,
        ];
    }
}
