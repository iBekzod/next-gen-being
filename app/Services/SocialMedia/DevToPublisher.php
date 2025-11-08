<?php

namespace App\Services\SocialMedia;

use App\Models\Post;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DevToPublisher
{
    private const BASE_URL = 'https://dev.to/api';

    /**
     * Publish post to Dev.to
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
            'platform' => 'devto',
            'status' => 'processing',
        ]);

        try {
            // Get API key
            $apiKey = $account->metadata['api_key'] ?? null;

            if (!$apiKey) {
                throw new Exception('No Dev.to API key available');
            }

            // Publish content
            $devtoPostData = $this->publishContent($post, $apiKey);

            if (!$devtoPostData || !isset($devtoPostData['id'])) {
                throw new Exception('Failed to publish to Dev.to');
            }

            // Get Dev.to post URL
            $devtoUrl = $devtoPostData['url'] ?? "https://dev.to/{$devtoPostData['username']}/{$devtoPostData['slug']}";

            // Update social post
            $socialPost->update([
                'platform_post_id' => $devtoPostData['id'],
                'platform_post_url' => $devtoUrl,
                'status' => 'published',
                'published_at' => now(),
                'metadata' => [
                    'dev_to_slug' => $devtoPostData['slug'] ?? null,
                    'dev_to_url' => $devtoUrl,
                    'user_id' => $devtoPostData['user_id'] ?? null,
                ]
            ]);

            Log::info("Successfully published post {$post->id} to Dev.to");
            return $socialPost;

        } catch (Exception $e) {
            Log::error("Failed to publish to Dev.to: {$e->getMessage()}");

            $socialPost->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Publish content to Dev.to
     */
    private function publishContent(Post $post, string $apiKey): ?array
    {
        try {
            $content = $this->prepareContent($post);

            $payload = [
                'article' => [
                    'title' => $post->title,
                    'body_markdown' => $content['markdown'],
                    'published' => true,
                    'tags' => $this->extractTags($post),
                    'description' => $post->excerpt ?? substr(strip_tags($post->content), 0, 160),
                ]
            ];

            // Add featured image if available
            if ($post->featured_image) {
                $payload['article']['main_image'] = asset("storage/{$post->featured_image}");
            }

            // Add canonical URL to original blog
            if ($post->slug) {
                $payload['article']['canonical_url'] = route('posts.show', $post->slug);
            }

            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])->post(self::BASE_URL . '/articles', $payload);

            if (!$response->successful()) {
                throw new Exception('Dev.to API error: ' . $response->body());
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error("Failed to publish content to Dev.to: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Prepare content for Dev.to
     */
    private function prepareContent(Post $post): array
    {
        $markdown = $post->content ?? '';

        // If content is HTML, try to convert to markdown-like format
        if (strpos($markdown, '<') !== false) {
            // Basic HTML to markdown conversion
            $markdown = $this->htmlToMarkdown($markdown);
        }

        // Add footer with link to original post
        $originalUrl = route('posts.show', $post->slug);
        $markdown .= "\n\n---\n\n";
        $markdown .= "*This article was originally published on [our blog]({$originalUrl}). ";
        $markdown .= "For more content like this, visit our platform.*";

        return [
            'markdown' => $markdown,
        ];
    }

    /**
     * Basic HTML to Markdown conversion
     */
    private function htmlToMarkdown(string $html): string
    {
        // Remove script and style tags
        $markdown = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $markdown = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $markdown);

        // Convert common HTML tags to markdown
        $replacements = [
            '/<h1[^>]*>(.*?)<\/h1>/i' => "# $1\n\n",
            '/<h2[^>]*>(.*?)<\/h2>/i' => "## $1\n\n",
            '/<h3[^>]*>(.*?)<\/h3>/i' => "### $1\n\n",
            '/<h4[^>]*>(.*?)<\/h4>/i' => "#### $1\n\n",
            '/<h5[^>]*>(.*?)<\/h5>/i' => "##### $1\n\n",
            '/<h6[^>]*>(.*?)<\/h6>/i' => "###### $1\n\n",
            '/<strong[^>]*>(.*?)<\/strong>/i' => "**$1**",
            '/<b[^>]*>(.*?)<\/b>/i' => "**$1**",
            '/<em[^>]*>(.*?)<\/em>/i' => "*$1*",
            '/<i[^>]*>(.*?)<\/i>/i' => "*$1*",
            '/<a\s+(?:.*?\s+)?href=([\'"])(.*?)\1[^>]*>(.*?)<\/a>/i' => "[$3]($2)",
            '/<img\s+(?:.*?\s+)?src=([\'"])(.*?)\1[^>]*>/i' => "![]($2)",
            '/<br\s*\/?>/i' => "\n",
            '/<p[^>]*>/i' => "",
            '/<\/p>/i' => "\n\n",
            '/<ul[^>]*>/i' => "",
            '/<\/ul>/i' => "\n",
            '/<li[^>]*>/i' => "- ",
            '/<\/li>/i' => "\n",
            '/<ol[^>]*>/i' => "",
            '/<\/ol>/i' => "\n",
        ];

        foreach ($replacements as $pattern => $replacement) {
            $markdown = preg_replace($pattern, $replacement, $markdown);
        }

        // Clean up extra whitespace
        $markdown = preg_replace('/\n\s*\n\s*\n/', "\n\n", $markdown);
        $markdown = trim($markdown);

        return $markdown;
    }

    /**
     * Extract tags for Dev.to
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

        // Limit to Dev.to's reasonable limit (4 tags)
        // Convert to dev.to slug format (lowercase, no spaces)
        $tags = array_slice(array_map(function ($tag) {
            return strtolower(str_replace(' ', '-', $tag));
        }, $tags), 0, 4);

        return $tags;
    }

    /**
     * Get post metrics from Dev.to
     */
    public function getMetrics(string $postId, string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])->get(self::BASE_URL . "/articles/{$postId}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'id' => $data['id'] ?? null,
                    'title' => $data['title'] ?? null,
                    'url' => $data['url'] ?? null,
                    'slug' => $data['slug'] ?? null,
                    'views' => $data['page_views_count'] ?? 0,
                    'likes' => $data['positive_reactions_count'] ?? 0,
                    'comments' => $data['comments_count'] ?? 0,
                    'published_at' => $data['published_at'] ?? null,
                ];
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Dev.to metrics: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Get user profile
     */
    public function getUserProfile(string $apiKey): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])->get(self::BASE_URL . '/user/me');

            if ($response->successful()) {
                return $response->json();
            }

            return [];

        } catch (Exception $e) {
            Log::error("Failed to get Dev.to user profile: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Check if API key is valid
     */
    public function validateApiKey(string $apiKey): bool
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])->get(self::BASE_URL . '/user/me');

            return $response->successful();

        } catch (Exception $e) {
            Log::error("API key validation failed: {$e->getMessage()}");
            return false;
        }
    }
}
