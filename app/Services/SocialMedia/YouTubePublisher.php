<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class YouTubePublisher
{
    /**
     * Publish video to YouTube
     *
     * @param Post $post
     * @param SocialMediaAccount $account
     * @return SocialMediaPost
     */
    public function publish(Post $post, SocialMediaAccount $account): SocialMediaPost
    {
        // Check if token is expired
        if ($account->isTokenExpired()) {
            $this->refreshAccessToken($account);
        }

        // Create social media post record
        $socialPost = SocialMediaPost::create([
            'post_id' => $post->id,
            'social_media_account_id' => $account->id,
            'platform' => 'youtube',
            'status' => 'processing',
        ]);

        try {
            // Step 1: Initialize upload
            $videoId = $this->uploadVideo($post, $account);

            // Step 2: Update video metadata
            $this->updateVideoMetadata($videoId, $post, $account);

            // Step 3: Set thumbnail
            if ($post->video_thumbnail) {
                $this->setThumbnail($videoId, $post->video_thumbnail, $account);
            }

            // Update social post with success
            $socialPost->update([
                'platform_post_id' => $videoId,
                'platform_post_url' => "https://www.youtube.com/watch?v={$videoId}",
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
     * Upload video to YouTube
     */
    protected function uploadVideo(Post $post, SocialMediaAccount $account): string
    {
        $videoPath = $post->video_url;

        // Download video if it's a URL
        if (filter_var($videoPath, FILTER_VALIDATE_URL)) {
            $videoContent = file_get_contents($videoPath);
        } else {
            $videoContent = Storage::get($videoPath);
        }

        // Prepare metadata
        $metadata = [
            'snippet' => [
                'title' => $this->truncate($post->title, 100),
                'description' => $this->prepareDescription($post),
                'tags' => $this->prepareTags($post),
                'categoryId' => '28', // Science & Technology
            ],
            'status' => [
                'privacyStatus' => 'public',
                'selfDeclaredMadeForKids' => false,
            ],
        ];

        // Upload video using resumable upload
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'Content-Type' => 'application/octet-stream',
            'X-Upload-Content-Length' => strlen($videoContent),
            'X-Upload-Content-Type' => 'video/*',
        ])->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status', $metadata);

        if (!$response->successful()) {
            throw new Exception('Failed to initialize YouTube upload: ' . $response->body());
        }

        $uploadUrl = $response->header('Location');

        // Upload video content
        $uploadResponse = Http::withHeaders([
            'Content-Type' => 'video/*',
        ])->withBody($videoContent, 'video/mp4')->put($uploadUrl);

        if (!$uploadResponse->successful()) {
            throw new Exception('Failed to upload video to YouTube: ' . $uploadResponse->body());
        }

        $videoData = $uploadResponse->json();
        return $videoData['id'];
    }

    /**
     * Update video metadata
     */
    protected function updateVideoMetadata(string $videoId, Post $post, SocialMediaAccount $account): void
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'Content-Type' => 'application/json',
        ])->put("https://www.googleapis.com/youtube/v3/videos?part=snippet", [
            'id' => $videoId,
            'snippet' => [
                'title' => $this->truncate($post->title, 100),
                'description' => $this->prepareDescription($post),
                'categoryId' => '28',
            ],
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to update YouTube video metadata: ' . $response->body());
        }
    }

    /**
     * Set custom thumbnail
     */
    protected function setThumbnail(string $videoId, string $thumbnailUrl, SocialMediaAccount $account): void
    {
        $thumbnailContent = file_get_contents($thumbnailUrl);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
            'Content-Type' => 'image/jpeg',
        ])->withBody($thumbnailContent, 'image/jpeg')
            ->post("https://www.googleapis.com/upload/youtube/v3/thumbnails/set?videoId={$videoId}");

        if (!$response->successful()) {
            // Thumbnail upload is not critical, just log the error
            \Log::warning('Failed to set YouTube thumbnail: ' . $response->body());
        }
    }

    /**
     * Refresh access token
     */
    protected function refreshAccessToken(SocialMediaAccount $account): void
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.youtube.client_id'),
            'client_secret' => config('services.youtube.client_secret'),
            'refresh_token' => $account->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to refresh YouTube access token');
        }

        $data = $response->json();

        $account->update([
            'access_token' => $data['access_token'],
            'token_expires_at' => now()->addSeconds($data['expires_in']),
        ]);
    }

    /**
     * Prepare video description
     */
    protected function prepareDescription(Post $post): string
    {
        $description = strip_tags($post->excerpt) . "\n\n";
        $description .= "📖 Read the full article: " . route('posts.show', $post->slug) . "\n\n";

        // Add hashtags
        $hashtags = $post->tags->pluck('name')->map(fn($tag) => '#' . str_replace(' ', '', $tag))->join(' ');
        if ($hashtags) {
            $description .= "\n" . $hashtags;
        }

        return $this->truncate($description, 5000);
    }

    /**
     * Prepare tags
     */
    protected function prepareTags(Post $post): array
    {
        $tags = $post->tags->pluck('name')->toArray();

        // Add category as tag
        if ($post->category) {
            $tags[] = $post->category->name;
        }

        // Limit to 500 characters total
        return array_slice($tags, 0, 15);
    }

    /**
     * Truncate text to specified length
     */
    protected function truncate(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 3) . '...';
    }

    /**
     * Get video statistics
     */
    public function getVideoStats(string $videoId, SocialMediaAccount $account): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $account->access_token,
        ])->get('https://www.googleapis.com/youtube/v3/videos', [
            'part' => 'statistics',
            'id' => $videoId,
        ]);

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();
        $stats = $data['items'][0]['statistics'] ?? [];

        return [
            'views' => (int)($stats['viewCount'] ?? 0),
            'likes' => (int)($stats['likeCount'] ?? 0),
            'comments' => (int)($stats['commentCount'] ?? 0),
        ];
    }
}
