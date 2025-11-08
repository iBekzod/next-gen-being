<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RedditPublisher
{
    private const BASE_URL = 'https://oauth.reddit.com';
    private const AUTH_URL = 'https://www.reddit.com/api/v1/access_token';
    private const REDDIT_API_URL = 'https://api.reddit.com';

    /**
     * Publish post to Reddit
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
            'platform' => 'reddit',
            'status' => 'processing',
        ]);

        try {
            // Get access token
            $token = $this->getValidToken($account);

            if (!$token) {
                throw new Exception('Failed to get valid Reddit access token');
            }

            // Get target subreddit
            $subreddit = $account->metadata['subreddit'] ?? null;

            if (!$subreddit) {
                throw new Exception('No subreddit configured for Reddit account');
            }

            // Publish content
            $redditPostData = $this->publishContent($post, $token, $subreddit);

            if (!$redditPostData || !isset($redditPostData['json']['data']['id'])) {
                throw new Exception('Failed to publish to Reddit');
            }

            $postId = $redditPostData['json']['data']['id'];
            $postFullname = $redditPostData['json']['data']['name'];

            // Get Reddit post URL
            $redditUrl = "https://reddit.com/r/{$subreddit}/comments/{$postId}";

            // Update social post
            $socialPost->update([
                'platform_post_id' => $postId,
                'platform_post_url' => $redditUrl,
                'status' => 'published',
                'published_at' => now(),
                'metadata' => [
                    'subreddit' => $subreddit,
                    'fullname' => $postFullname,
                    'reddit_url' => $redditUrl,
                ]
            ]);

            Log::info("Successfully published post {$post->id} to Reddit r/{$subreddit}");
            return $socialPost;

        } catch (Exception $e) {
            Log::error("Failed to publish to Reddit: {$e->getMessage()}");

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
     * Refresh Reddit access token
     */
    private function refreshToken(SocialMediaAccount $account): ?string
    {
        try {
            $metadata = $account->metadata ?? [];
            $refreshToken = $metadata['refresh_token'] ?? null;

            if (!$refreshToken) {
                Log::warning("No refresh token available for Reddit account {$account->id}");
                return null;
            }

            $clientId = config('services.reddit.client_id');
            $clientSecret = config('services.reddit.client_secret');
            $username = config('services.reddit.username');
            $password = config('services.reddit.password');

            // Reddit uses password grant for script/bot accounts
            // For user accounts, use refresh token grant
            $response = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post(self::AUTH_URL, [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ]);

            if (!$response->successful()) {
                throw new Exception('Failed to refresh Reddit token: ' . $response->body());
            }

            $data = $response->json();
            $newToken = $data['access_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600;

            if ($newToken) {
                // Update account with new token
                $metadata['access_token'] = $newToken;
                $metadata['expires_at'] = now()->addSeconds($expiresIn)->toDateTimeString();

                $account->update(['metadata' => $metadata]);

                return $newToken;
            }

            return null;

        } catch (Exception $e) {
            Log::error("Reddit token refresh failed: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Publish content to Reddit
     */
    private function publishContent(Post $post, string $token, string $subreddit): ?array
    {
        try {
            $content = $this->prepareContent($post);

            // Reddit requires different parameters for link posts vs text posts
            $payload = [
                'sr' => $subreddit,
                'kind' => 'link', // link post with URL
                'title' => substr($post->title, 0, 300), // Reddit limit is 300 chars
                'url' => route('posts.show', $post->slug),
                'text' => $content['description'],
                'api_type' => 'json',
                'validate' => 'on',
            ];

            $response = Http::withToken($token)
                ->withHeaders([
                    'User-Agent' => 'NextGenBeing/1.0 by ' . config('services.reddit.username'),
                ])
                ->post(self::BASE_URL . '/api/submit', $payload);

            if (!$response->successful()) {
                throw new Exception('Reddit API error: ' . $response->body());
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error("Failed to publish content to Reddit: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Prepare content for Reddit
     */
    private function prepareContent(Post $post): array
    {
        $description = $post->excerpt ?? substr(strip_tags($post->content), 0, 500);

        // Add source attribution
        $description .= "\n\n---\n\n";
        $description .= "*This article was originally published on [our blog](" . route('posts.show', $post->slug) . ")*";

        return [
            'description' => $description,
        ];
    }

    /**
     * Get post metrics from Reddit
     */
    public function getMetrics(string $postId, string $token, string $subreddit): array
    {
        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'User-Agent' => 'NextGenBeing/1.0 by ' . config('services.reddit.username'),
                ])
                ->get(self::BASE_URL . "/r/{$subreddit}/comments/{$postId}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data[0]['data']['children'][0])) {
                    $post = $data[0]['data']['children'][0]['data'];

                    return [
                        'id' => $post['id'] ?? null,
                        'title' => $post['title'] ?? null,
                        'url' => $post['url'] ?? null,
                        'score' => $post['score'] ?? 0,
                        'upvotes' => $post['ups'] ?? 0,
                        'downvotes' => $post['downs'] ?? 0,
                        'comments' => $post['num_comments'] ?? 0,
                        'views' => $post['view_count'] ?? 0,
                        'created_at' => isset($post['created_utc']) ? date('Y-m-d H:i:s', $post['created_utc']) : null,
                    ];
                }
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Reddit metrics: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Get list of user's subreddits
     */
    public function getUserSubreddits(string $token): array
    {
        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'User-Agent' => 'NextGenBeing/1.0 by ' . config('services.reddit.username'),
                ])
                ->get(self::BASE_URL . '/subreddits/mine/moderator');

            if ($response->successful()) {
                $subreddits = [];
                $data = $response->json()['data']['children'] ?? [];

                foreach ($data as $item) {
                    $subreddit = $item['data'];
                    $subreddits[] = [
                        'name' => $subreddit['display_name'] ?? null,
                        'title' => $subreddit['title'] ?? null,
                        'subscribers' => $subreddit['subscribers'] ?? 0,
                        'description' => $subreddit['public_description'] ?? '',
                    ];
                }

                return $subreddits;
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Reddit subreddits: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Check if token is valid
     */
    public function validateToken(string $token): bool
    {
        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'User-Agent' => 'NextGenBeing/1.0 by ' . config('services.reddit.username'),
                ])
                ->get(self::BASE_URL . '/api/v1/me');

            return $response->successful();

        } catch (Exception $e) {
            Log::error("Token validation failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Submit a comment to a post
     */
    public function submitComment(string $postFullname, string $comment, string $token): ?array
    {
        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'User-Agent' => 'NextGenBeing/1.0 by ' . config('services.reddit.username'),
                ])
                ->post(self::BASE_URL . '/api/comment', [
                    'thing_id' => $postFullname,
                    'text' => $comment,
                    'api_type' => 'json',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (Exception $e) {
            Log::error("Failed to submit Reddit comment: {$e->getMessage()}");
            return null;
        }
    }
}
