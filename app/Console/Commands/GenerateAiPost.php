<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Services\ImageGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateAiPost extends Command
{
    protected $signature = 'ai:generate-post
                            {--count=1 : Number of posts to generate}
                            {--category= : Specific category slug to generate post for}
                            {--author= : Author user ID (defaults to first admin)}
                            {--draft : Create as draft instead of publishing}
                            {--premium : Mark post as premium content}
                            {--free : Force free content (default is smart premium strategy)}
                            {--provider= : AI provider (groq, openai) - defaults to config}';

    protected $description = 'Generate AI-written blog posts using free Groq API';

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

        // Get the number of posts to generate
        $count = (int) $this->option('count');

        if ($count < 1) {
            $this->error('Count must be at least 1');
            return self::FAILURE;
        }

        $this->info("🤖 Starting AI post generation... (generating {$count} post(s))");
        $this->newLine();

        // Reset and load recently used images to prevent duplicates
        ImageGenerationService::resetUsedImages();
        $imageService = app(ImageGenerationService::class);
        $imageService->loadRecentlyUsedImages(30); // Check last 30 days

        $generatedPosts = [];
        $failedCount = 0;

        for ($i = 1; $i <= $count; $i++) {
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📝 Generating Post {$i} of {$count}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            try {
                $post = $this->generateSinglePost();
                $generatedPosts[] = $post;
                $this->newLine();
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("❌ Failed to generate post {$i}: " . $e->getMessage());
                Log::error("AI post generation failed for post {$i}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->newLine();

                // Continue to next post
                continue;
            }
        }

        // Summary
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Generation Summary");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Total requested: {$count}");
        $this->info("Successfully generated: " . count($generatedPosts));
        $this->info("Failed: {$failedCount}");

        if (count($generatedPosts) > 0) {
            $this->newLine();
            $this->info("Generated Posts:");
            foreach ($generatedPosts as $index => $post) {
                $num = $index + 1;
                $this->info("  {$num}. {$post->title} (ID: {$post->id})");
            }
        }

        return $failedCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function generateSinglePost(): Post
    {
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

        // Step 6: Determine premium strategy
        $isPremium = $this->determinePremiumStrategy();

        // Step 7: Generate featured image
        $this->info('🎨 Generating featured image...');
        $imageData = $this->generateFeaturedImage($postData['title'], $category->name);

        // Step 8: Create the post
        $post = Post::create([
            'title' => $postData['title'],
            'excerpt' => $postData['excerpt'],
            'content' => $postData['content'],
            'featured_image' => $imageData['url'] ?? null,
            'image_attribution' => $imageData['attribution'] ?? null,
            'author_id' => $author->id,
            'category_id' => $category->id,
            'status' => $this->option('draft') ? 'draft' : 'published',
            'published_at' => $this->option('draft') ? null : now(),
            'is_premium' => $isPremium,
            'is_featured' => $isPremium, // Featured premium content gets more visibility
            'allow_comments' => true,
            'seo_meta' => [
                'meta_title' => $postData['meta_title'],
                'meta_description' => $postData['meta_description'],
                'meta_keywords' => $postData['keywords'],
                'og_title' => $postData['title'],
                'og_description' => $postData['excerpt'],
                'og_image' => $imageData['url'] ?? null,
            ],
        ]);

        // Attach tags
        $post->tags()->attach($tags->pluck('id'));

        $status = $this->option('draft') ? 'draft' : 'published';
        $premiumLabel = $isPremium ? '💎 PREMIUM' : '🆓 FREE';

        $this->info("✅ Post created successfully!");
        $this->info("   ID: {$post->id}");
        $this->info("   Title: {$post->title}");
        $this->info("   Status: {$status}");
        $this->info("   Type: {$premiumLabel}");
        $this->info("   Featured: " . ($post->is_featured ? 'Yes' : 'No'));
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

        return $post;
    }

    private function selectTrendingTopic(): array
    {
        // Get recent topics to avoid duplication
        $recentTopics = Post::where('created_at', '>=', now()->subDays(30))
            ->pluck('title')
            ->toArray();

        $currentYear = now()->year;
        $currentMonth = now()->format('F');

        // Create comprehensive trending topics prompt with current context
        $trendingCategories = [
            'AI & Machine Learning' => [
                'Large Language Models (LLMs) like GPT, Claude, Gemini',
                'AI Agents and Autonomous Systems',
                'Generative AI for images, video, and code',
                'AI Ethics and Safety',
                'Fine-tuning and Prompt Engineering',
                'RAG (Retrieval Augmented Generation)',
                'AI in Production and MLOps',
            ],
            'Web Development' => [
                'React Server Components and Next.js 14+',
                'TypeScript best practices',
                'Modern CSS (Container Queries, CSS Grid, Tailwind)',
                'Web Performance Optimization',
                'Progressive Web Apps (PWA)',
                'WebAssembly and Edge Computing',
                'Jamstack and Static Site Generators',
            ],
            'DevOps & Cloud' => [
                'Kubernetes and Container Orchestration',
                'Infrastructure as Code (Terraform, Pulumi)',
                'CI/CD Pipelines and GitHub Actions',
                'Serverless Architecture',
                'Microservices vs Monoliths',
                'Cloud Security Best Practices',
                'Docker and Containerization',
            ],
            'Programming Languages' => [
                'Rust for Systems Programming',
                'Go for Backend Development',
                'Python for Data Science and AI',
                'Modern JavaScript/TypeScript features',
                'Functional Programming paradigms',
                'WebAssembly and WASI',
            ],
            'Software Architecture' => [
                'Domain-Driven Design (DDD)',
                'Event-Driven Architecture',
                'CQRS and Event Sourcing',
                'Clean Architecture patterns',
                'API Design and GraphQL',
                'System Design at Scale',
            ],
            'Mobile Development' => [
                'React Native and Cross-platform development',
                'Flutter best practices',
                'Mobile App Performance',
                'Native vs Hybrid approaches',
            ],
            'Data & Analytics' => [
                'Real-time Data Pipelines',
                'Data Warehousing strategies',
                'Stream Processing (Kafka, Flink)',
                'Data Visualization best practices',
            ],
            'Security' => [
                'Zero Trust Architecture',
                'API Security and OAuth 2.0',
                'Secure Coding Practices',
                'Vulnerability Management',
                'Privacy-First Development',
            ],
            'Emerging Tech' => [
                'WebGPU and Graphics Programming',
                'Edge Computing and IoT',
                'Blockchain and Web3 developments',
                'Quantum Computing basics',
            ],
        ];

        // Pick random category for variety
        $categoryKeys = array_keys($trendingCategories);
        $randomCategory = $categoryKeys[array_rand($categoryKeys)];
        $categoryTopics = $trendingCategories[$randomCategory];

        $recentTopicsList = !empty($recentTopics)
            ? "Topics to AVOID (already covered): " . implode(', ', array_slice($recentTopics, 0, 15))
            : "No recent topics to avoid.";

        $prompt = "You are a tech blog content strategist tracking current trends in {$currentMonth} {$currentYear}.

Generate ONE highly specific, trending blog post topic from the category: {$randomCategory}

Trending areas in this category:
" . implode("\n", array_map(fn($t) => "- $t", $categoryTopics)) . "

{$recentTopicsList}

Requirements:
1. Topic must be CURRENT and TRENDING in {$currentYear}
2. Be SPECIFIC - include version numbers, specific technologies, or frameworks
3. Focus on practical, actionable content developers want NOW
4. Use engaging titles with numbers, power words, or specific outcomes
5. Avoid generic or evergreen topics
6. Make it newsworthy or address current pain points

Examples of GOOD titles:
- \"Building Production-Ready RAG Applications with LangChain and Pinecone in 2024\"
- \"Next.js 14 Server Actions: Complete Guide to Type-Safe Server Mutations\"
- \"Migrating 100K Users from REST to GraphQL: Lessons Learned\"
- \"Rust vs Go for Microservices: Performance Benchmarks and Real-World Trade-offs\"

Return ONLY a JSON object:
{
  \"title\": \"Specific, trending, actionable title with technology/version\",
  \"category\": \"{$randomCategory}\"
}";

        try {
            $response = $this->callOpenAI([
                [
                    'role' => 'system',
                    'content' => 'You are an expert tech content strategist who identifies trending, high-value topics that developers are actively searching for. You stay current with latest tech trends and focus on practical, specific content.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ], 200, 0.9, true); // Higher temperature for creativity, JSON mode

            // Parse response
            $topic = json_decode($response, true);
            if (!$topic && preg_match('/\{.*\}/s', $response, $matches)) {
                $topic = json_decode($matches[0], true);
            }

            if ($topic && isset($topic['title']) && isset($topic['category'])) {
                return $topic;
            }
        } catch (\Exception $e) {
            Log::warning('Trending topic generation failed, using fallback', [
                'error' => $e->getMessage()
            ]);
        }

        // Improved fallback topics with variety
        $fallbackTopics = [
            ['title' => 'Building Scalable Microservices with Kubernetes in 2024', 'category' => 'DevOps & Cloud'],
            ['title' => 'Modern React Patterns: Server Components and Suspense Deep Dive', 'category' => 'Web Development'],
            ['title' => 'Production-Ready LLM Applications: From Prototype to Scale', 'category' => 'AI & Machine Learning'],
            ['title' => 'TypeScript 5.0: Advanced Type Patterns for Better Code Safety', 'category' => 'Programming Languages'],
            ['title' => 'API Security in 2024: OAuth 2.1 and Beyond', 'category' => 'Security'],
        ];

        return $fallbackTopics[array_rand($fallbackTopics)];
    }

    private function generatePostContent(array $topic): array
    {
        // Determine if this should be premium content
        $isPremium = $this->option('premium') || (!$this->option('free') && rand(1, 100) <= 70); // 70% premium by default

        $conversionStrategy = $isPremium ? $this->getPremiumContentStrategy() : '';

        $prompt = "Write a high-value, conversion-focused blog post about: {$topic['title']}

CONTENT STRATEGY:
{$conversionStrategy}

Requirements:
- 1200-1800 words of exceptional quality
- Start with a compelling hook that highlights reader's pain point
- Include an engaging introduction that promises transformation
- Use clear headings and subheadings (use ## for h2, ### for h3)
- Provide actionable insights and practical examples
- Use psychological triggers (FOMO, authority, social proof, urgency)
- Include specific numbers, statistics, or case studies
- Create curiosity gaps that make readers want to learn more
- End with a strong conclusion that emphasizes the value of deep expertise
- Use markdown formatting
- Professional yet engaging tone

CONTENT STRUCTURE:
1. Hook (pain point or opportunity)
2. Promise (what they'll learn)
3. Context (why this matters now)
4. Main content (valuable insights)
5. Advanced section (deeper techniques - hint at premium depth)
6. Conclusion (emphasize value of expertise, subtle CTA for premium access)

Also provide:
- A compelling 150-200 character excerpt that creates curiosity
- Meta title (60 characters max) - benefit-driven
- Meta description (155 characters max) - value proposition
- 5-7 high-intent keywords
- 3-5 tags

Return response in this JSON format:
{
  \"title\": \"Benefit-driven title with numbers or power words\",
  \"content\": \"Full markdown content with conversion strategy\",
  \"excerpt\": \"Curiosity-driven excerpt\",
  \"meta_title\": \"SEO title with benefit\",
  \"meta_description\": \"Value proposition description\",
  \"keywords\": [\"keyword1\", \"keyword2\"],
  \"tags\": [\"tag1\", \"tag2\"]
}";

        $response = $this->callOpenAI([
            [
                'role' => 'system',
                'content' => 'You are an expert tech blogger who writes engaging, informative articles about technology, programming, and web development. You MUST return valid JSON with properly escaped strings.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 3000, 0.7, true); // Enable JSON mode

        // Parse JSON response - try multiple approaches
        $postData = $this->parseAIResponse($response);

        // Validate required fields
        $required = ['title', 'content', 'excerpt', 'meta_title', 'meta_description', 'keywords', 'tags'];
        foreach ($required as $field) {
            if (!isset($postData[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        return $postData;
    }

    private function parseAIResponse(string $response): array
    {
        // Try 1: Extract JSON block (between ```json and ``` or just curly braces)
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
            $jsonStr = $matches[1];
        } elseif (preg_match('/\{.*\}/s', $response, $matches)) {
            $jsonStr = $matches[0];
        } else {
            throw new \Exception('No JSON found in AI response');
        }

        // Try 2: Decode with error handling
        $postData = json_decode($jsonStr, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $postData;
        }

        // Try 3: If JSON has control character errors, try to fix common issues
        // Remove BOM and zero-width characters
        $jsonStr = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonStr);

        $postData = json_decode($jsonStr, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $postData;
        }

        // Try 4: Use JSON_INVALID_UTF8_IGNORE flag
        $postData = json_decode($jsonStr, true, 512, JSON_INVALID_UTF8_IGNORE);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $postData;
        }

        // Log the problematic response for debugging
        Log::error('Failed to parse AI JSON response', [
            'error' => json_last_error_msg(),
            'response_preview' => substr($response, 0, 500)
        ]);

        throw new \Exception('Invalid JSON in AI response: ' . json_last_error_msg() . '. Check logs for details.');
    }

    private function cleanupPostData(array $postData): array
    {
        // Clean up the content to ensure it's valid
        if (isset($postData['content'])) {
            $postData['content'] = trim($postData['content']);
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

    private function callOpenAI(array $messages, int $maxTokens = 2000, float $temperature = 0.7, bool $jsonMode = false): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        // Enable JSON mode for supported providers
        if ($jsonMode && $this->provider === 'groq') {
            $payload['response_format'] = ['type' => 'json_object'];
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

    private function determinePremiumStrategy(): bool
    {
        // If explicitly set, use that
        if ($this->option('premium')) {
            return true;
        }

        if ($this->option('free')) {
            return false;
        }

        // Smart strategy: 70% premium, 30% free
        // This creates FOMO - users see valuable content they can't access
        // which drives subscriptions
        $random = rand(1, 100);

        // 70% chance of premium content
        return $random <= 70;
    }

    private function getPremiumContentStrategy(): string
    {
        return "
PREMIUM CONTENT STRATEGY:
This will be PREMIUM content requiring subscription. Your goal is to:

1. **Create High Perceived Value**:
   - Present advanced techniques and insider knowledge
   - Include implementation details and code examples
   - Share real-world case studies and results
   - Provide step-by-step frameworks

2. **Strategic Teaser (First 30%)**:
   - First few paragraphs are engaging and valuable
   - Hook them with a compelling problem statement
   - Show what's possible (the transformation)
   - Build credibility with initial insights

3. **Premium Content Markers**:
   - Use phrases like: 'Advanced techniques', 'Deep dive', 'Complete guide'
   - Include: 'Production-ready code', 'Battle-tested strategies'
   - Mention: 'Step-by-step implementation', 'Avoiding common pitfalls'

4. **Psychological Triggers**:
   - FOMO: 'Most developers miss this critical step...'
   - Authority: 'From years of production experience...'
   - Social Proof: 'Used by leading tech companies...'
   - Urgency: 'Essential for modern applications...'
   - Exclusivity: 'Advanced techniques not found elsewhere...'

5. **Content Depth Indicators**:
   - Promise specific, actionable outcomes
   - Include technical depth that shows expertise
   - Reference advanced concepts and optimizations
   - Provide complete, copy-paste solutions

The content should make free users think: 'This looks incredibly valuable, I need full access!'
";
    }

    private function generateFeaturedImage(string $title, string $category): ?array
    {
        try {
            $imageService = app(ImageGenerationService::class);

            if (!$imageService->isAvailable()) {
                $this->warn('   ⚠️  No image generation service configured. Skipping image.');
                return null;
            }

            $this->info('   🎨 Using: ' . $imageService->getProvider());
            $imageData = $imageService->generateFeaturedImage($title, $category);

            if ($imageData && isset($imageData['url'])) {
                $this->info('   ✅ Image generated successfully!');
                if ($imageData['attribution']) {
                    $this->info('   📸 Photo by: ' . $imageData['attribution']['photographer_name']);
                }
                return $imageData;
            }

            $this->warn('   ⚠️  Failed to generate image.');
            return null;
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Image generation error: ' . $e->getMessage());
            Log::warning('Featured image generation failed', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
