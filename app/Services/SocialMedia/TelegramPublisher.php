<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Exception;

class TelegramPublisher
{
    /**
     * Publish video to Telegram channel
     *
     * @param Post $post
     * @param string $channelId Telegram channel ID (e.g., @channelname or -100123456789)
     * @return SocialMediaPost
     */
    public function publish(Post $post, string $channelId): SocialMediaPost
    {
        $botToken = config('services.telegram.bot_token');

        if (!$botToken) {
            throw new Exception('Telegram bot token not configured');
        }

        // Create social media post record
        $socialPost = SocialMediaPost::create([
            'post_id' => $post->id,
            'social_media_account_id' => null, // Telegram uses bot token, not user account
            'platform' => 'telegram',
            'status' => 'processing',
        ]);

        try {
            // Send video to channel
            $messageId = $this->sendVideo($post, $channelId, $botToken);

            // Get channel message URL
            $channelUsername = ltrim($channelId, '@');
            $messageUrl = "https://t.me/{$channelUsername}/{$messageId}";

            // Update social post
            $socialPost->update([
                'platform_post_id' => $messageId,
                'platform_post_url' => $messageUrl,
                'status' => 'published',
                'published_at' => now(),
            ]);

            return $socialPost;

        } catch (Exception $e) {
            $socialPost->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send video to Telegram channel
     */
    protected function sendVideo(Post $post, string $channelId, string $botToken): string
    {
        $caption = $this->prepareCaption($post);
        $videoUrl = $post->video_url;

        // Telegram API endpoint
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendVideo";

        $response = Http::post($apiUrl, [
            'chat_id' => $channelId,
            'video' => $videoUrl,
            'caption' => $caption,
            'parse_mode' => 'HTML',
            'supports_streaming' => true,
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to send video to Telegram: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['ok']) || !$data['ok']) {
            throw new Exception('Telegram API error: ' . ($data['description'] ?? 'Unknown error'));
        }

        return $data['result']['message_id'];
    }

    /**
     * Prepare caption for Telegram
     */
    protected function prepareCaption(Post $post): string
    {
        $caption = "<b>" . htmlspecialchars($post->title) . "</b>\n\n";
        $caption .= htmlspecialchars(strip_tags($post->excerpt)) . "\n\n";

        // Add hashtags
        $hashtags = $post->tags->pluck('name')
            ->map(fn($tag) => '#' . str_replace([' ', '-'], '_', $tag))
            ->join(' ');

        if ($hashtags) {
            $caption .= $hashtags . "\n\n";
        }

        // Add link
        $postUrl = route('posts.show', $post->slug);
        $caption .= "🔗 <a href=\"{$postUrl}\">Read full article</a>";

        return $this->truncate($caption, 1024); // Telegram caption limit
    }

    /**
     * Get message view count
     */
    public function getMessageStats(string $messageId, string $channelId): array
    {
        $botToken = config('services.telegram.bot_token');

        // Note: View counts are only available for public channels
        // This is a simplified version - real implementation would need
        // Telegram Bot API access to channel statistics

        return [
            'views' => 0, // Requires additional Telegram Bot API permissions
            'forwards' => 0,
        ];
    }

    /**
     * Truncate text
     */
    protected function truncate(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 3) . '...';
    }

    /**
     * Check if bot has access to channel
     */
    public function checkBotAccess(string $channelId): bool
    {
        $botToken = config('services.telegram.bot_token');
        $apiUrl = "https://api.telegram.org/bot{$botToken}/getChat";

        $response = Http::post($apiUrl, [
            'chat_id' => $channelId,
        ]);

        if (!$response->successful()) {
            return false;
        }

        $data = $response->json();
        return isset($data['ok']) && $data['ok'];
    }
}
