<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class LinkedInPublisher
{
    private const BASE_URL = 'https://api.linkedin.com/v2';

    /**
     * Publish post to LinkedIn
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
            'platform' => 'linkedin',
            'status' => 'processing',
        ]);

        try {
            // Get access token
            $token = $this->getValidToken($account);

            if (!$token) {
                throw new Exception('Failed to get valid LinkedIn access token');
            }

            // Publish content
            $postId = $this->publishContent($post, $token, $account);

            if (!$postId) {
                throw new Exception('Failed to publish to LinkedIn');
            }

            // Get LinkedIn post URL
            $linkedinUrl = "https://www.linkedin.com/feed/update/{$postId}";

            // Update social post
            $socialPost->update([
                'platform_post_id' => $postId,
                'platform_post_url' => $linkedinUrl,
                'status' => 'published',
                'published_at' => now(),
                'metadata' => [
                    'token_expires_at' => now()->addSeconds(5184000), // 60 days
                ]
            ]);

            Log::info("Successfully published post {$post->id} to LinkedIn");
            return $socialPost;

        } catch (Exception $e) {
            Log::error("Failed to publish to LinkedIn: {$e->getMessage()}");

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
     * Refresh LinkedIn access token using refresh token
     */
    private function refreshToken(SocialMediaAccount $account): ?string
    {
        try {
            $metadata = $account->metadata ?? [];
            $refreshToken = $metadata['refresh_token'] ?? null;

            if (!$refreshToken) {
                Log::warning("No refresh token available for LinkedIn account {$account->id}");
                return null;
            }

            $clientId = config('services.linkedin.client_id');
            $clientSecret = config('services.linkedin.client_secret');

            $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to refresh LinkedIn token: ' . $response->body());
            }

            $data = $response->json();
            $newToken = $data['access_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 5184000;

            if ($newToken) {
                // Update account with new token
                $metadata['access_token'] = $newToken;
                $metadata['expires_at'] = now()->addSeconds($expiresIn)->toDateTimeString();

                if (isset($data['refresh_token'])) {
                    $metadata['refresh_token'] = $data['refresh_token'];
                }

                $account->update(['metadata' => $metadata]);

                return $newToken;
            }

            return null;

        } catch (Exception $e) {
            Log::error("LinkedIn token refresh failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Publish content to LinkedIn
     */
    private function publishContent(Post $post, string $token, SocialMediaAccount $account): ?string
    {
        try {
            $metadata = $account->metadata ?? [];
            $personUrn = $metadata['person_urn'] ?? null;

            if (!$personUrn) {
                // Try to get person URN from LinkedIn
                $personUrn = $this->getPersonUrn($token);

                if (!$personUrn) {
                    throw new Exception('Could not retrieve LinkedIn person URN');
                }

                $metadata['person_urn'] = $personUrn;
                $account->update(['metadata' => $metadata]);
            }

            $content = $this->prepareContent($post);

            // LinkedIn API request
            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post(self::BASE_URL . '/ugcPosts', [
                    'author' => $personUrn,
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => [
                                'text' => $content['text'],
                            ],
                            'shareMediaCategory' => 'ARTICLE',
                            'media' => [
                                [
                                    'media' => $content['media'],
                                    'status' => 'READY',
                                    'description' => [
                                        'text' => $post->title,
                                    ],
                                    'title' => [
                                        'text' => $post->title,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                    ]
                ]);

            if (!$response->successful()) {
                throw new Exception('LinkedIn API error: ' . $response->body());
            }

            // Extract post ID from response headers
            $location = $response->header('X-LinkedIn-Request-Id') ?? 'unknown-' . time();

            return $location;

        } catch (Exception $e) {
            Log::error("Failed to publish content to LinkedIn: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Get user's person URN from LinkedIn
     */
    private function getPersonUrn(string $token): ?string
    {
        try {
            $response = Http::withToken($token)
                ->get(self::BASE_URL . '/me');

            if ($response->successful()) {
                return $response->json('id');
            }

            return null;

        } catch (Exception $e) {
            Log::error("Failed to get LinkedIn person URN: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Prepare content for LinkedIn
     */
    private function prepareContent(Post $post): array
    {
        $text = "🚀 New Blog Post: {$post->title}\n\n";
        $text .= "{$post->excerpt}\n\n";
        $text .= "Read more: " . route('posts.show', $post->slug);

        $media = [];

        if ($post->featured_image) {
            $media = [
                'title' => [
                    'text' => $post->title,
                ],
                'description' => [
                    'text' => $post->excerpt,
                ],
                'originalUrl' => asset("storage/{$post->featured_image}"),
            ];
        }

        return [
            'text' => $text,
            'media' => $media,
        ];
    }

    /**
     * Get engagement metrics from LinkedIn
     */
    public function getMetrics(string $postId, string $token): array
    {
        try {
            $response = Http::withToken($token)
                ->get(self::BASE_URL . "/ugcPosts/{$postId}?projection=(id,lifecycleState,created)");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'id' => $data['id'] ?? null,
                    'likes' => $data['likeCount'] ?? 0,
                    'comments' => $data['commentCount'] ?? 0,
                    'shares' => $data['shareCount'] ?? 0,
                    'views' => $data['impressionCount'] ?? 0,
                ];
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get LinkedIn metrics: {$e->getMessage()}");
            return [];
        }
    }
}