<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FacebookPublisher
{
    private const GRAPH_API_URL = 'https://graph.facebook.com/v18.0';

    /**
     * Publish post to Facebook
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
            'platform' => 'facebook',
            'status' => 'processing',
        ]);

        try {
            // Get access token
            $token = $this->getValidToken($account);

            if (!$token) {
                throw new Exception('Failed to get valid Facebook access token');
            }

            // Determine if publishing to page or personal profile
            $pageId = $account->metadata['page_id'] ?? null;
            $targetId = $pageId ?? $account->metadata['user_id'];

            if (!$targetId) {
                throw new Exception('No page or user ID found in account metadata');
            }

            // Publish content
            $postId = $this->publishContent($post, $token, $targetId);

            if (!$postId) {
                throw new Exception('Failed to publish to Facebook');
            }

            // Get Facebook post URL
            $facebookUrl = "https://www.facebook.com/{$targetId}/posts/{$postId}";

            // Update social post
            $socialPost->update([
                'platform_post_id' => $postId,
                'platform_post_url' => $facebookUrl,
                'status' => 'published',
                'published_at' => now(),
                'metadata' => [
                    'page_id' => $pageId,
                ]
            ]);

            Log::info("Successfully published post {$post->id} to Facebook");
            return $socialPost;

        } catch (Exception $e) {
            Log::error("Failed to publish to Facebook: {$e->getMessage()}");

            $socialPost->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get valid access token, refreshing if necessary
     */
    private function getValidToken(SocialMediaAccount $account): ?string
    {
        $metadata = $account->metadata ?? [];
        $token = $metadata['access_token'] ?? null;
        $expiresAt = isset($metadata['expires_at']) ? strtotime($metadata['expires_at']) : null;

        // If token is expired or expiring soon, refresh it
        if (!$expiresAt || $expiresAt < time() + 300) {
            $token = $this->refreshToken($account);
        }

        return $token;
    }

    /**
     * Refresh Facebook access token
     */
    private function refreshToken(SocialMediaAccount $account): ?string
    {
        try {
            $metadata = $account->metadata ?? [];
            $token = $metadata['access_token'] ?? null;

            if (!$token) {
                Log::warning("No token available for Facebook account {$account->id}");
                return null;
            }

            $appId = config('services.facebook.client_id');
            $appSecret = config('services.facebook.client_secret');

            $response = Http::get(self::GRAPH_API_URL . '/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'fb_exchange_token' => $token,
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to refresh Facebook token: ' . $response->body());
            }

            $data = $response->json();
            $newToken = $data['access_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 5184000;

            if ($newToken) {
                // Update account with new token
                $metadata['access_token'] = $newToken;
                $metadata['expires_at'] = now()->addSeconds($expiresIn)->toDateTimeString();

                $account->update(['metadata' => $metadata]);

                return $newToken;
            }

            return null;

        } catch (Exception $e) {
            Log::error("Facebook token refresh failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Publish content to Facebook
     */
    private function publishContent(Post $post, string $token, string $targetId): ?string
    {
        try {
            $content = $this->prepareContent($post);

            $fields = [
                'message' => $content['message'],
                'access_token' => $token,
            ];

            // Add link if available
            if ($post->featured_image) {
                $fields['picture'] = asset("storage/{$post->featured_image}");
                $fields['link'] = route('posts.show', $post->slug);
                $fields['description'] = $post->excerpt;
                $fields['name'] = $post->title;
            }

            // Facebook API request
            $response = Http::post(self::GRAPH_API_URL . "/{$targetId}/feed", $fields);

            if (!$response->successful()) {
                throw new Exception('Facebook API error: ' . $response->body());
            }

            return $response->json('id');

        } catch (Exception $e) {
            Log::error("Failed to publish content to Facebook: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Prepare content for Facebook
     */
    private function prepareContent(Post $post): array
    {
        $message = "🚀 New Blog Post: {$post->title}\n\n";
        $message .= "{$post->excerpt}\n\n";
        $message .= "Read more at our blog!";

        return [
            'message' => $message,
        ];
    }

    /**
     * Get page information
     */
    public function getPageInfo(string $pageId, string $token): array
    {
        try {
            $response = Http::get(self::GRAPH_API_URL . "/{$pageId}", [
                'fields' => 'id,name,category,picture',
                'access_token' => $token,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Facebook page info: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Get engagement metrics from Facebook
     */
    public function getMetrics(string $postId, string $token): array
    {
        try {
            $response = Http::get(self::GRAPH_API_URL . "/{$postId}", [
                'fields' => 'id,created_time,insights.metric(post_impressions,post_clicks,post_engaged_users).period(lifetime)',
                'access_token' => $token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $insights = $data['insights']['data'] ?? [];

                $metrics = [
                    'id' => $data['id'] ?? null,
                    'created_at' => $data['created_time'] ?? null,
                    'impressions' => 0,
                    'clicks' => 0,
                    'engaged_users' => 0,
                ];

                foreach ($insights as $insight) {
                    if ($insight['name'] === 'post_impressions') {
                        $metrics['impressions'] = $insight['values'][0]['value'] ?? 0;
                    } elseif ($insight['name'] === 'post_clicks') {
                        $metrics['clicks'] = $insight['values'][0]['value'] ?? 0;
                    } elseif ($insight['name'] === 'post_engaged_users') {
                        $metrics['engaged_users'] = $insight['values'][0]['value'] ?? 0;
                    }
                }

                return $metrics;
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Facebook metrics: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * List user pages (for page selection during OAuth)
     */
    public function getUserPages(string $token): array
    {
        try {
            $response = Http::get(self::GRAPH_API_URL . '/me/accounts', [
                'fields' => 'id,name,category,picture',
                'access_token' => $token,
                'limit' => 100,
            ]);

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get user pages: {$e->getMessage()}");
            return [];
        }
    }
}