<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\SocialMedia\TelegramPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class PublishToTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    public function backoff(): array
    {
        return [60, 300, 900]; // 1 min, 5 min, 15 min
    }

    public function __construct(public Post $post)
    {
        $this->onQueue('social');
    }

    public function handle(TelegramPublisher $telegramPublisher): void
    {
        $channelId = config('services.telegram.channel_id');

        if (!$channelId) {
            Log::info("Telegram channel ID not configured, skipping");
            return;
        }

        Log::info("Publishing to Telegram", [
            'post_id' => $this->post->id,
            'channel_id' => $channelId,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Check if already published
            $existingPost = $this->post->socialMediaPosts()
                ->where('platform', 'telegram')
                ->where('status', 'published')
                ->first();

            if ($existingPost) {
                Log::info("Already published to Telegram, skipping", [
                    'post_id' => $this->post->id,
                ]);
                return;
            }

            $socialPost = $telegramPublisher->publish($this->post, $channelId);

            Log::info("Successfully published to Telegram", [
                'post_id' => $this->post->id,
                'message_id' => $socialPost->platform_post_id,
                'message_url' => $socialPost->platform_post_url,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to publish to Telegram", [
                'post_id' => $this->post->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Exception $exception): void
    {
        Log::error("Telegram publishing job failed permanently", [
            'post_id' => $this->post->id,
            'error' => $exception->getMessage(),
        ]);
    }

    public function tags(): array
    {
        return [
            'telegram',
            'post:' . $this->post->id,
        ];
    }
}
