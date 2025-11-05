<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EnhancedAIGenerationService
{
    /**
     * Generate AI content for a blog post
     */
    public function generateContent(User $user, string $topic, ?string $keywords = null): array
    {
        // Check quota
        if (!$this->canGenerateContent($user)) {
            throw new Exception('You have reached your monthly AI content generation limit. Please upgrade your plan.');
        }

        try {
            // Determine which API to use based on tier
            $content = $this->callAIProvider($user, $topic, $keywords);

            // Track usage
            $this->incrementContentUsage($user);

            return [
                'success' => true,
                'content' => $content,
                'remaining_quota' => $this->getRemainingContentQuota($user),
            ];
        } catch (Exception $e) {
            Log::error('AI Content Generation Failed', [
                'user_id' => $user->id,
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate AI image for a blog post
     */
    public function generateImage(User $user, string $prompt): array
    {
        // Check quota
        if (!$this->canGenerateImage($user)) {
            throw new Exception('You have reached your monthly AI image generation limit. Please upgrade your plan.');
        }

        try {
            // Determine which API to use based on tier
            $imageUrl = $this->callImageProvider($user, $prompt);

            // Track usage
            $this->incrementImageUsage($user);

            return [
                'success' => true,
                'image_url' => $imageUrl,
                'remaining_quota' => $this->getRemainingImageQuota($user),
            ];
        } catch (Exception $e) {
            Log::error('AI Image Generation Failed', [
                'user_id' => $user->id,
                'prompt' => $prompt,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Call the appropriate AI provider for content generation
     */
    protected function callAIProvider(User $user, string $topic, ?string $keywords): string
    {
        // Free and Basic tiers use Groq with user's API key
        if (in_array($user->ai_tier, ['free', 'basic']) && $user->groq_api_key) {
            return $this->generateWithGroq($user->groq_api_key, $topic, $keywords);
        }

        // Premium and Enterprise use platform's GPT-4
        if (in_array($user->ai_tier, ['premium', 'enterprise'])) {
            $apiKey = config('services.openai.api_key');
            if (!$apiKey) {
                throw new Exception('Platform AI service is temporarily unavailable.');
            }
            return $this->generateWithGPT4($apiKey, $topic, $keywords);
        }

        throw new Exception('Please add your Groq API key in AI Settings or upgrade to Premium.');
    }

    /**
     * Call the appropriate image provider
     */
    protected function callImageProvider(User $user, string $prompt): string
    {
        // Free and Basic tiers use Unsplash with user's API key
        if (in_array($user->ai_tier, ['free', 'basic']) && $user->unsplash_api_key) {
            return $this->searchUnsplash($user->unsplash_api_key, $prompt);
        }

        // Premium and Enterprise use platform's DALL-E 3
        if (in_array($user->ai_tier, ['premium', 'enterprise'])) {
            $apiKey = config('services.openai.api_key');
            if (!$apiKey) {
                throw new Exception('Platform AI service is temporarily unavailable.');
            }
            return $this->generateWithDALLE($apiKey, $prompt);
        }

        throw new Exception('Please add your Unsplash API key in AI Settings or upgrade to Premium.');
    }

    /**
     * Generate content using Groq API
     */
    protected function generateWithGroq(string $apiKey, string $topic, ?string $keywords): string
    {
        $systemPrompt = "You are a professional tech blogger. Write high-quality, engaging blog content.";
        $userPrompt = "Write a comprehensive blog post about: {$topic}";

        if ($keywords) {
            $userPrompt .= "\n\nInclude these keywords: {$keywords}";
        }

        $userPrompt .= "\n\nFormat the output as HTML with proper headings (h2, h3), paragraphs, and lists.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2048,
        ]);

        if (!$response->successful()) {
            throw new Exception('Groq API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Generate content using OpenAI GPT-4
     */
    protected function generateWithGPT4(string $apiKey, string $topic, ?string $keywords): string
    {
        $systemPrompt = "You are an expert tech blogger and content writer. Write high-quality, SEO-optimized blog content.";
        $userPrompt = "Write a comprehensive, engaging blog post about: {$topic}";

        if ($keywords) {
            $userPrompt .= "\n\nInclude these keywords naturally: {$keywords}";
        }

        $userPrompt .= "\n\nFormat the output as HTML with proper headings (h2, h3), paragraphs, lists, and code blocks if relevant. Make it SEO-friendly.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4-turbo-preview',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 3000,
        ]);

        if (!$response->successful()) {
            throw new Exception('OpenAI API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Search Unsplash for images
     */
    protected function searchUnsplash(string $apiKey, string $query): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Client-ID ' . $apiKey,
        ])->get('https://api.unsplash.com/search/photos', [
            'query' => $query,
            'per_page' => 1,
            'orientation' => 'landscape',
        ]);

        if (!$response->successful()) {
            throw new Exception('Unsplash API error: ' . $response->body());
        }

        $data = $response->json();
        if (empty($data['results'])) {
            throw new Exception('No images found for this search.');
        }

        return $data['results'][0]['urls']['regular'];
    }

    /**
     * Generate image using DALL-E 3
     */
    protected function generateWithDALLE(string $apiKey, string $prompt): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1792x1024',
            'quality' => 'standard',
        ]);

        if (!$response->successful()) {
            throw new Exception('DALL-E API error: ' . $response->body());
        }

        $data = $response->json();
        return $data['data'][0]['url'] ?? '';
    }

    /**
     * Check if user can generate content
     */
    public function canGenerateContent(User $user): bool
    {
        // Reset quota if needed
        $this->resetQuotaIfNeeded($user);

        // Free tier with no API key
        if ($user->ai_tier === 'free' && !$user->groq_api_key) {
            return false;
        }

        // Check monthly limit
        if ($user->monthly_ai_posts_limit === null) {
            return true; // Unlimited
        }

        return $user->ai_posts_generated < $user->monthly_ai_posts_limit;
    }

    /**
     * Check if user can generate images
     */
    public function canGenerateImage(User $user): bool
    {
        // Reset quota if needed
        $this->resetQuotaIfNeeded($user);

        // Free tier with no API key
        if ($user->ai_tier === 'free' && !$user->unsplash_api_key) {
            return false;
        }

        // Check monthly limit
        if ($user->monthly_ai_images_limit === null) {
            return true; // Unlimited
        }

        return $user->ai_images_generated < $user->monthly_ai_images_limit;
    }

    /**
     * Get remaining content quota
     */
    public function getRemainingContentQuota(User $user): int|string
    {
        if ($user->monthly_ai_posts_limit === null) {
            return 'unlimited';
        }

        return max(0, $user->monthly_ai_posts_limit - $user->ai_posts_generated);
    }

    /**
     * Get remaining image quota
     */
    public function getRemainingImageQuota(User $user): int|string
    {
        if ($user->monthly_ai_images_limit === null) {
            return 'unlimited';
        }

        return max(0, $user->monthly_ai_images_limit - $user->ai_images_generated);
    }

    /**
     * Increment content usage counter
     */
    protected function incrementContentUsage(User $user): void
    {
        $user->increment('ai_posts_generated');
    }

    /**
     * Increment image usage counter
     */
    protected function incrementImageUsage(User $user): void
    {
        $user->increment('ai_images_generated');
    }

    /**
     * Reset quota if it's a new month
     */
    protected function resetQuotaIfNeeded(User $user): void
    {
        if (!$user->ai_usage_reset_date || now()->greaterThan($user->ai_usage_reset_date)) {
            $user->update([
                'ai_posts_generated' => 0,
                'ai_images_generated' => 0,
                'ai_usage_reset_date' => now()->addMonth()->startOfMonth(),
            ]);
        }
    }

    /**
     * Get tier limits
     */
    public function getTierLimits(string $tier): array
    {
        return match($tier) {
            'free' => [
                'posts' => 5,
                'images' => 10,
                'price' => 0,
                'features' => ['Bring your own API keys', 'Basic Groq/Unsplash access'],
            ],
            'basic' => [
                'posts' => 50,
                'images' => 100,
                'price' => 9.99,
                'features' => ['50 AI posts/month', '100 AI images/month', 'Groq + Unsplash', 'Priority support'],
            ],
            'premium' => [
                'posts' => null, // unlimited
                'images' => null, // unlimited
                'price' => 29.99,
                'features' => ['Unlimited AI posts', 'Unlimited AI images', 'GPT-4 + DALL-E 3', 'Priority support', 'Advanced features'],
            ],
            'enterprise' => [
                'posts' => null,
                'images' => null,
                'price' => 99.99,
                'features' => ['Everything in Premium', 'Dedicated support', 'Custom AI models', 'API access', 'White-label options'],
            ],
            default => [
                'posts' => 0,
                'images' => 0,
                'price' => 0,
                'features' => [],
            ],
        };
    }

    /**
     * Get usage statistics for a user
     */
    public function getUsageStats(User $user): array
    {
        $limits = $this->getTierLimits($user->ai_tier);

        return [
            'tier' => $user->ai_tier,
            'posts_used' => $user->ai_posts_generated,
            'posts_limit' => $limits['posts'],
            'images_used' => $user->ai_images_generated,
            'images_limit' => $limits['images'],
            'reset_date' => $user->ai_usage_reset_date,
            'can_generate_content' => $this->canGenerateContent($user),
            'can_generate_image' => $this->canGenerateImage($user),
        ];
    }
}
