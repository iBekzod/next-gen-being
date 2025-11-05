<?php

namespace App\Jobs;

use App\Models\SocialMediaPost;
use App\Services\SocialMedia\SocialMediaPublishingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateEngagementMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 60;

    public function backoff(): array
    {
        return [300, 1800]; // 5 min, 30 min
    }

    public function __construct(public SocialMediaPost $socialPost)
    {
        $this->onQueue('low-priority');
    }

    public function handle(SocialMediaPublishingService $publishingService): void
    {
        if ($this->socialPost->status !== 'published') {
            return;
        }

        Log::info("Updating engagement metrics", [
            'social_post_id' => $this->socialPost->id,
            'platform' => $this->socialPost->platform,
            'post_id' => $this->socialPost->post_id,
        ]);

        try {
            $publishingService->updateEngagementMetrics($this->socialPost->post);

            Log::info("Engagement metrics updated", [
                'social_post_id' => $this->socialPost->id,
                'views' => $this->socialPost->fresh()->views_count,
                'likes' => $this->socialPost->fresh()->likes_count,
                'comments' => $this->socialPost->fresh()->comments_count,
            ]);

        } catch (Exception $e) {
            Log::warning("Failed to update engagement metrics", [
                'social_post_id' => $this->socialPost->id,
                'error' => $e->getMessage(),
            ]);

            // Don't throw - metrics update is not critical
        }
    }

    public function tags(): array
    {
        return [
            'engagement-metrics',
            'post:' . $this->socialPost->post_id,
            'platform:' . $this->socialPost->platform,
        ];
    }
}
