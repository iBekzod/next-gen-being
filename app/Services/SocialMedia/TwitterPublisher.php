<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class TwitterPublisher
{
    /**
     * Publish video to Twitter/X
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
            'platform' => 'twitter',
            'status' => 'processing',
        ]);

        try {
            // Step 1: Upload video
            $mediaId = $this->uploadVideo($post->video_url, $account);

            // Step 2: Create tweet with video
            $tweetId = $this->createTweet($post, $mediaId, $account);

            // Get tweet URL
            $username = $this->getUsernameFromAccount($account);
            $tweetUrl = "https://twitter.com/{$username}/status/{$tweetId}";

            // Update social post
            $socialPost->update([
                'platform_post_id' => $tweetId,
                'platform_post_url' => $tweetUrl,
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
     * Upload video to Twitter
     */
    protected function uploadVideo(string $videoPath, SocialMediaAccount $account): string
    {
        // Download video content
        if (filter_var($videoPath, FILTER_VALIDATE_URL)) {
            $videoContent = file_get_contents($videoPath);
        } else {
            $videoContent = Storage::get($videoPath);
        }

        $videoSize = strlen($videoContent);

        // Step 1: Initialize upload
        $initResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'Content-Type' => 'application/json',
        ])->post('https://upload.twitter.com/1.1/media/upload.json', [
            'command' => 'INIT',
            'total_bytes' => $videoSize,
            'media_type' => 'video/mp4',
            'media_category' => 'tweet_video',
        ]);

        if (!$initResponse->successful()) {
            throw new Exception('Failed to initialize Twitter video upload: ' . $initResponse->body());
        }

        $mediaId = $initResponse->json()['media_id_string'];

        // Step 2: Upload video in chunks
        $this->uploadVideoChunks($mediaId, $videoContent, $account);

        // Step 3: Finalize upload
        $finalizeResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
        ])->post('https://upload.twitter.com/1.1/media/upload.json', [
            'command' => 'FINALIZE',
            'media_id' => $mediaId,
        ]);

        if (!$finalizeResponse->successful()) {
            throw new Exception('Failed to finalize Twitter video upload: ' . $finalizeResponse->body());
        }

        // Step 4: Wait for processing
        $this->waitForProcessing($mediaId, $account);

        return $mediaId;
    }

    /**
     * Upload video in chunks
     */
    protected function uploadVideoChunks(string $mediaId, string $videoContent, SocialMediaAccount $account): void
    {
        $chunkSize = 5 * 1024 * 1024; // 5MB chunks
        $totalBytes = strlen($videoContent);
        $segmentIndex = 0;

        for ($offset = 0; $offset < $totalBytes; $offset += $chunkSize) {
            $chunk = substr($videoContent, $offset, $chunkSize);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $account->access_token,
            ])->asMultipart()->post('https://upload.twitter.com/1.1/media/upload.json', [
                [
                    'name' => 'command',
                    'contents' => 'APPEND',
                ],
                [
                    'name' => 'media_id',
                    'contents' => $mediaId,
                ],
                [
                    'name' => 'segment_index',
                    'contents' => $segmentIndex,
                ],
                [
                    'name' => 'media',
                    'contents' => $chunk,
                    'filename' => 'video.mp4',
                ],
            ]);

            if (!$response->successful()) {
                throw new Exception("Failed to upload video chunk {$segmentIndex}: " . $response->body());
            }

            $segmentIndex++;
        }
    }

    /**
     * Wait for video processing
     */
    protected function waitForProcessing(string $mediaId, SocialMediaAccount $account): void
    {
        $maxAttempts = 30;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $account->access_token,
            ])->get('https://upload.twitter.com/1.1/media/upload.json', [
                'command' => 'STATUS',
                'media_id' => $mediaId,
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to check Twitter video processing status: ' . $response->body());
            }

            $data = $response->json();
            $processingInfo = $data['processing_info'] ?? null;

            if (!$processingInfo) {
                // No processing info means it's ready
                return;
            }

            $state = $processingInfo['state'] ?? 'unknown';

            if ($state === 'succeeded') {
                return;
            }

            if ($state === 'failed') {
                throw new Exception('Twitter video processing failed');
            }

            $checkAfterSecs = $processingInfo['check_after_secs'] ?? 5;
            sleep($checkAfterSecs);
            $attempts++;
        }

        throw new Exception('Twitter video processing timeout');
    }

    /**
     * Create tweet with media
     */
    protected function createTweet(Post $post, string $mediaId, SocialMediaAccount $account): string
    {
        $text = $this->prepareTweetText($post);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'Content-Type' => 'application/json',
        ])->post('https://api.twitter.com/2/tweets', [
            'text' => $text,
            'media' => [
                'media_ids' => [$mediaId],
            ],
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to create tweet: ' . $response->body());
        }

        return $response->json()['data']['id'];
    }

    /**
     * Prepare tweet text
     */
    protected function prepareTweetText(Post $post): string
    {
        $text = $post->title . "\n\n";
        $text .= strip_tags($post->excerpt) . "\n\n";

        // Add hashtags
        $hashtags = $post->tags->pluck('name')
            ->map(fn($tag) => '#' . str_replace(' ', '', $tag))
            ->take(3) // Limit hashtags for readability
            ->join(' ');

        if ($hashtags) {
            $text .= $hashtags . "\n\n";
        }

        // Add link
        $text .= "🔗 " . route('posts.show', $post->slug);

        return $this->truncate($text, 280); // Twitter character limit
    }

    /**
     * Get username from account
     */
    protected function getUsernameFromAccount(SocialMediaAccount $account): string
    {
        return $account->platform_username ?? 'user';
    }

    /**
     * Get tweet statistics
     */
    public function getTweetStats(string $tweetId, SocialMediaAccount $account): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
        ])->get("https://api.twitter.com/2/tweets/{$tweetId}", [
            'tweet.fields' => 'public_metrics',
        ]);

        if (!$response->successful()) {
            return [];
        }

        $metrics = $response->json()['data']['public_metrics'] ?? [];

        return [
            'views' => (int)($metrics['impression_count'] ?? 0),
            'likes' => (int)($metrics['like_count'] ?? 0),
            'comments' => (int)($metrics['reply_count'] ?? 0),
            'retweets' => (int)($metrics['retweet_count'] ?? 0),
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
