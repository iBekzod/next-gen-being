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

        // Step 7: Load recently used images to prevent duplicates
        $imageService = app(ImageGenerationService::class);
        $imageService->loadRecentlyUsedImages(30);

        // Step 8: Generate featured image
        $this->info('🎨 Generating featured image...');
        $imageData = $this->generateFeaturedImage($postData['title'], $category->name);

        // Step 9: Create the post
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
        // Get recent topics to avoid duplication - include keywords for better matching
        $recentPosts = Post::where('created_at', '>=', now()->subDays(60)) // Extended to 60 days
            ->select('title', 'content', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $recentTopics = $recentPosts->pluck('title')->toArray();

        // Extract ALL technology keywords from recent posts (comprehensive)
        $recentKeywords = [];
        $commonTechKeywords = [
            'Laravel', 'React', 'Vue', 'Angular', 'Node', 'Python', 'Django', 'Flask',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'Redis', 'PostgreSQL', 'MySQL',
            'MongoDB', 'GraphQL', 'REST', 'API', 'Microservices', 'Serverless',
            'TypeScript', 'JavaScript', 'PHP', 'Java', 'Go', 'Rust', 'Ruby',
            'Kafka', 'RabbitMQ', 'Nginx', 'Apache', 'Elasticsearch', 'Terraform',
            'CI/CD', 'GitHub', 'GitLab', 'Jenkins', 'Next.js', 'Nuxt', 'Gatsby',
            'TailwindCSS', 'Bootstrap', 'Sass', 'Webpack', 'Vite', 'Rollup'
        ];

        foreach ($recentPosts as $post) {
            $text = $post->title . ' ' . substr($post->content, 0, 500);

            // Extract technology names (capital letter words/phrases)
            preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/', $text, $matches);
            if (!empty($matches[0])) {
                $recentKeywords = array_merge($recentKeywords, $matches[0]);
            }

            // Also check for common tech keywords (case-insensitive)
            foreach ($commonTechKeywords as $tech) {
                if (stripos($text, $tech) !== false) {
                    $recentKeywords[] = $tech;
                }
            }
        }

        $recentKeywords = array_unique($recentKeywords);
        $recentKeywords = array_values(array_filter($recentKeywords, function($keyword) {
            // Filter out common words that aren't technologies
            $excludeWords = ['The', 'This', 'That', 'With', 'From', 'Your', 'How', 'Why', 'What', 'When', 'Where', 'Using', 'Build', 'Creating'];
            return !in_array($keyword, $excludeWords) && strlen($keyword) > 2;
        }));

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

        $recentKeywordsList = !empty($recentKeywords)
            ? "🚫 AVOID THESE TECHNOLOGIES (already covered): " . implode(', ', array_slice($recentKeywords, 0, 30))
            : "No recent technology keywords.";

        $prompt = "You are a tech blog content strategist tracking current trends in {$currentMonth} {$currentYear}.

Generate ONE highly specific, practical blog post topic from the category: {$randomCategory}

Trending areas in this category:
" . implode("\n", array_map(fn($t) => "- $t", $categoryTopics)) . "

📋 RECENT POSTS TO AVOID DUPLICATING:
{$recentTopicsList}

{$recentKeywordsList}

⚠️ CRITICAL DUPLICATE PREVENTION:
1. Choose COMPLETELY DIFFERENT technologies/frameworks than those listed above
2. If a technology was used recently (e.g., Kafka, Laravel, React), pick something ELSE
3. Provide MAXIMUM VARIETY - explore underutilized technologies
4. NEVER generate similar topics to recent posts
5. If you're unsure, pick from a different subcategory entirely

Examples of GOOD variety:
- If recent posts covered Kafka → try RabbitMQ, NATS, or Pulsar instead
- If recent posts covered React → try Svelte, Solid.js, or Qwik instead
- If recent posts covered Laravel → try Symfony, Lumen, or FastAPI instead
- If recent posts covered Docker → try Podman, LXC, or containerd instead

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
                    'content' => 'You are an expert tech content strategist who identifies trending, high-value topics that developers are actively searching for. You stay current with latest tech trends and focus on practical, specific content. Return ONLY valid JSON wrapped in ```json code blocks.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ], 200, 0.9, false); // Higher temperature for creativity, no strict JSON mode

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

        $prompt = "Write a comprehensive, professional blog post about: {$topic['title']}

CONTENT STRATEGY:
{$conversionStrategy}

🎯 TARGET AUDIENCE:
- Mid-to-senior level developers (3+ years experience)
- Professionals who already know the basics
- Looking for production-grade, battle-tested solutions
- Need ADVANCED patterns, not beginner tutorials
- Want to learn things NOT commonly found in documentation

⚠️ CRITICAL RULES - MUST FOLLOW:
1. NO clickbait or exaggerated claims (avoid: '10x faster', 'turbocharge', 'unlock', '99.99%')
2. NO unrealistic performance numbers without real benchmarks
3. Focus on REALISTIC expectations and honest trade-offs
4. Be EDUCATIONAL first, promotional never
5. Provide REAL working code examples with explanations
6. Cite actual tools, versions, and documentation
7. VARY YOUR WRITING STYLE - Don't use the same structure as other articles
8. Write like a REAL HUMAN developer sharing knowledge, not a template-following robot
9. **SKIP THE BASICS** - Assume reader knows fundamentals, jump to ADVANCED concepts
10. **PRODUCTION FOCUS** - Address real-world challenges: scaling, performance, debugging, security
11. **ARCHITECTURE DECISIONS** - Explain WHY certain patterns over others, trade-offs
12. **EDGE CASES** - Cover the gotchas, race conditions, memory leaks, N+1 queries
13. **FRAMEWORK-SPECIFIC DEPTH** - For Laravel: Service Containers, Events, Queues, Pipelines, Macros, etc.
14. **REAL METRICS** - If mentioning performance, use realistic numbers from actual testing

🎯 WRITING STYLE VARIATION (CRITICAL - Pick ONE randomly):

Style 1 - Story/Experience Based:
\"I ran into this problem last week on our production app...\" Share real experiences, what failed, what worked, lessons learned. Natural flow without rigid structure.

Style 2 - Direct Technical Deep Dive:
Skip the fluff. Jump right into the technical details. Code-heavy. Explain WHY things work. Debug tips. Short intro, no formal conclusion.

Style 3 - Step-by-Step Tutorial:
\"Let's build X together.\" Conversational guide. Show outputs. Explain errors. Feel like pair programming with a friend.

Style 4 - Comparative/Analysis:
\"I tested 3 approaches...\" Compare options. Real benchmarks. When to use each. Honest pros/cons. Make a recommendation.

Style 5 - Opinion/Best Practices:
\"After 5 years with this tech...\" Strong opinions. What docs don't say. Mistakes to avoid. Your workflow. Opinionated but fair.

🎯 NATURAL WRITING PRINCIPLES:

1. **START WITH A PRODUCTION SCENARIO**
   - Open with a REAL production problem or high-scale challenge
   - Use \"You\" language to connect personally
   - Example: \"You've scaled to 10M requests/day. Suddenly, your DB connection pool is maxed out...\"

2. **BE EXTREMELY PRACTICAL (PRODUCTION-GRADE)**
   - Every section must have actionable takeaways for REAL applications
   - Include production-ready code with FULL context (migrations, configs, tests)
   - Add \"Quick Win\" boxes with high-impact, battle-tested actions
   - Include \"⚡ Quick Win:\", \"💡 Pro Tip:\", \"⚠️ Common Mistake:\", \"🔥 Performance:\" callouts

3. **USE STORYTELLING (SENIOR-LEVEL CONTEXT)**
   - Share PRODUCTION scenarios from high-scale applications (100k+ users, millions of requests)
   - Include before/after comparisons with REAL metrics (response times, query counts, memory usage)
   - Reference real companies, patterns, or well-known architecture decisions
   - Example: \"When we scaled to 10M requests/day, we discovered that...\"

4. **MAKE IT SCANNABLE**
   - Use short paragraphs (2-4 sentences max)
   - Add bullet points and numbered lists frequently
   - Include visual breaks with emojis for key points (sparingly)
   - Clear, benefit-driven subheadings

5. **PROVIDE REAL VALUE (ADVANCED LEVEL)**
   - Production-ready code with FULL context: migrations, configs, service providers, routes, tests
   - Specific numbers from REAL testing: query times, memory usage, request/sec
   - Tools/libraries with version numbers (Laravel 11.x, Redis 7.x, PostgreSQL 15)
   - Step-by-step walkthroughs that SKIP basics (assume they know composer, migrations, etc.)
   - Architecture patterns: Repository, Service Layer, CQRS, Event Sourcing, DDD
   - Database optimization: indexes, query analysis, N+1 solutions, EXPLAIN examples
   - Caching layers: Redis patterns, cache invalidation, cache warming
   - Security: Laravel Gates/Policies, CSRF, XSS, SQL injection, rate limiting
   - Testing: Feature tests, Mocking, Database transactions, Factories
   - Deployment: Queue workers, Horizon, supervisor, zero-downtime migrations

6. **BE CONVERSATIONAL (BUT SENIOR-LEVEL)**
   - Write like talking to a senior colleague over coffee
   - Use contractions (you'll, don't, can't)
   - Ask rhetorical questions about architecture trade-offs
   - Share strong opinions and recommendations based on experience
   - Inject personality (but stay professional)

FLEXIBLE STRUCTURE (2000-3000 words - adapt based on chosen style):

⚠️ DO NOT use same headings every time. Be natural and varied.

SAMPLE NATURAL INTRO (vary based on style):
- Story style: \"Last month, our team ran into...\"
- Technical: \"Here's how X actually works under the hood...\"
- Tutorial: \"Today we're building... Here's what you need...\"
- Comparative: \"I benchmarked 3 solutions...\"
- Opinion: \"After working with X for 2 years, here's what I learned...\"

MAIN CONTENT (distribute 1500-2500 words naturally):
- Use NATURAL headings based on your content, NOT templated ones
- Examples of good headings:
  * \"The Problem\", \"What I Tried First\", \"The Solution That Worked\"
  * \"How It Works\", \"Implementation\", \"Gotchas and Edge Cases\"
  * \"Step 1: Setup\", \"Step 2: Configuration\", \"Step 3: Testing\"
  * \"Option A: Using Library X\", \"Option B: Rolling Your Own\"
  * \"What the Docs Don't Tell You\", \"Common Mistakes\", \"My Workflow\"

AVOID rigid templates like:
❌ \"Opening Hook\"
❌ \"Why This Matters\"
❌ \"Background/Context\"
❌ \"Core Concepts\"
❌ \"Conclusion\"

Instead use natural, content-specific headings
CONTENT SECTIONS - Choose natural headings:

Story Style Example:
## The Problem We Faced
## What We Tried First (and why it failed)
## The Solution That Actually Worked
## Lessons Learned

Technical Style Example:
## How It Works Under the Hood
## Implementation Details
[code blocks with explanations]
## Performance Characteristics
## Gotchas and Edge Cases

Tutorial Style Example:
## What We're Building
## Setup and Prerequisites
## Step 1: [First Task]
## Step 2: [Second Task]
## Testing and Debugging
## What's Next

CODE EXAMPLES (CRITICAL - MUST INCLUDE OUTPUTS):
- Include REAL working code with comments
- **ALWAYS show actual command/code outputs** after code blocks
- Show terminal sessions with commands AND their results
- Include error outputs when relevant (and how to fix them)
- Example format:
  ```bash
  php artisan queue:work --tries=3
  ```
  Output:
  ```
  [2024-01-15 10:23:45] Processing: App\\Jobs\\ProcessOrder
  [2024-01-15 10:23:47] Processed:  App\\Jobs\\ProcessOrder
  ```
- Show database query results, API responses, log outputs, dd() dumps
- For SQL queries, show EXPLAIN output or query execution time
- For API calls, show actual JSON responses
- Explain WHY, not just WHAT
- Common errors with actual error messages and fixes
- Use callouts sparingly, not in every section

CALLOUTS (use naturally, not everywhere):
💡 **Worth Knowing:** [Something non-obvious]
⚠️ **Watch Out:** [Common mistake]
🔧 **Quick Fix:** [Immediate solution]

ADVANCED TOPICS (optional, based on content):
- Can be separate section or woven throughout
- Performance considerations
- Edge cases
- Debugging tips
- When NOT to use this approach
{$advancedTipsExtra}

WRAP-UP (200-400 words - optional, vary by style):
- Story style: \"Here's what we learned\" or \"This solved our problem by...\"
- Technical: Brief summary or just end after last technical point
- Tutorial: \"What's next\" or \"Further improvements\"
- Comparative: \"My recommendation\" or \"When to use each\"
- Opinion: \"TL;DR\" or personal recommendations

AVOID formal \"Conclusion\" heading. Instead:
- End naturally based on your content
- Some posts just end after the last point
- Others have \"Key Takeaways\" or \"What I'd Do Differently\"
- Can include next steps, resources, or warnings
- Don't force a conclusion if the content speaks for itself
{$conclusionExtra}

FORMATTING RULES:
- Use ## for main headings, ### for subheadings
- Add code blocks with language specification: ```javascript, ```python, etc.
- CRITICAL: Keep code examples clear and simple for JSON compatibility
- Use **bold** for key terms, *italics* for emphasis
- Add > blockquotes for important notes
- Use tables for comparisons
- Keep paragraphs short and punchy
- When in JSON mode, ensure proper escaping of all special characters

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
                'content' => 'You are a senior software engineer and technical educator known for writing comprehensive, professional, and highly practical content. Your articles are educational, honest, and packed with real working code examples and actionable insights. You NEVER use clickbait or exaggerated claims. You write clear, realistic, professional content that developers trust. You MUST return ONLY valid JSON with properly escaped strings. Wrap your response in ```json code blocks.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 5000, 0.7, false); // Disable strict JSON mode due to Groq limitations with large content

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
        if (preg_match('/```json\s*(\{.+\})\s*```/s', $response, $matches)) {
            $jsonStr = $matches[1];
        } elseif (preg_match('/```\s*(\{.+\})\s*```/s', $response, $matches)) {
            $jsonStr = $matches[1];
        } elseif (preg_match('/\{.+\}/s', $response, $matches)) {
            $jsonStr = $matches[0];
        } else {
            throw new \Exception('No JSON found in AI response');
        }

        // Try 2: Direct decode
        $postData = json_decode($jsonStr, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $postData;
        }

        // Try 3: Fix the most common issue - literal newlines in JSON strings
        // This handles cases where AI outputs actual newlines instead of \n
        // We need to manually extract and rebuild to ensure proper encoding
        $fields = [];

        // Extract simple string fields with proper newline handling
        foreach (['title', 'excerpt', 'meta_title', 'meta_description'] as $field) {
            if (preg_match('/"' . $field . '":\s*"(.*?)"\s*[,}]/s', $jsonStr, $match)) {
                // The value already has newlines - keep them as-is for markdown
                $fields[$field] = $match[1];
            }
        }

        // Extract content separately (it's large and has newlines)
        if (preg_match('/"content":\s*"(.*?)"\s*,\s*"excerpt"/s', $jsonStr, $match)) {
            $fields['content'] = $match[1];
        }

        // Extract array fields
        if (preg_match('/"keywords":\s*\[(.*?)\]/s', $jsonStr, $match)) {
            preg_match_all('/"([^"]+)"/', $match[1], $keywords);
            $fields['keywords'] = $keywords[1];
        }

        if (preg_match('/"tags":\s*\[(.*?)\]/s', $jsonStr, $match)) {
            preg_match_all('/"([^"]+)"/', $match[1], $tags);
            $fields['tags'] = $tags[1];
        }

        // If we successfully extracted all fields, use them
        $required = ['title', 'content', 'excerpt', 'meta_title', 'meta_description', 'keywords', 'tags'];
        if (count(array_intersect_key(array_flip($required), $fields)) === count($required)) {
            return $fields;
        }

        // Try 4: More aggressive - escape all control characters in all string values
        $jsonStr = preg_replace_callback(
            '/"([^"]+)":\s*"([^"]*(?:\\.[^"]*)*)"/s',
            function($matches) {
                $key = $matches[1];
                $value = $matches[2];
                // Only process string fields, not array fields
                if (!in_array($key, ['keywords', 'tags'])) {
                    $value = addcslashes($value, "\n\r\t\"\\");
                }
                return '"' . $key . '": "' . $value . '"';
            },
            $jsonStr
        );

        $postData = json_decode($jsonStr, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $postData;
        }

        // Try 5: Nuclear option - manually parse and rebuild JSON
        // Extract each field individually and rebuild
        $fields = [];

        // Extract simple string fields
        foreach (['title', 'content', 'excerpt', 'meta_title', 'meta_description'] as $field) {
            if (preg_match('/"' . $field . '":\s*"(.+?)"\s*[,}]/s', $jsonStr, $match)) {
                $value = $match[1];
                $value = str_replace(["\n", "\r", "\t", "\\"], ["\\n", "\\r", "\\t", "\\\\"], $value);
                $fields[$field] = $value;
            }
        }

        // Extract array fields
        if (preg_match('/"keywords":\s*\[(.*?)\]/s', $jsonStr, $match)) {
            preg_match_all('/"([^"]+)"/', $match[1], $keywords);
            $fields['keywords'] = $keywords[1];
        }

        if (preg_match('/"tags":\s*\[(.*?)\]/s', $jsonStr, $match)) {
            preg_match_all('/"([^"]+)"/', $match[1], $tags);
            $fields['tags'] = $tags[1];
        }

        // Validate we got all required fields
        $required = ['title', 'content', 'excerpt', 'meta_title', 'meta_description', 'keywords', 'tags'];
        $missing = array_diff($required, array_keys($fields));
        if (empty($missing)) {
            return $fields;
        }

        // Log the problematic response for debugging
        Log::error('Failed to parse AI JSON response', [
            'error' => json_last_error_msg(),
            'missing_fields' => $missing,
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
                ['role' => 'system', 'content' => 'You are an expert technical educator who creates comprehensive, realistic tutorial series. You NEVER use clickbait or exaggerated claims. You focus on practical, professional, educational content. You MUST return ONLY valid JSON wrapped in ```json code blocks.'],
                ['role' => 'user', 'content' => $prompt]
            ], 1000, 0.7, false); // Lower temperature for more consistent, professional output, no strict JSON mode

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
                'content' => 'You are a senior software engineer and technical educator creating comprehensive tutorial series. Each part must be clear, practical, and build properly on previous parts. You NEVER use clickbait or exaggerated performance claims. You write professional, realistic, educational content. You MUST return ONLY valid JSON wrapped in ```json code blocks with properly escaped strings.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 5000, 0.7, false); // Higher tokens for depth, lower temp for consistency, no strict JSON mode

        return $this->parseAIResponse($response);
    }

    private function getBaseContentPrompt(string $conversionStrategy, string $advancedTipsExtra, string $conclusionExtra, bool $isPremium): string
    {
        return "Write a comprehensive, professional, and highly educational blog post

CONTENT STRATEGY:
{$conversionStrategy}

🎯 TARGET AUDIENCE:
- Mid-to-senior level developers (3+ years experience)
- Professionals who already know the basics
- Looking for production-grade, battle-tested solutions
- Need ADVANCED patterns, not beginner tutorials
- Want to learn things NOT commonly found in documentation

⚠️ CRITICAL RULES - MUST FOLLOW:
1. NO clickbait or exaggerated claims (avoid: '10x faster', 'turbocharge', 'unlock', '99.99%')
2. NO unrealistic performance numbers without real benchmarks
3. Focus on REALISTIC expectations and honest trade-offs
4. Be EDUCATIONAL first, promotional never
5. Provide REAL working code examples with explanations
6. Cite actual tools, versions, and documentation
7. **SKIP THE BASICS** - Assume reader knows fundamentals, jump to ADVANCED concepts
8. **PRODUCTION FOCUS** - Address real-world challenges: scaling, performance, debugging, security
9. **ARCHITECTURE DECISIONS** - Explain WHY certain patterns over others, trade-offs
10. **EDGE CASES** - Cover the gotchas, race conditions, memory leaks, N+1 queries
11. **FRAMEWORK-SPECIFIC DEPTH** - For Laravel: Service Containers, Events, Queues, Pipelines, Macros, etc.
12. **REAL METRICS** - If mentioning performance, use realistic numbers from actual testing

🎯 ENGAGEMENT PRINCIPLES (CRITICAL):

1. **START WITH A PRODUCTION SCENARIO**
   - Open with a REAL production problem or high-scale challenge
   - Use \"You\" language to connect personally
   - Example: \"You've scaled to 10M requests/day. Suddenly, your DB connection pool is maxed out...\"

2. **BE EXTREMELY PRACTICAL (PRODUCTION-GRADE)**
   - Every section must have actionable takeaways for REAL applications
   - Include production-ready code with FULL context (migrations, configs, tests)
   - Add \"Quick Win\" boxes with high-impact, battle-tested actions
   - Include \"⚡ Quick Win:\", \"💡 Pro Tip:\", \"⚠️ Common Mistake:\", \"🔥 Performance:\" callouts

3. **USE STORYTELLING (SENIOR-LEVEL CONTEXT)**
   - Share PRODUCTION scenarios from high-scale applications (100k+ users, millions of requests)
   - Include before/after comparisons with REAL metrics (response times, query counts, memory usage)
   - Reference real companies, patterns, or well-known architecture decisions
   - Example: \"When we scaled to 10M requests/day, we discovered that...\"

4. **MAKE IT SCANNABLE**
   - Use short paragraphs (2-4 sentences max)
   - Add bullet points and numbered lists frequently
   - Include visual breaks with emojis for key points (sparingly)
   - Clear, benefit-driven subheadings

5. **PROVIDE REAL VALUE (ADVANCED LEVEL)**
   - Production-ready code with FULL context: migrations, configs, service providers, routes, tests
   - Specific numbers from REAL testing: query times, memory usage, request/sec
   - Tools/libraries with version numbers (Laravel 11.x, Redis 7.x, PostgreSQL 15)
   - Step-by-step walkthroughs that SKIP basics (assume they know composer, migrations, etc.)
   - Architecture patterns: Repository, Service Layer, CQRS, Event Sourcing, DDD
   - Database optimization: indexes, query analysis, N+1 solutions, EXPLAIN examples
   - Caching layers: Redis patterns, cache invalidation, cache warming
   - Security: Laravel Gates/Policies, CSRF, XSS, SQL injection, rate limiting
   - Testing: Feature tests, Mocking, Database transactions, Factories
   - Deployment: Queue workers, Horizon, supervisor, zero-downtime migrations

6. **BE CONVERSATIONAL (BUT SENIOR-LEVEL)**
   - Write like talking to a senior colleague over coffee
   - Use contractions (you'll, don't, can't)
   - Ask rhetorical questions about architecture trade-offs
   - Share strong opinions and recommendations based on experience
   - Inject personality (but stay professional)

FLEXIBLE STRUCTURE (2000-3000 words - adapt based on chosen style):

⚠️ DO NOT use same headings every time. Be natural and varied.

SAMPLE NATURAL INTRO (vary based on style):
- Story style: \"Last month, our team ran into...\"
- Technical: \"Here's how X actually works under the hood...\"
- Tutorial: \"Today we're building... Here's what you need...\"
- Comparative: \"I benchmarked 3 solutions...\"
- Opinion: \"After working with X for 2 years, here's what I learned...\"

MAIN CONTENT (distribute 1500-2500 words naturally):
- Use NATURAL headings based on your content, NOT templated ones
- Examples of good headings:
  * \"The Problem\", \"What I Tried First\", \"The Solution That Worked\"
  * \"How It Works\", \"Implementation\", \"Gotchas and Edge Cases\"
  * \"Step 1: Setup\", \"Step 2: Configuration\", \"Step 3: Testing\"
  * \"Option A: Using Library X\", \"Option B: Rolling Your Own\"
  * \"What the Docs Don't Tell You\", \"Common Mistakes\", \"My Workflow\"

AVOID rigid templates like:
❌ \"Opening Hook\"
❌ \"Why This Matters\"
❌ \"Background/Context\"
❌ \"Core Concepts\"
❌ \"Conclusion\"

Instead use natural, content-specific headings
CONTENT SECTIONS - Choose natural headings:

Story Style Example:
## The Problem We Faced
## What We Tried First (and why it failed)
## The Solution That Actually Worked
## Lessons Learned

Technical Style Example:
## How It Works Under the Hood
## Implementation Details
[code blocks with explanations]
## Performance Characteristics
## Gotchas and Edge Cases

Tutorial Style Example:
## What We're Building
## Setup and Prerequisites
## Step 1: [First Task]
## Step 2: [Second Task]
## Testing and Debugging
## What's Next

CODE EXAMPLES (CRITICAL - MUST INCLUDE OUTPUTS):
- Include REAL working code with comments
- **ALWAYS show actual command/code outputs** after code blocks
- Show terminal sessions with commands AND their results
- Include error outputs when relevant (and how to fix them)
- Example format:
  ```bash
  php artisan queue:work --tries=3
  ```
  Output:
  ```
  [2024-01-15 10:23:45] Processing: App\\Jobs\\ProcessOrder
  [2024-01-15 10:23:47] Processed:  App\\Jobs\\ProcessOrder
  ```
- Show database query results, API responses, log outputs, dd() dumps
- For SQL queries, show EXPLAIN output or query execution time
- For API calls, show actual JSON responses
- Explain WHY, not just WHAT
- Common errors with actual error messages and fixes
- Use callouts sparingly, not in every section

CALLOUTS (use naturally, not everywhere):
💡 **Worth Knowing:** [Something non-obvious]
⚠️ **Watch Out:** [Common mistake]
🔧 **Quick Fix:** [Immediate solution]

ADVANCED TOPICS (optional, based on content):
- Can be separate section or woven throughout
- Performance considerations
- Edge cases
- Debugging tips
- When NOT to use this approach
{$advancedTipsExtra}

WRAP-UP (200-400 words - optional, vary by style):
- Story style: \"Here's what we learned\" or \"This solved our problem by...\"
- Technical: Brief summary or just end after last technical point
- Tutorial: \"What's next\" or \"Further improvements\"
- Comparative: \"My recommendation\" or \"When to use each\"
- Opinion: \"TL;DR\" or personal recommendations

AVOID formal \"Conclusion\" heading. Instead:
- End naturally based on your content
- Some posts just end after the last point
- Others have \"Key Takeaways\" or \"What I'd Do Differently\"
- Can include next steps, resources, or warnings
- Don't force a conclusion if the content speaks for itself
{$conclusionExtra}

FORMATTING RULES:
- Use ## for main headings, ### for subheadings
- Add code blocks with language specification: ```javascript, ```python, etc.
- CRITICAL: Keep code examples clear and simple for JSON compatibility
- Use **bold** for key terms, *italics* for emphasis
- Add > blockquotes for important notes
- Use tables for comparisons
- Keep paragraphs short and punchy
- When in JSON mode, ensure proper escaping of all special characters

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
