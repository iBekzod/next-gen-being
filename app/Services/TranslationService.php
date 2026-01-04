<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TranslationService
{
    private const CLAUDE_MODEL = 'claude-3-5-sonnet-20241022';
    private const API_TIMEOUT = 120;
    private const MAX_RETRIES = 2;

    // Language configurations
    private const LANGUAGES = [
        'en' => ['name' => 'English', 'code' => 'en-US'],
        'es' => ['name' => 'Español', 'code' => 'es-ES'],
        'fr' => ['name' => 'Français', 'code' => 'fr-FR'],
        'de' => ['name' => 'Deutsch', 'code' => 'de-DE'],
        'zh' => ['name' => '中文', 'code' => 'zh-CN'],
        'pt' => ['name' => 'Português', 'code' => 'pt-PT'],
        'it' => ['name' => 'Italiano', 'code' => 'it-IT'],
        'ja' => ['name' => '日本語', 'code' => 'ja-JP'],
        'ru' => ['name' => 'Русский', 'code' => 'ru-RU'],
        'ko' => ['name' => '한국어', 'code' => 'ko-KR'],
    ];

    protected $apiKey;
    protected $baseUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key');
        if (!$this->apiKey) {
            throw new \Exception('ANTHROPIC_API_KEY not configured');
        }
    }

    /**
     * Translate a post to multiple languages
     */
    public function translatePost(
        Post $post,
        array $targetLanguages = ['es', 'fr', 'de']
    ): array {
        Log::info("Starting translation for post: {$post->id} to " . count($targetLanguages) . " languages");

        $createdTranslations = [];

        foreach ($targetLanguages as $language) {
            if (!isset(self::LANGUAGES[$language])) {
                Log::warning("Unknown language: {$language}");
                continue;
            }

            try {
                $translated = $this->translateToLanguage($post, $language);

                if ($translated) {
                    $createdTranslations[] = $translated;
                }
            } catch (\Exception $e) {
                Log::error("Translation to {$language} failed: {$e->getMessage()}");
            }
        }

        Log::info("Created " . count($createdTranslations) . " translations for post {$post->id}");

        return $createdTranslations;
    }

    /**
     * Translate post to a specific language
     */
    private function translateToLanguage(Post $post, string $language): ?Post
    {
        Log::info("Translating post {$post->id} to {$language}");

        // Check if translation already exists
        $existing = Post::where('base_post_id', $post->id)
            ->where('base_language', $language)
            ->first();

        if ($existing) {
            Log::info("Translation already exists for language {$language}");
            return $existing;
        }

        try {
            // Translate content
            $translatedContent = $this->translateContent(
                title: $post->title,
                excerpt: $post->excerpt,
                content: $post->content,
                targetLanguage: $language
            );

            if (!$translatedContent) {
                return null;
            }

            // Generate language-specific slug
            $slug = Str::slug($translatedContent['title']) . '-' . $language;

            // Ensure unique slug
            $count = 1;
            $originalSlug = $slug;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            // Create translation post
            $translatedPost = Post::create([
                'title' => $translatedContent['title'],
                'excerpt' => $translatedContent['excerpt'],
                'content' => $translatedContent['content'],
                'slug' => $slug,
                'status' => $post->status,
                'published_at' => $post->published_at,
                'is_curated' => $post->is_curated,
                'content_source_type' => $post->content_source_type,
                'author_id' => $post->author_id,
                'category_id' => $post->category_id,
                'content_aggregation_id' => $post->content_aggregation_id,
                'source_ids' => $post->source_ids,
                'references' => $post->references,
                'base_language' => $language,
                'base_post_id' => $post->id,
                'paraphrase_confidence_score' => $post->paraphrase_confidence_score,
                'is_fact_verified' => $post->is_fact_verified,
                'allow_comments' => $post->allow_comments,
                'is_premium' => $post->is_premium,
            ]);

            // Copy tags
            foreach ($post->tags as $tag) {
                $translatedPost->tags()->attach($tag);
            }

            Log::info("Created translation for language {$language}: {$translatedPost->id}");

            return $translatedPost;

        } catch (\Exception $e) {
            Log::error("Failed to create translation for {$language}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Translate content using Claude
     */
    private function translateContent(
        string $title,
        string $excerpt,
        string $content,
        string $targetLanguage,
        int $attempt = 1
    ): ?array {
        try {
            $languageInfo = self::LANGUAGES[$targetLanguage] ?? null;
            if (!$languageInfo) {
                return null;
            }

            $prompt = "You are a professional translator specializing in technology content.

Translate the following article title, excerpt, and content to {$languageInfo['name']} ({$languageInfo['code']}).

IMPORTANT:
1. Preserve all technical terms accurately
2. Maintain the structure and formatting
3. Keep references and citations intact
4. Preserve URLs exactly as they are
5. Maintain the tone and style of the original
6. Do not add or remove information
7. Keep any source citations like [Source Name] as they are

TITLE:
{$title}

EXCERPT:
{$excerpt}

CONTENT:
{$content}

Respond in JSON format:
{
  \"title\": \"Translated title\",
  \"excerpt\": \"Translated excerpt\",
  \"content\": \"Translated content with HTML/markdown preserved\"
}";

            $response = Http::timeout(self::API_TIMEOUT)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                ])
                ->post($this->baseUrl, [
                    'model' => self::CLAUDE_MODEL,
                    'max_tokens' => 4000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                if ($attempt < self::MAX_RETRIES) {
                    Log::warning("Translation attempt {$attempt} failed, retrying...");
                    sleep(2 ** $attempt);
                    return $this->translateContent($title, $excerpt, $content, $targetLanguage, $attempt + 1);
                }
                return null;
            }

            $responseText = $response->json('content.0.text');
            if (!$responseText) {
                return null;
            }

            // Parse JSON
            try {
                $parsed = json_decode($responseText, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Try to extract JSON
                    if (preg_match('/\{[^{}]*"title"[^{}]*\}/s', $responseText, $matches)) {
                        $parsed = json_decode($matches[0], true);
                    }
                }

                if (!isset($parsed['content'])) {
                    return null;
                }

                return $parsed;

            } catch (\Exception $e) {
                Log::error("Failed to parse translation response: {$e->getMessage()}");
                return null;
            }

        } catch (\Exception $e) {
            if ($attempt < self::MAX_RETRIES) {
                sleep(2 ** $attempt);
                return $this->translateContent($title, $excerpt, $content, $targetLanguage, $attempt + 1);
            }
            throw $e;
        }
    }

    /**
     * Get all available languages
     */
    public function getAvailableLanguages(): array
    {
        return self::LANGUAGES;
    }

    /**
     * Get language info
     */
    public function getLanguageInfo(string $languageCode): ?array
    {
        return self::LANGUAGES[$languageCode] ?? null;
    }

    /**
     * Check if language is supported
     */
    public function isLanguageSupported(string $languageCode): bool
    {
        return isset(self::LANGUAGES[$languageCode]);
    }

    /**
     * Get all translations of a post
     */
    public function getPostTranslations(Post $post): array
    {
        // If this is a translation, get all translations of the base post
        $basePost = $post->basePost ?? $post;

        $translations = [];
        $translations[$basePost->base_language] = $basePost;

        // Get all language versions
        foreach ($basePost->translatedVersions as $version) {
            $translations[$version->base_language] = $version;
        }

        return $translations;
    }

    /**
     * Get translation stats
     */
    public function getTranslationStats(): array
    {
        $allPosts = Post::where('is_curated', true)->get();
        $basePostCount = $allPosts->whereNull('base_post_id')->count();
        $totalTranslations = $allPosts->count() - $basePostCount;

        $languageDistribution = $allPosts->groupBy('base_language')->map->count();

        return [
            'total_posts' => $allPosts->count(),
            'base_posts' => $basePostCount,
            'total_translations' => $totalTranslations,
            'avg_translations_per_post' => $basePostCount > 0 ? round($totalTranslations / $basePostCount, 2) : 0,
            'languages_used' => $languageDistribution,
            'coverage' => round(($totalTranslations / ($basePostCount * count(self::LANGUAGES))) * 100, 2) . '%',
        ];
    }

    /**
     * Create missing translations for all curated posts
     */
    public function createMissingTranslations(array $targetLanguages = ['es', 'fr', 'de']): int
    {
        Log::info("Creating missing translations for target languages: " . implode(', ', $targetLanguages));

        $basePosts = Post::where('is_curated', true)
            ->whereNull('base_post_id')
            ->where('status', 'published')
            ->get();

        $created = 0;

        foreach ($basePosts as $post) {
            $languages = array_filter($targetLanguages, function ($lang) use ($post) {
                return !$post->translatedVersions()
                    ->where('base_language', $lang)
                    ->exists();
            });

            if (!empty($languages)) {
                $translations = $this->translatePost($post, $languages);
                $created += count($translations);
            }
        }

        Log::info("Created {$created} missing translations");

        return $created;
    }

    /**
     * Translate batch of posts
     */
    public function translateBatch(array $postIds, array $targetLanguages): int
    {
        $posts = Post::whereIn('id', $postIds)->get();
        $totalCreated = 0;

        foreach ($posts as $post) {
            $created = count($this->translatePost($post, $targetLanguages));
            $totalCreated += $created;
        }

        return $totalCreated;
    }

    /**
     * Validate translation quality
     */
    public function validateTranslation(Post $originalPost, Post $translatedPost): array
    {
        $validation = [
            'valid' => true,
            'issues' => [],
            'warnings' => [],
        ];

        // Check title is different
        if ($originalPost->title === $translatedPost->title) {
            $validation['issues'][] = 'Title was not translated';
            $validation['valid'] = false;
        }

        // Check content is different
        if ($originalPost->content === $translatedPost->content) {
            $validation['issues'][] = 'Content was not translated';
            $validation['valid'] = false;
        }

        // Check word counts are reasonable
        $originalWords = str_word_count($originalPost->content);
        $translatedWords = str_word_count($translatedPost->content);
        $ratio = $translatedWords / $originalWords;

        if ($ratio < 0.7 || $ratio > 1.5) {
            $validation['warnings'][] = "Word count ratio unusual: {$ratio}";
        }

        // Check references are preserved
        if (preg_match_all('/\[.*?\]/', $originalPost->content) !==
            preg_match_all('/\[.*?\]/', $translatedPost->content)) {
            $validation['warnings'][] = 'Reference count differs from original';
        }

        return $validation;
    }

    /**
     * Get language-specific URL for post
     */
    public function getPostUrlForLanguage(Post $post, string $language): string
    {
        $post = $this->getPostInLanguage($post, $language);

        if (!$post) {
            return route('posts.show', $post->slug ?? 'not-found');
        }

        return route('posts.show', $post->slug);
    }

    /**
     * Get post in specific language
     */
    public function getPostInLanguage(Post $post, string $language): ?Post
    {
        if ($post->base_language === $language) {
            return $post;
        }

        if ($post->base_post_id) {
            // This is a translation, get the base post first
            $basePost = $post->basePost;
        } else {
            // This is a base post
            $basePost = $post;
        }

        if ($language === $basePost->base_language) {
            return $basePost;
        }

        return $basePost->translatedVersions()
            ->where('base_language', $language)
            ->first();
    }

    /**
     * Get language switcher data
     */
    public function getLanguageSwitcherData(Post $post): array
    {
        $translations = $this->getPostTranslations($post);

        $data = [];
        foreach (self::LANGUAGES as $code => $info) {
            $data[$code] = [
                'name' => $info['name'],
                'code' => $code,
                'available' => isset($translations[$code]),
                'url' => isset($translations[$code]) ? route('posts.show', $translations[$code]->slug) : null,
                'current' => $post->base_language === $code,
            ];
        }

        return $data;
    }
}
