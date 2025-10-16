<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateAiPost extends Command
{
    protected $signature = 'ai:generate-post
                            {--category= : Specific category slug to generate post for}
                            {--author= : Author user ID (defaults to first admin)}
                            {--draft : Create as draft instead of publishing}
                            {--premium : Mark post as premium content}
                            {--provider= : AI provider (groq, openai) - defaults to config}';

    protected $description = 'Generate a complete AI-written blog post using free Groq API';

    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private string $provider;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Determine provider
        $this->provider = $this->option('provider') ?? config('ai.provider', 'groq');

        // Set up API credentials based on provider
        $this->setupProvider();

        if (!$this->apiKey) {
            $this->error("AI API key not configured. Please set {$this->getApiKeyEnvName()} in your .env file.");
            return self::FAILURE;
        }

        $this->info('🤖 Starting AI post generation...');

        try {
            // Step 1: Get trending topic
            $this->info('📊 Analyzing trending topics...');
            $topic = $this->selectTrendingTopic();

            // Step 2: Generate post content
            $this->info("✍️  Generating content for: {$topic['title']}");
            $postData = $this->generatePostContent($topic);

            // Step 3: Get or create category
            $category = $this->getCategory();

            // Step 4: Get author
            $author = $this->getAuthor();

            // Step 5: Generate tags
            $tags = $this->getOrCreateTags($postData['tags']);

            // Step 6: Create the post
            $post = Post::create([
                'title' => $postData['title'],
                'excerpt' => $postData['excerpt'],
                'content' => $postData['content'],
                'author_id' => $author->id,
                'category_id' => $category->id,
                'status' => $this->option('draft') ? 'draft' : 'published',
                'published_at' => $this->option('draft') ? null : now(),
                'is_premium' => $this->option('premium'),
                'is_featured' => false,
                'allow_comments' => true,
                'seo_meta' => [
                    'meta_title' => $postData['meta_title'],
                    'meta_description' => $postData['meta_description'],
                    'meta_keywords' => $postData['keywords'],
                    'og_title' => $postData['title'],
                    'og_description' => $postData['excerpt'],
                ],
            ]);

            // Attach tags
            $post->tags()->attach($tags->pluck('id'));

            $status = $this->option('draft') ? 'draft' : 'published';
            $this->info("✅ Post created successfully!");
            $this->info("   ID: {$post->id}");
            $this->info("   Title: {$post->title}");
            $this->info("   Status: {$status}");
            $this->info("   Category: {$category->name}");
            $this->info("   Author: {$author->name}");
            $this->info("   Tags: " . $tags->pluck('name')->implode(', '));

            if (!$this->option('draft')) {
                $url = config('app.url') . '/posts/' . $post->slug;
                $this->info("   URL: {$url}");
            }

            Log::info('AI post generated successfully', [
                'post_id' => $post->id,
                'title' => $post->title,
                'topic' => $topic['title']
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to generate post: ' . $e->getMessage());
            Log::error('AI post generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    private function selectTrendingTopic(): array
    {
        // Get recent topics to avoid duplication
        $recentTopics = Post::where('created_at', '>=', now()->subDays(30))
            ->pluck('title')
            ->toArray();

        // Generate topic ideas using AI
        $prompt = "Generate a trending tech blog post topic. Recent topics to avoid: " .
                  implode(', ', array_slice($recentTopics, 0, 10)) . ". " .
                  "Return only a JSON object with: {\"title\": \"topic title\", \"category\": \"suggested category\"}";

        $response = $this->callOpenAI([
            [
                'role' => 'system',
                'content' => 'You are a tech blog content strategist. Generate trending, engaging topics about technology, programming, AI, web development, or related fields.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 150, 0.8);

        // Parse response
        preg_match('/\{.*\}/s', $response, $matches);
        if (!empty($matches)) {
            $topic = json_decode($matches[0], true);
            if ($topic && isset($topic['title'])) {
                return $topic;
            }
        }

        // Fallback topic
        return [
            'title' => 'The Evolution of Modern Web Development',
            'category' => 'Web Development'
        ];
    }

    private function generatePostContent(array $topic): array
    {
        $prompt = "Write a comprehensive, well-structured blog post about: {$topic['title']}

Requirements:
- 1000-1500 words
- Include an engaging introduction
- Use clear headings and subheadings (use ## for h2, ### for h3)
- Provide practical examples and insights
- Include a conclusion with key takeaways
- Use markdown formatting
- Write in a professional but approachable tone
- Focus on providing value to readers

Also provide:
- A compelling 150-200 character excerpt
- Meta title (60 characters max)
- Meta description (155 characters max)
- 5-7 relevant keywords
- 3-5 tags

Return response in this JSON format:
{
  \"title\": \"Final polished title\",
  \"content\": \"Full markdown content\",
  \"excerpt\": \"Brief excerpt\",
  \"meta_title\": \"SEO title\",
  \"meta_description\": \"SEO description\",
  \"keywords\": [\"keyword1\", \"keyword2\"],
  \"tags\": [\"tag1\", \"tag2\"]
}";

        $response = $this->callOpenAI([
            [
                'role' => 'system',
                'content' => 'You are an expert tech blogger who writes engaging, informative articles about technology, programming, and web development.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 3000, 0.7);

        // Parse JSON response
        preg_match('/\{.*\}/s', $response, $matches);
        if (empty($matches)) {
            throw new \Exception('Failed to parse AI response - no JSON found');
        }

        $postData = json_decode($matches[0], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in AI response: ' . json_last_error_msg());
        }

        // Validate required fields
        $required = ['title', 'content', 'excerpt', 'meta_title', 'meta_description', 'keywords', 'tags'];
        foreach ($required as $field) {
            if (!isset($postData[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        return $postData;
    }

    private function setupProvider(): void
    {
        switch ($this->provider) {
            case 'groq':
                $this->apiKey = config('services.groq.api_key');
                $this->baseUrl = config('services.groq.base_url');
                $this->model = config('services.groq.model', 'llama-3.1-70b-versatile');
                break;

            case 'openai':
                $this->apiKey = config('services.openai.api_key');
                $this->baseUrl = 'https://api.openai.com/v1';
                $this->model = config('services.openai.model', 'gpt-4');
                break;

            default:
                throw new \Exception("Unsupported AI provider: {$this->provider}");
        }
    }

    private function getApiKeyEnvName(): string
    {
        return match ($this->provider) {
            'groq' => 'GROQ_API_KEY',
            'openai' => 'OPENAI_API_KEY',
            default => 'AI_API_KEY',
        };
    }

    private function callOpenAI(array $messages, int $maxTokens = 2000, float $temperature = 0.7): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
        ];

        // Groq uses max_tokens, some models use max_completion_tokens
        if ($this->provider === 'groq') {
            $payload['max_tokens'] = $maxTokens;
        } else {
            $payload['max_tokens'] = $maxTokens;
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/chat/completions', $payload);

        if (!$response->successful()) {
            throw new \Exception("{$this->provider} API request failed: " . $response->body());
        }

        return $response->json()['choices'][0]['message']['content'];
    }

    private function getCategory()
    {
        $categorySlug = $this->option('category');

        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if (!$category) {
                $this->warn("Category '{$categorySlug}' not found. Using default category.");
            } else {
                return $category;
            }
        }

        // Get a random active category or create default
        $category = Category::inRandomOrder()->first();

        if (!$category) {
            $category = Category::create([
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Tech articles and insights',
            ]);
            $this->info("Created default 'Technology' category");
        }

        return $category;
    }

    private function getAuthor()
    {
        $authorId = $this->option('author');

        if ($authorId) {
            $author = User::find($authorId);
            if ($author) {
                return $author;
            }
            $this->warn("Author ID {$authorId} not found. Using default author.");
        }

        // Get first admin or first user
        $author = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$author) {
            $author = User::first();
        }

        if (!$author) {
            throw new \Exception('No users found in the system. Please create a user first.');
        }

        return $author;
    }

    private function getOrCreateTags(array $tagNames)
    {
        $tags = collect();

        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName, 'description' => '']
            );
            $tags->push($tag);
        }

        return $tags;
    }
}
