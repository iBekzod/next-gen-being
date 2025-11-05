<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Exception;

class InstagramPublisher
{
    /**
     * Publish video to Instagram (Reel)
     *
     * @param Post $post
     * @param SocialMediaAccount $account
     * @return SocialMediaPost
     */
    public function publish(Post $post, SocialMediaAccount $account): SocialMediaPost
    {
        // Create social media post record
        $socialPost = SocialMediaPost::create([
            'post_id' => $post->id,
            'social_media_account_id' => $account->id,
            'platform' => 'instagram',
            'status' => 'processing',
        ]);

        try {
            // Instagram requires video to be publicly accessible URL
            $videoUrl = $post->video_url;

            // Get Instagram Business Account ID
            $igAccountId = $this->getInstagramBusinessAccountId($account);

            // Step 1: Create media container
            $containerId = $this->createMediaContainer($igAccountId, $videoUrl, $post, $account);

            // Step 2: Publish the media
            $mediaId = $this->publishMedia($igAccountId, $containerId, $account);

            // Update social post
            $socialPost->update([
                'platform_post_id' => $mediaId,
                'platform_post_url' => "https://www.instagram.com/reel/{$mediaId}",
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
     * Get Instagram Business Account ID
     */
    protected function getInstagramBusinessAccountId(SocialMediaAccount $account): string
    {
        // First, get Facebook Pages
        $response = Http::get('https://graph.facebook.com/v18.0/me/accounts', [
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to get Facebook pages: ' . $response->body());
        }

        $pages = $response->json()['data'] ?? [];
        if (empty($pages)) {
            throw new Exception('No Facebook pages found. Please create a Facebook page and connect it to an Instagram Business Account.');
        }

        // Get the first page's Instagram account
        $pageId = $pages[0]['id'];
        $pageAccessToken = $pages[0]['access_token'];

        $igResponse = Http::get("https://graph.facebook.com/v18.0/{$pageId}", [
            'fields' => 'instagram_business_account',
            'access_token' => $pageAccessToken,
        ]);

        if (!$igResponse->successful()) {
            throw new Exception('Failed to get Instagram Business Account: ' . $igResponse->body());
        }

        $igAccountId = $igResponse->json()['instagram_business_account']['id'] ?? null;

        if (!$igAccountId) {
            throw new Exception('No Instagram Business Account connected to your Facebook page. Please connect one in Facebook settings.');
        }

        return $igAccountId;
    }

    /**
     * Create media container for Instagram Reel
     */
    protected function createMediaContainer(string $igAccountId, string $videoUrl, Post $post, SocialMediaAccount $account): string
    {
        $caption = $this->prepareCaption($post);

        $response = Http::post("https://graph.facebook.com/v18.0/{$igAccountId}/media", [
            'video_url' => $videoUrl,
            'media_type' => 'REELS',
            'caption' => $caption,
            'share_to_feed' => true,
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to create Instagram media container: ' . $response->body());
        }

        return $response->json()['id'];
    }

    /**
     * Publish media container
     */
    protected function publishMedia(string $igAccountId, string $containerId, SocialMediaAccount $account): string
    {
        // Wait for video processing (poll status)
        $this->waitForProcessing($containerId, $account);

        $response = Http::post("https://graph.facebook.com/v18.0/{$igAccountId}/media_publish", [
            'creation_id' => $containerId,
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to publish Instagram media: ' . $response->body());
        }

        return $response->json()['id'];
    }

    /**
     * Wait for video processing to complete
     */
    protected function waitForProcessing(string $containerId, SocialMediaAccount $account): void
    {
        $maxAttempts = 30; // 30 attempts = 5 minutes
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $response = Http::get("https://graph.facebook.com/v18.0/{$containerId}", [
                'fields' => 'status_code',
                'access_token' => $account->access_token,
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to check media processing status: ' . $response->body());
            }

            $statusCode = $response->json()['status_code'] ?? 'UNKNOWN';

            if ($statusCode === 'FINISHED') {
                return;
            }

            if ($statusCode === 'ERROR') {
                throw new Exception('Instagram video processing failed');
            }

            sleep(10); // Wait 10 seconds
            $attempts++;
        }

        throw new Exception('Instagram video processing timeout');
    }

    /**
     * Prepare caption for Instagram
     */
    protected function prepareCaption(Post $post): string
    {
        $caption = strip_tags($post->excerpt);

        // Add hashtags
        $hashtags = $post->tags->pluck('name')
            ->map(fn($tag) => '#' . str_replace(' ', '', $tag))
            ->take(30) // Instagram allows max 30 hashtags
            ->join(' ');

        if ($hashtags) {
            $caption .= "\n\n" . $hashtags;
        }

        // Add link in bio call-to-action
        $caption .= "\n\n🔗 Link in bio";

        return $this->truncate($caption, 2200); // Instagram caption limit
    }

    /**
     * Get media insights
     */
    public function getMediaStats(string $mediaId, SocialMediaAccount $account): array
    {
        $response = Http::get("https://graph.facebook.com/v18.0/{$mediaId}/insights", [
            'metric' => 'impressions,reach,likes,comments,shares,saved',
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            return [];
        }

        $insights = $response->json()['data'] ?? [];
        $stats = [];

        foreach ($insights as $insight) {
            $stats[$insight['name']] = $insight['values'][0]['value'] ?? 0;
        }

        return [
            'views' => (int)($stats['impressions'] ?? 0),
            'likes' => (int)($stats['likes'] ?? 0),
            'comments' => (int)($stats['comments'] ?? 0),
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
}
