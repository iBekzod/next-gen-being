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
                            {--provider= : AI provider (groq, openai) - defaults to config}
                            {--series= : Generate tutorial series with specified number of parts (e.g., --series=5)}
                            {--series-title= : Custom title for the tutorial series}';

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

        // Check if generating a series
        $seriesParts = (int) $this->option('series');
        $isGeneratingSeries = $seriesParts > 0;

        if ($isGeneratingSeries) {
            return $this->generateSeries($seriesParts);
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

                // Add delay between posts to avoid rate limiting (except for last post)
                if ($i < $count) {
                    $delay = $this->provider === 'groq' ? 5 : 2; // Longer delay for Groq
                    $this->info("⏳ Waiting {$delay} seconds to avoid rate limits...");
                    sleep($delay);
                    $this->newLine();
                }
            } catch (\Exception $e) {
                $failedCount++;
                $errorMessage = $e->getMessage();
                $this->error("❌ Failed to generate post {$i}: " . $errorMessage);

                // Check if it's a rate limit error
                if (str_contains($errorMessage, 'rate_limit_exceeded') || str_contains($errorMessage, 'Rate limit')) {
                    // Extract wait time from error message if available
                    preg_match('/try again in (\d+\.?\d*)s/i', $errorMessage, $matches);
                    $waitTime = isset($matches[1]) ? ceil((float)$matches[1]) + 1 : 10;

                    $this->warn("⏸️  Rate limit hit. Waiting {$waitTime} seconds before retrying...");
                    sleep($waitTime);

                    // Retry this post
                    try {
                        $this->info("🔄 Retrying post {$i}...");
                        $post = $this->generateSinglePost();
                        $generatedPosts[] = $post;
                        $failedCount--; // Decrement since retry succeeded
                        $this->info("✅ Retry successful!");
                        $this->newLine();

                        // Add delay after successful retry
                        if ($i < $count) {
                            $delay = $this->provider === 'groq' ? 5 : 2;
                            $this->info("⏳ Waiting {$delay} seconds to avoid rate limits...");
                            sleep($delay);
                            $this->newLine();
                        }
                    } catch (\Exception $retryError) {
                        $this->error("❌ Retry failed: " . $retryError->getMessage());
                    }
                } else {
                    // Log non-rate-limit errors
                    Log::error("AI post generation failed for post {$i}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                $this->newLine();
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

Generate ONE highly specific, practical blog post topic from the category: {$randomCategory}

Trending areas in this category:
" . implode("\n", array_map(fn($t) => "- $t", $categoryTopics)) . "

{$recentTopicsList}

Requirements:
1. Topic must be CURRENT and RELEVANT in {$currentYear}
2. Be SPECIFIC - include actual version numbers and technologies when applicable
3. Focus on practical, educational content developers need
4. Use REALISTIC, professional titles - NO clickbait or exaggerated claims
5. AVOID phrases like: '10x Faster', '20x Better', '99.99% Uptime', 'Turbocharge', 'Unlock'
6. Focus on learning, understanding, and practical implementation
7. Make it educational and actionable

Examples of GOOD titles:
- \"Building Production-Ready RAG Applications with LangChain and Pinecone\"
- \"Understanding Next.js 14 Server Actions: A Complete Guide\"
- \"Migrating from REST to GraphQL: Practical Lessons and Trade-offs\"
- \"Comparing Rust and Go for Microservices: Real-World Considerations\"
- \"Implementing Event-Driven Architecture with Apache Kafka\"
- \"A Practical Guide to Domain-Driven Design in .NET\"

Examples of BAD titles (AVOID):
- \"10x Faster Performance with [Technology]\" (unrealistic claims)
- \"Turbocharge Your App with [Tool]\" (clickbait language)
- \"Unlock 99.99% Uptime\" (exaggerated guarantees)
- \"Master [Complex Topic] in 10 Minutes\" (unrealistic timeframes)

Return ONLY a JSON object:
{
  \"title\": \"Professional, educational, specific title without clickbait\",
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
        $advancedTipsExtra = $isPremium ? '- Hint at deeper premium content' : '';
        $conclusionExtra = $isPremium ? '- Subtle mention of deeper expertise available' : '';

        $prompt = "Write a comprehensive, professional, and highly educational blog post about: {$topic['title']}

CONTENT STRATEGY:
{$conversionStrategy}

⚠️ CRITICAL RULES - MUST FOLLOW:
1. NO clickbait or exaggerated claims (avoid: '10x faster', 'turbocharge', 'unlock', '99.99%')
2. NO unrealistic performance numbers without real benchmarks
3. Focus on REALISTIC expectations and honest trade-offs
4. Be EDUCATIONAL first, promotional never
5. Provide REAL working code examples with explanations
6. Cite actual tools, versions, and documentation

🎯 ENGAGEMENT PRINCIPLES (CRITICAL):

1. **START WITH A STORY OR REAL SCENARIO**
   - Open with a relatable developer problem or real-world scenario
   - Use \"You\" language to connect personally
   - Example: \"You've just deployed your app. 2 AM. Your phone buzzes. The database is on fire...\"

2. **BE EXTREMELY PRACTICAL**
   - Every section must have actionable takeaways
   - Include real code examples (working, copy-pasteable code)
   - Add \"Quick Win\" boxes with immediate actions
   - Include \"⚡ Quick Win:\", \"💡 Pro Tip:\", \"⚠️ Common Mistake:\" callouts

3. **USE STORYTELLING**
   - Share real scenarios, case studies, or experiences
   - Include before/after comparisons with metrics
   - Add relatable developer pain points
   - Example: \"When Airbnb faced this, they reduced load time by 43%\"

4. **MAKE IT SCANNABLE**
   - Use short paragraphs (2-4 sentences max)
   - Add bullet points and numbered lists frequently
   - Include visual breaks with emojis for key points (sparingly)
   - Clear, benefit-driven subheadings

5. **PROVIDE REAL VALUE**
   - Code snippets that actually work
   - Specific numbers, metrics, benchmarks
   - Links to tools, libraries, documentation
   - Step-by-step walkthroughs
   - Comparison tables when relevant

6. **BE CONVERSATIONAL**
   - Write like talking to a friend over coffee
   - Use contractions (you'll, don't, can't)
   - Ask rhetorical questions
   - Share opinions and recommendations
   - Inject personality (but stay professional)

CONTENT STRUCTURE (2000-3000 words for comprehensive, in-depth coverage):

## Opening Hook (150-200 words)
- Start with a relatable scenario or real-world context (NO shocking stats unless verified)
- Identify the pain point or opportunity clearly
- Promise specific, realistic, achievable learning outcomes
- Make it personal and engaging without hype

## Why This Matters (200-250 words)
- Current state of the problem in the industry
- Why this topic is relevant NOW (with context)
- What you'll learn (specific, achievable benefits)
- Who this is for and what prerequisites exist
- Set realistic expectations

## Background/Context (250-350 words)
- Technical background and relevant history
- How this technology/approach evolved
- Current ecosystem, tools, and frameworks
- Real-world examples with specific contexts
- Industry adoption level and maturity

## Core Concepts (400-600 words)
- Fundamental principles explained clearly
- Key terminology and definitions
- Architecture overview and design patterns
- How components fit together
- Important trade-offs to understand

## Practical Implementation (1000-1400 words) - MAIN CONTENT
Break into 4-6 comprehensive sections with:
- Detailed step-by-step approach with reasoning
- **Complete working code examples** (properly formatted with thorough comments)
- Real configuration examples and setup instructions
- Realistic metrics and benchmarks (NO exaggeration)
- Honest comparisons and trade-offs
- Common pitfalls and how to avoid them

Example structure:
### Step 1: [Specific Technique/Setup]
[Thorough explanation with context and reasoning]
```language
// Complete working code example
// with detailed comments explaining each part
```
💡 **Pro Tip:** [Insider insight based on real experience]

⚡ **Quick Win:** [Immediate, actionable step they can take]

⚠️ **Common Mistake:** [Real pitfall with explanation]

## Advanced Considerations (300-400 words)
- Production-ready optimizations
- Scaling considerations and limitations
- Security implications
- Edge cases from real experience
- Performance tuning with measurable results
- Monitoring and debugging strategies
{$advancedTipsExtra}

## Real-World Application (200-300 words)
- How companies use this in production
- Success stories with realistic metrics
- When to use vs when NOT to use
- Alternative solutions and trade-offs
- Cost and resource considerations

## Conclusion (150-200 words)
- Recap key takeaways (5-7 clear bullets)
- Clear, actionable next steps
- Resources for further learning
- Honest assessment of difficulty and time
- Realistic expectations for mastery
{$conclusionExtra}

FORMATTING RULES:
- Use ## for main headings, ### for subheadings
- Add code blocks with language specification: ```javascript, ```python, etc.
- Use **bold** for key terms, *italics* for emphasis
- Add > blockquotes for important notes
- Use tables for comparisons
- Keep paragraphs short and punchy

TONE:
- Professional and educational
- Helpful and supportive
- Enthusiastic but realistic about technology
- ALWAYS honest about trade-offs and limitations
- Like a senior developer mentoring with real-world experience
- NO hype, NO clickbait, NO exaggeration

Also provide:
- **Excerpt**: Educational hook with specific benefit (150-200 chars, NO clickbait)
- **Meta title**: Professional title with specifics (60 chars max, NO hype)
- **Meta description**: Clear value prop with realistic expectations (155 chars max)
- **Keywords**: 5-7 high-intent, searchable technical terms
- **Tags**: 3-5 relevant, specific topic tags

Return ONLY this JSON (ensure proper escaping):
{
  \"title\": \"[Professional, educational title - NO clickbait phrases like '10x', 'turbocharge', 'unlock']\",
  \"content\": \"[Full markdown content with stories, code, tips]\",
  \"excerpt\": \"[Specific benefit that creates curiosity]\",
  \"meta_title\": \"[SEO title with benefit]\",
  \"meta_description\": \"[Value proposition with outcome]\",
  \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\", \"keyword4\", \"keyword5\"],
  \"tags\": [\"tag1\", \"tag2\", \"tag3\"]
}";

        $response = $this->callOpenAI([
            [
                'role' => 'system',
                'content' => 'You are a senior software engineer and technical educator known for writing comprehensive, professional, and highly practical content. Your articles are educational, honest, and packed with real working code examples and actionable insights. You NEVER use clickbait or exaggerated claims. You write clear, realistic, professional content that developers trust. You MUST return valid JSON with properly escaped strings.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 5000, 0.7, true); // Enable JSON mode, higher tokens for comprehensive content, lower temp for professionalism

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

    private function generateSeries(int $parts): int
    {
        if ($parts < 2) {
            $this->error('Series must have at least 2 parts');
            return self::FAILURE;
        }

        if ($parts > 10) {
            $this->error('Series cannot have more than 10 parts (to avoid rate limits)');
            return self::FAILURE;
        }

        $this->info("📚 Starting AI Tutorial Series generation... (generating {$parts} part series)");
        $this->newLine();

        // Reset and load recently used images to prevent duplicates
        ImageGenerationService::resetUsedImages();
        $imageService = app(ImageGenerationService::class);
        $imageService->loadRecentlyUsedImages(30);

        // Step 1: Generate series outline
        $this->info('📝 Generating series outline and topic...');
        $seriesOutline = $this->generateSeriesOutline($parts);

        if (!$seriesOutline) {
            $this->error('Failed to generate series outline');
            return self::FAILURE;
        }

        $this->info("✅ Series Topic: {$seriesOutline['title']}");
        $this->newLine();

        $generatedPosts = [];
        $failedCount = 0;
        $seriesSlug = Str::slug($seriesOutline['title']);

        // Step 2: Generate each part
        for ($i = 1; $i <= $parts; $i++) {
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📖 Generating Part {$i} of {$parts}: {$seriesOutline['parts'][$i-1]['title']}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            try {
                $post = $this->generateSeriesPart($seriesOutline, $i, $parts);
                $generatedPosts[] = $post;
                $this->newLine();

                // Add delay between posts
                if ($i < $parts) {
                    $delay = $this->provider === 'groq' ? 5 : 2;
                    $this->info("⏳ Waiting {$delay} seconds...");
                    sleep($delay);
                    $this->newLine();
                }
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("❌ Failed to generate part {$i}: " . $e->getMessage());

                if (str_contains($e->getMessage(), 'rate_limit_exceeded')) {
                    preg_match('/try again in (\d+\.?\d*)s/i', $e->getMessage(), $matches);
                    $waitTime = isset($matches[1]) ? ceil((float)$matches[1]) + 1 : 10;
                    $this->warn("⏸️  Rate limit hit. Waiting {$waitTime} seconds...");
                    sleep($waitTime);

                    try {
                        $this->info("🔄 Retrying part {$i}...");
                        $post = $this->generateSeriesPart($seriesOutline, $i, $parts);
                        $generatedPosts[] = $post;
                        $failedCount--;
                        $this->info("✅ Retry successful!");
                    } catch (\Exception $retryError) {
                        $this->error("❌ Retry failed: " . $retryError->getMessage());
                    }
                }
                $this->newLine();
            }
        }

        // Summary
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Series Generation Summary");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Series: {$seriesOutline['title']}");
        $this->info("Total parts: {$parts}");
        $this->info("Successfully generated: " . count($generatedPosts));
        $this->info("Failed: {$failedCount}");

        if (count($generatedPosts) > 0) {
            $this->newLine();
            $this->info("Generated Series Parts:");
            foreach ($generatedPosts as $index => $post) {
                $partNum = $index + 1;
                $this->info("  Part {$partNum}. {$post->title} (ID: {$post->id})");
            }
        }

        return $failedCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function generateSeriesOutline(int $parts): ?array
    {
        $currentYear = now()->year;
        $customTitle = $this->option('series-title');

        $prompt = $customTitle
            ? "Create a detailed {$parts}-part tutorial series outline for: \"{$customTitle}\""
            : "Create a detailed {$parts}-part tutorial series outline for a practical, trending tech topic in {$currentYear}";

        $prompt .= "

Create a comprehensive, realistic tutorial series that takes readers from fundamentals to production-ready implementation.

CRITICAL REQUIREMENTS:
- Series title must be PROFESSIONAL and EDUCATIONAL (NO clickbait like '10x Faster', 'Turbocharge', 'Unlock')
- Each part builds logically on the previous one
- Clear learning progression from basics to advanced
- Focus on real-world, production-ready applications
- Specific technologies with actual version numbers
- Realistic time and complexity expectations
- Each part should be substantial (2000-3000 words worth of content)

GOOD series examples:
- \"Building Production-Ready Microservices with Docker and Kubernetes\"
- \"Complete Guide to Real-Time Data Pipelines with Apache Kafka\"
- \"Understanding Domain-Driven Design: A Practical Implementation Guide\"

BAD series examples (AVOID):
- \"10x Your App Performance\" (unrealistic claims)
- \"Turbocharge Your Development\" (clickbait)
- \"Unlock Ultimate Productivity\" (vague and hypey)

Return ONLY this JSON:
{
  \"title\": \"Series title (e.g., 'Building Production-Ready Microservices with Docker & Kubernetes')\",
  \"description\": \"2-3 sentence series description\",
  \"category\": \"Main category\",
  \"parts\": [
    {
      \"part\": 1,
      \"title\": \"Part 1 title (e.g., 'Setting Up Your Development Environment')\",
      \"focus\": \"What this part teaches\"
    },
    ... ({$parts} parts total)
  ]
}";

        try {
            $response = $this->callOpenAI([
                ['role' => 'system', 'content' => 'You are an expert technical educator who creates comprehensive, realistic tutorial series. You NEVER use clickbait or exaggerated claims. You focus on practical, professional, educational content. You MUST return valid JSON.'],
                ['role' => 'user', 'content' => $prompt]
            ], 1000, 0.7, true); // Lower temperature for more consistent, professional output

            $outline = json_decode($response, true);
            if (!$outline && preg_match('/\{.*\}/s', $response, $matches)) {
                $outline = json_decode($matches[0], true);
            }

            if ($outline && isset($outline['title']) && isset($outline['parts']) && count($outline['parts']) === $parts) {
                return $outline;
            }
        } catch (\Exception $e) {
            Log::error('Series outline generation failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function generateSeriesPart(array $seriesOutline, int $partNumber, int $totalParts): Post
    {
        $partInfo = $seriesOutline['parts'][$partNumber - 1];
        $seriesSlug = Str::slug($seriesOutline['title']);

        // Build context from previous parts
        $previousPartsContext = '';
        if ($partNumber > 1) {
            $previousPartsContext = "\n\nPREVIOUS PARTS COVERED:\n";
            for ($i = 0; $i < $partNumber - 1; $i++) {
                $previousPartsContext .= "Part " . ($i + 1) . ": " . $seriesOutline['parts'][$i]['title'] . " - " . $seriesOutline['parts'][$i]['focus'] . "\n";
            }
        }

        // Modified topic for series context
        $topic = [
            'title' => "Part {$partNumber}/{$totalParts}: {$partInfo['title']} | {$seriesOutline['title']}",
            'category' => $seriesOutline['category'] ?? 'Technology'
        ];

        // Generate content with series context
        $this->info("📊 Analyzing part {$partNumber} requirements...");
        $isPremium = $this->option('premium') || (!$this->option('free') && rand(1, 100) <= 70);

        $postData = $this->generateSeriesPartContent($topic, $partInfo, $partNumber, $totalParts, $previousPartsContext, $isPremium);

        // Get other required data
        $category = $this->getCategory();
        $author = $this->getAuthor();
        $tags = $this->getOrCreateTags($postData['tags']);

        // Generate image
        $this->info('🎨 Generating featured image...');
        $imageData = $this->generateFeaturedImage($postData['title'], $category->name);

        // Create post with series data
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
            'is_featured' => $partNumber === 1, // Feature first part
            'allow_comments' => true,
            'seo_meta' => [
                'meta_title' => $postData['meta_title'],
                'meta_description' => $postData['meta_description'],
                'meta_keywords' => $postData['keywords'],
                'og_title' => $postData['title'],
                'og_description' => $postData['excerpt'],
                'og_image' => $imageData['url'] ?? null,
            ],
            'series_title' => $seriesOutline['title'],
            'series_slug' => $seriesSlug,
            'series_part' => $partNumber,
            'series_total_parts' => $totalParts,
            'series_description' => $seriesOutline['description'] ?? null,
        ]);

        $post->tags()->attach($tags->pluck('id'));

        $status = $this->option('draft') ? 'draft' : 'published';
        $premiumLabel = $isPremium ? '💎 PREMIUM' : '🆓 FREE';

        $this->info("✅ Part {$partNumber} created successfully!");
        $this->info("   ID: {$post->id}");
        $this->info("   Title: {$post->title}");
        $this->info("   Status: {$status}");
        $this->info("   Type: {$premiumLabel}");
        $this->info("   Series: {$seriesOutline['title']} ({$partNumber}/{$totalParts})");

        return $post;
    }

    private function generateSeriesPartContent(array $topic, array $partInfo, int $partNumber, int $totalParts, string $previousPartsContext, bool $isPremium): array
    {
        $conversionStrategy = $isPremium ? $this->getPremiumContentStrategy() : '';
        $advancedTipsExtra = $isPremium ? '- Hint at deeper premium content' : '';
        $conclusionExtra = $isPremium ? '- Subtle mention of deeper expertise available' : '';

        $seriesContext = $partNumber === 1
            ? "This is PART 1 - the foundation. Introduce the series and set up readers for success."
            : "This is PART {$partNumber} of {$totalParts}. Build on previous knowledge.{$previousPartsContext}";

        $nextPartTeaser = $partNumber < $totalParts
            ? "\nEnd with a teaser for the next part in the series."
            : "\nThis is the FINAL part - wrap up the entire series with a complete working example.";

        $prompt = "Write Part {$partNumber} of a {$totalParts}-part tutorial series about: {$topic['title']}

SERIES CONTEXT:
{$seriesContext}

THIS PART FOCUSES ON: {$partInfo['focus']}

{$nextPartTeaser}

" . substr($this->getBaseContentPrompt($conversionStrategy, $advancedTipsExtra, $conclusionExtra, $isPremium), strpos($this->getBaseContentPrompt($conversionStrategy, $advancedTipsExtra, $conclusionExtra, $isPremium), '🎯 ENGAGEMENT PRINCIPLES'));

        $response = $this->callOpenAI([
            [
                'role' => 'system',
                'content' => 'You are a senior software engineer and technical educator creating comprehensive tutorial series. Each part must be clear, practical, and build properly on previous parts. You NEVER use clickbait or exaggerated performance claims. You write professional, realistic, educational content. You MUST return valid JSON with properly escaped strings.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 5000, 0.7, true); // Higher tokens for depth, lower temp for consistency

        return $this->parseAIResponse($response);
    }

    private function getBaseContentPrompt(string $conversionStrategy, string $advancedTipsExtra, string $conclusionExtra, bool $isPremium): string
    {
        return "Write a comprehensive, professional, and highly educational blog post

CONTENT STRATEGY:
{$conversionStrategy}

⚠️ CRITICAL RULES - MUST FOLLOW:
1. NO clickbait or exaggerated claims (avoid: '10x faster', 'turbocharge', 'unlock', '99.99%')
2. NO unrealistic performance numbers without real benchmarks
3. Focus on REALISTIC expectations and honest trade-offs
4. Be EDUCATIONAL first, promotional never
5. Provide REAL working code examples with explanations
6. Cite actual tools, versions, and documentation

🎯 ENGAGEMENT PRINCIPLES (CRITICAL):

1. **START WITH A STORY OR REAL SCENARIO**
   - Open with a relatable developer problem or real-world scenario
   - Use \"You\" language to connect personally
   - Example: \"You've just deployed your app. 2 AM. Your phone buzzes. The database is on fire...\"

2. **BE EXTREMELY PRACTICAL**
   - Every section must have actionable takeaways
   - Include real code examples (working, copy-pasteable code)
   - Add \"Quick Win\" boxes with immediate actions
   - Include \"⚡ Quick Win:\", \"💡 Pro Tip:\", \"⚠️ Common Mistake:\" callouts

3. **USE STORYTELLING**
   - Share real scenarios, case studies, or experiences
   - Include before/after comparisons with metrics
   - Add relatable developer pain points
   - Example: \"When Airbnb faced this, they reduced load time by 43%\"

4. **MAKE IT SCANNABLE**
   - Use short paragraphs (2-4 sentences max)
   - Add bullet points and numbered lists frequently
   - Include visual breaks with emojis for key points (sparingly)
   - Clear, benefit-driven subheadings

5. **PROVIDE REAL VALUE**
   - Code snippets that actually work
   - Specific numbers, metrics, benchmarks
   - Links to tools, libraries, documentation
   - Step-by-step walkthroughs
   - Comparison tables when relevant

6. **BE CONVERSATIONAL**
   - Write like talking to a friend over coffee
   - Use contractions (you'll, don't, can't)
   - Ask rhetorical questions
   - Share opinions and recommendations
   - Inject personality (but stay professional)

CONTENT STRUCTURE (2000-3000 words for comprehensive, in-depth coverage):

## Opening Hook (150-200 words)
- Start with a relatable scenario or real-world context (NO shocking stats unless verified)
- Identify the pain point or opportunity clearly
- Promise specific, realistic, achievable learning outcomes
- Make it personal and engaging without hype

## Why This Matters (200-250 words)
- Current state of the problem in the industry
- Why this topic is relevant NOW (with context)
- What you'll learn (specific, achievable benefits)
- Who this is for and what prerequisites exist
- Set realistic expectations

## Background/Context (250-350 words)
- Technical background and relevant history
- How this technology/approach evolved
- Current ecosystem, tools, and frameworks
- Real-world examples with specific contexts
- Industry adoption level and maturity

## Core Concepts (400-600 words)
- Fundamental principles explained clearly
- Key terminology and definitions
- Architecture overview and design patterns
- How components fit together
- Important trade-offs to understand

## Practical Implementation (1000-1400 words) - MAIN CONTENT
Break into 4-6 comprehensive sections with:
- Detailed step-by-step approach with reasoning
- **Complete working code examples** (properly formatted with thorough comments)
- Real configuration examples and setup instructions
- Realistic metrics and benchmarks (NO exaggeration)
- Honest comparisons and trade-offs
- Common pitfalls and how to avoid them

Example structure:
### Step 1: [Specific Technique/Setup]
[Thorough explanation with context and reasoning]
```language
// Complete working code example
// with detailed comments explaining each part
```
💡 **Pro Tip:** [Insider insight based on real experience]

⚡ **Quick Win:** [Immediate, actionable step they can take]

⚠️ **Common Mistake:** [Real pitfall with explanation]

## Advanced Considerations (300-400 words)
- Production-ready optimizations
- Scaling considerations and limitations
- Security implications
- Edge cases from real experience
- Performance tuning with measurable results
- Monitoring and debugging strategies
{$advancedTipsExtra}

## Real-World Application (200-300 words)
- How companies use this in production
- Success stories with realistic metrics
- When to use vs when NOT to use
- Alternative solutions and trade-offs
- Cost and resource considerations

## Conclusion (150-200 words)
- Recap key takeaways (5-7 clear bullets)
- Clear, actionable next steps
- Resources for further learning
- Honest assessment of difficulty and time
- Realistic expectations for mastery
{$conclusionExtra}

FORMATTING RULES:
- Use ## for main headings, ### for subheadings
- Add code blocks with language specification: ```javascript, ```python, etc.
- Use **bold** for key terms, *italics* for emphasis
- Add > blockquotes for important notes
- Use tables for comparisons
- Keep paragraphs short and punchy

TONE:
- Professional and educational
- Helpful and supportive
- Enthusiastic but realistic about technology
- ALWAYS honest about trade-offs and limitations
- Like a senior developer mentoring with real-world experience
- NO hype, NO clickbait, NO exaggeration

Also provide:
- **Excerpt**: Educational hook with specific benefit (150-200 chars, NO clickbait)
- **Meta title**: Professional title with specifics (60 chars max, NO hype)
- **Meta description**: Clear value prop with realistic expectations (155 chars max)
- **Keywords**: 5-7 high-intent, searchable technical terms
- **Tags**: 3-5 relevant, specific topic tags

Return ONLY this JSON (ensure proper escaping):
{
  \"title\": \"[Professional, educational title - NO clickbait phrases like '10x', 'turbocharge', 'unlock']\",
  \"content\": \"[Full markdown content with stories, code, tips]\",
  \"excerpt\": \"[Specific benefit that creates curiosity]\",
  \"meta_title\": \"[SEO title with benefit]\",
  \"meta_description\": \"[Value proposition with outcome]\",
  \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\", \"keyword4\", \"keyword5\"],
  \"tags\": [\"tag1\", \"tag2\", \"tag3\"]
}";
    }
}
