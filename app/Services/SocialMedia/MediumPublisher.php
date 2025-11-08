<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MediumPublisher
{
    private const BASE_URL = 'https://api.medium.com/v1';

    /**
     * Publish post to Medium
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
            'platform' => 'medium',
            'status' => 'processing',
        ]);

        try {
            // Get access token
            $token = $account->metadata['access_token'] ?? null;

            if (!$token) {
                throw new Exception('No Medium access token available');
            }

            // Get author ID
            $authorId = $this->getAuthorId($token);

            if (!$authorId) {
                throw new Exception('Failed to retrieve Medium author ID');
            }

            // Publish content
            $mediumPostData = $this->publishContent($post, $token, $authorId);

            if (!$mediumPostData || !isset($mediumPostData['id'])) {
                throw new Exception('Failed to publish to Medium');
            }

            // Get Medium post URL
            $mediumUrl = $mediumPostData['url'] ?? "https://medium.com/@{$mediumPostData['authorId']}/{$mediumPostData['id']}";

            // Update social post
            $socialPost->update([
                'platform_post_id' => $mediumPostData['id'],
                'platform_post_url' => $mediumUrl,
                'status' => 'published',
                'published_at' => now(),
                'metadata' => [
                    'author_id' => $authorId,
                    'medium_post_url' => $mediumUrl,
                ]
            ]);

            Log::info("Successfully published post {$post->id} to Medium");
            return $socialPost;

        } catch (Exception $e) {
            Log::error("Failed to publish to Medium: {$e->getMessage()}");

            $socialPost->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get Medium author ID
     */
    private function getAuthorId(string $token): ?string
    {
        try {
            $response = Http::withToken($token)
                ->get(self::BASE_URL . '/me');

            if ($response->successful()) {
                return $response->json('id');
            }

            Log::warning('Failed to get Medium author ID: ' . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error("Failed to get Medium author ID: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Publish content to Medium
     */
    private function publishContent(Post $post, string $token, string $authorId): ?array
    {
        try {
            $content = $this->prepareContent($post);

            // Medium API requires different content format
            $payload = [
                'title' => $post->title,
                'contentFormat' => 'html',
                'content' => $content['html'],
                'publishStatus' => 'public',
                'tags' => $this->extractTags($post),
                'notifyFollowers' => true,
            ];

            // If cross-posting, add canonical URL to original blog
            if ($post->slug) {
                $payload['canonicalUrl'] = route('posts.show', $post->slug);
            }

            $response = Http::withToken($token)
                ->post(self::BASE_URL . "/authors/{$authorId}/posts", $payload);

            if (!$response->successful()) {
                throw new Exception('Medium API error: ' . $response->body());
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error("Failed to publish content to Medium: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Prepare content for Medium
     */
    private function prepareContent(Post $post): array
    {
        $html = $post->content ?? '';

        // If content is markdown, keep as is - Medium supports markdown-like HTML
        // Add featured image if available
        if ($post->featured_image) {
            $imageUrl = asset("storage/{$post->featured_image}");
            $html = "<figure><img src=\"{$imageUrl}\" alt=\"{$post->title}\"></figure>\n\n" . $html;
        }

        // Add footer with link to original post
        $originalUrl = route('posts.show', $post->slug);
        $html .= "\n\n---\n\n";
        $html .= "<p><em>This article was originally published on <a href=\"{$originalUrl}\">our blog</a>. ";
        $html .= "For more content like this, visit our platform.</em></p>";

        return [
            'html' => $html,
        ];
    }

    /**
     * Extract tags from post categories
     */
    private function extractTags(Post $post): array
    {
        $tags = [];

        // Get from post categories if available
        if (method_exists($post, 'categories')) {
            $categories = $post->categories()->pluck('name')->toArray();
            $tags = array_merge($tags, $categories);
        }

        // Add post tags if available
        if (method_exists($post, 'tags')) {
            $postTags = $post->tags()->pluck('name')->toArray();
            $tags = array_merge($tags, $postTags);
        }

        // Limit to Medium's tag limit (5 tags)
        return array_slice($tags, 0, 5);
    }

    /**
     * Get post metrics from Medium
     */
    public function getMetrics(string $postId, string $token): array
    {
        try {
            // Medium API doesn't expose detailed post metrics publicly
            // This would require Medium's Stats API which is limited
            // We can still fetch basic post info
            $response = Http::withToken($token)
                ->get(self::BASE_URL . "/posts/{$postId}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'id' => $data['id'] ?? null,
                    'title' => $data['title'] ?? null,
                    'url' => $data['url'] ?? null,
                    'published_at' => $data['publishedAt'] ?? null,
                    'author_id' => $data['authorId'] ?? null,
                    // Note: Detailed metrics require Medium Premium or Stats API access
                ];
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Medium metrics: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * List user's publications (for publication selection)
     */
    public function getPublications(string $token): array
    {
        try {
            $authorId = $this->getAuthorId($token);

            if (!$authorId) {
                return [];
            }

            $response = Http::withToken($token)
                ->get(self::BASE_URL . "/authors/{$authorId}/publications");

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Medium publications: {$e->getMessage()}");
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
                ->get(self::BASE_URL . '/me');

            return $response->successful();

        } catch (Exception $e) {
            Log::error("Token validation failed: {$e->getMessage()}");
            return false;
        }
    }
}
