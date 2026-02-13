<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ContentPlan;
use App\Services\ImageGenerationService;
use App\Services\ContentModerationService;
use App\Services\WebResearchService;
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

        // Step 9: Run AI moderation check
        $this->info('🔍 Running content moderation check...');
        $moderationService = new ContentModerationService();
        $moderationResult = $moderationService->moderateContent(
            $postData['title'],
            $postData['content'],
            $postData['excerpt']
        );

        // AI-generated content gets auto-approved if high quality
        $moderation_status = ($moderationResult['passed'] && $moderationResult['score'] >= 85)
            ? 'approved'
            : 'pending';

        if ($moderation_status === 'approved') {
            $this->info("✅ Content passed moderation (score: {$moderationResult['score']})");
        } else {
            $this->warn("⚠️  Content flagged for manual review (score: {$moderationResult['score']})");
        }

        // Step 10: Create the post
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
            'is_featured' => $isPremium,
            'allow_comments' => true,
            'moderation_status' => $moderation_status,
            'moderated_at' => $moderation_status === 'approved' ? now() : null,
            'moderation_notes' => $moderation_status === 'approved'
                ? "Auto-approved by AI (score: {$moderationResult['score']})"
                : null,
            'ai_moderation_check' => $moderationResult,
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

        // Track in content plan if this was from a plan
        if (isset($topic['from_plan']) && $topic['from_plan'] && isset($topic['plan_id'])) {
            $plan = ContentPlan::find($topic['plan_id']);
            if ($plan) {
                $plan->markTopicGenerated($topic['title'], $post->id);
                $this->info("✅ Marked topic as generated in content plan");

                if ($plan->fresh()->isComplete()) {
                    $plan->update(['status' => 'completed']);
                    $this->info("🎉 Monthly content plan completed!");
                }
            }
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
        // Check if there's a monthly content plan
        $contentPlan = ContentPlan::getCurrentPlan();

        if ($contentPlan && !$contentPlan->isComplete()) {
            $this->info("📅 Using monthly content plan: {$contentPlan->theme}");
            // Get a random ungenerated topic from the plan
            $generated = collect($contentPlan->generated_topics ?? [])->pluck('topic')->toArray();

            // Filter out already generated topics
            $remaining = array_filter($contentPlan->planned_topics, function($topic) use ($generated) {
                $title = is_array($topic) ? $topic['title'] : $topic;
                return !in_array($title, $generated);
            });

            if (!empty($remaining)) {
                $selectedTopic = $remaining[array_rand($remaining)];
                $title = is_array($selectedTopic) ? $selectedTopic['title'] : $selectedTopic;
                $type = is_array($selectedTopic) ? ($selectedTopic['type'] ?? 'free') : 'free';
                $week = is_array($selectedTopic) ? ($selectedTopic['week'] ?? 1) : 1;

                $badge = $type === 'premium' ? '💎 PREMIUM' : '🆓 FREE';
                $this->info("📝 Selected from plan: [{$badge}] [Week {$week}] {$title}");

                return [
                    'title' => $title,
                    'category' => $contentPlan->theme,
                    'from_plan' => true,
                    'plan_id' => $contentPlan->id,
                    'is_premium' => $type === 'premium',
                    'week' => $week,
                ];
            }
        }

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
        // EXPANDED: Not just programming - emerging tech, next-gen concepts, innovative solutions
        $trendingCategories = [
            'AI & Machine Learning' => [
                'Multimodal AI (vision + language models)',
                'AI Agents & Autonomous Systems',
                'Neural Architecture Search (NAS)',
                'Federated Learning & Privacy-Preserving ML',
                'Diffusion Models & Generative AI',
                'LLM Fine-tuning & RLHF',
                'Vector Databases (Pinecone, Weaviate, Qdrant)',
                'AI Safety & Alignment',
            ],
            'Quantum Computing' => [
                'Quantum Algorithms (Shor, Grover, VQE)',
                'Quantum Cryptography & Post-Quantum Security',
                'Quantum Machine Learning',
                'IBM Qiskit, Google Cirq, Amazon Braket',
                'Quantum Supremacy Applications',
                'Quantum Error Correction',
            ],
            'Blockchain & Web3' => [
                'Zero-Knowledge Proofs (zkSNARKs, zkSTARKs)',
                'Layer 2 Scaling Solutions (Optimism, Arbitrum)',
                'Decentralized Identity (DIDs, Verifiable Credentials)',
                'Smart Contract Security & Auditing',
                'DAOs & Decentralized Governance',
                'NFT Utility Beyond Art',
                'Cross-Chain Interoperability',
            ],
            'Extended Reality (XR)' => [
                'WebXR & Spatial Computing',
                'Digital Twins in Manufacturing',
                'AR for E-commerce & Retail',
                'VR Training & Simulations',
                'Mixed Reality for Healthcare',
                'Metaverse Infrastructure',
            ],
            'Edge Computing & IoT' => [
                'Edge AI & TinyML',
                'LoRaWAN & LPWAN Networks',
                '5G-Enabled IoT Applications',
                'Digital Twin Technology',
                'Industrial IoT (IIoT) & Industry 4.0',
                'Smart Cities Infrastructure',
                'Edge Analytics & Real-time Processing',
            ],
            'Biotechnology & HealthTech' => [
                'CRISPR & Gene Editing Software',
                'AI-Driven Drug Discovery',
                'Wearable Health Monitoring',
                'Brain-Computer Interfaces (Neuralink, OpenBCI)',
                'Synthetic Biology & Bioinformatics',
                'Telemedicine Platforms',
            ],
            'Energy & CleanTech' => [
                'Smart Grid Technology',
                'Battery Management Systems',
                'Carbon Capture & Monitoring',
                'Renewable Energy Optimization',
                'EV Charging Infrastructure',
                'Energy Trading Platforms',
            ],
            'Space Technology' => [
                'Satellite Data Analytics',
                'Space Mission Planning Software',
                'CubeSat Development',
                'Space Communication Protocols',
                'Orbital Debris Tracking',
            ],
            'Advanced Software Engineering' => [
                'WebAssembly & WASI',
                'Rust for Systems Programming',
                'eBPF & Kernel Programming',
                'Event-Driven Architecture',
                'CQRS & Event Sourcing',
                'Service Mesh (Istio, Linkerd)',
                'Chaos Engineering',
            ],
            'Next-Gen Databases' => [
                'Vector Databases for AI',
                'Time-Series Databases (InfluxDB, TimescaleDB)',
                'Graph Databases (Neo4j, ArangoDB)',
                'NewSQL Databases (CockroachDB, YugabyteDB)',
                'Database Sharding Strategies',
                'Multi-Model Databases',
            ],
            'Cybersecurity Innovation' => [
                'Zero Trust Architecture',
                'AI-Powered Threat Detection',
                'Homomorphic Encryption',
                'Supply Chain Security',
                'DevSecOps Automation',
                'Behavioral Biometrics',
            ],
            'Robotics & Automation' => [
                'ROS 2 & Robotic Middleware',
                'Computer Vision for Robotics',
                'Autonomous Navigation Systems',
                'Collaborative Robots (Cobots)',
                'Drone Programming & Control',
                'Robotic Process Automation (RPA)',
            ],
            'FinTech & DeFi' => [
                'Algorithmic Trading Systems',
                'Open Banking APIs',
                'Central Bank Digital Currencies (CBDCs)',
                'RegTech & Compliance Automation',
                'Decentralized Finance Protocols',
                'Payment Processing Innovation',
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

🎯 CONTENT TYPE STRATEGY (Pick ONE of these valuable formats):

**1. COMPARISON & BENCHMARKS** (30% of topics)
Users want to know WHICH technology to choose, not just HOW to use one.
Examples:
- \"ChatGPT vs Claude 3.5 Sonnet: Performance, Cost, and Use Cases Compared\"
- \"PHP 8.3 vs Python 3.12: Real-World Web Performance Benchmarks\"
- \"Laravel vs Django vs Ruby on Rails: Building the Same App in All Three\"
- \"PostgreSQL vs MySQL vs MongoDB: When to Use Each Database\"
- \"React vs Vue vs Svelte: Bundle Size, Performance, and Developer Experience\"
- \"AWS vs Azure vs GCP: Cost Analysis for a Real Production App\"
- \"Top 10 Laravel Packages for Modern Web Development in {$currentYear}\"
- \"5 Best Redis Alternatives for Caching and Session Management\"

**2. FASCINATING FEATURES & DEEP DIVES** (30% of topics)
NOT basic installation/usage guides. Focus on UNIQUE, powerful features that make tech special.
Examples:
- \"Laravel's Service Container: The Hidden Power Behind Dependency Injection\"
- \"React Concurrent Features: How Time Slicing Actually Works\"
- \"PostgreSQL's JSONB: When Your Relational Database Becomes NoSQL\"
- \"Rust's Ownership System: Why It Prevents Memory Leaks Without Garbage Collection\"
- \"Docker's BuildKit: The Secret to 5x Faster Container Builds\"
- \"Kafka's Exactly-Once Semantics: How It Actually Guarantees Message Delivery\"
- \"TypeScript's Template Literal Types: Building Type-Safe APIs\"

**3. COMPLETE PROJECT BUILDS** (25% of topics)
Build FULL, production-ready applications from scratch. This is UNIQUE and valuable.
Examples:
- \"Building a Complete E-commerce Platform with Laravel 11, Stripe, and Vue 3\"
- \"Full-Stack Blog with Next.js 14, Prisma, and PostgreSQL: From Zero to Deploy\"
- \"Real-Time Chat Application using NestJS, WebSockets, and Redis\"
- \"Building a SaaS Starter Kit with Django, Celery, and Stripe Integration\"
- \"Complete API Platform with FastAPI, PostgreSQL, and JWT Authentication\"
- \"Building a Social Media Dashboard with React, Node.js, and MongoDB\"
- \"Full-Stack Task Management App using MERN Stack (MongoDB, Express, React, Node)\"

**4. ARCHITECTURE & PATTERNS** (15% of topics)
How to SCALE and structure applications properly.
Examples:
- \"Microservices with Laravel: Breaking Down a Monolith Step-by-Step\"
- \"Event-Driven Architecture: Building Scalable Systems with Kafka and Go\"
- \"CQRS and Event Sourcing in .NET: A Practical Implementation\"
- \"Domain-Driven Design: Refactoring a Legacy PHP Application\"
- \"Building Multi-Tenant SaaS Architecture with PostgreSQL Row-Level Security\"

Requirements:
1. Topic must be CURRENT and RELEVANT in {$currentYear}
2. Be SPECIFIC - include actual version numbers and technologies when applicable
3. Focus on practical, educational content developers need
4. Use REALISTIC, professional titles - NO clickbait or exaggerated claims
5. AVOID phrases like: '10x Faster', '20x Better', '99.99% Uptime', 'Turbocharge', 'Unlock'
6. Focus on learning, understanding, and practical implementation
7. Make it educational and actionable
8. ⚠️ AVOID basic installation/setup guides - users can find those anywhere
9. ⚠️ AVOID simple \"Getting Started\" tutorials - go DEEPER
10. Focus on UNIQUE insights, comparisons, or complete builds

Examples of GOOD titles:
- \"ChatGPT vs Claude vs Gemini: Which LLM is Best for Code Generation?\"
- \"Building a Complete Multi-Tenant SaaS with Laravel 11 and Filament\"
- \"PostgreSQL's Most Underrated Features: CTEs, Window Functions, and LISTEN/NOTIFY\"
- \"Laravel Octane vs FrankenPHP: Real Benchmarks and Production Experience\"
- \"Building a Real-Time Analytics Dashboard with ClickHouse and React\"
- \"Top 8 Node.js Frameworks Beyond Express in {$currentYear}\"

Examples of BAD titles (AVOID):
- \"Getting Started with Laravel\" (too basic, found everywhere)
- \"How to Install Docker\" (basic installation guide)
- \"Introduction to React Hooks\" (too introductory)
- \"10x Faster Performance with [Technology]\" (unrealistic claims)
- \"Turbocharge Your App with [Tool]\" (clickbait language)

Return ONLY a JSON object:
{
  \"title\": \"Professional, educational, specific title without clickbait\",
  \"category\": \"{$randomCategory}\"
}";

        try {
            $response = $this->callOpenAI([
                [
                    'role' => 'system',
                    'content' => 'You are an expert tech content strategist who identifies trending, high-value topics that developers are actively searching for. You focus on UNIQUE content: comparisons/benchmarks, fascinating feature deep-dives, complete project builds, and architecture patterns - NOT basic installation guides. Return ONLY valid JSON wrapped in ```json code blocks.'
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
        // If topic is from content plan, respect its premium designation
        if (isset($topic['is_premium'])) {
            $isPremium = $topic['is_premium'];
        } else {
            // Otherwise use command options or 70% premium by default
            $isPremium = $this->option('premium') || (!$this->option('free') && rand(1, 100) <= 70);
        }

        // 🔍 GATHER DEEP RESEARCH FROM MULTIPLE SOURCES
        $this->info("   🔍 Gathering research from multiple sources...");
        try {
            $researchService = app(WebResearchService::class);
            $research = $researchService->gatherResearch($topic['title'], 3);
            $researchContext = $this->buildResearchContext($research);
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Research gathering failed, continuing with prompt-only generation");
            Log::warning('Research gathering failed for topic', ['title' => $topic['title'], 'error' => $e->getMessage()]);
            $researchContext = '';
        }

        $conversionStrategy = $isPremium ? $this->getPremiumContentStrategy() : '';
        $advancedTipsExtra = $isPremium ? '- Hint at deeper premium content' : '';
        $conclusionExtra = $isPremium ? '- Subtle mention of deeper expertise available' : '';

        $prompt = "Write a comprehensive, deep-research blog post about: {$topic['title']}

LENGTH REQUIREMENT: **MINIMUM 4000-5000 WORDS (15+ MINUTE READ)** - This is a deep research post, not a quick tip.

{$researchContext}

CONTENT STRATEGY:
{$conversionStrategy}

🎯 TARGET AUDIENCE:
- Mid-to-senior level developers (3+ years experience)
- Professionals who already know the basics
- Looking for production-grade, battle-tested solutions
- Need ADVANCED patterns, not beginner tutorials
- Want to learn things NOT commonly found in documentation

🚫 CRITICAL RULES - MUST FOLLOW (QUALITY CONTROL) - THIS DETERMINES SUCCESS OR FAILURE:

**FORBIDDEN PATTERNS (INSTANT FAIL - REWRITE IF YOU DETECT ANY OF THESE):**
❌ \"As developers, we often...\" → GENERIC OPENING. Replace with: \"Last quarter, our team discovered...\"
❌ \"In this article, we'll explore...\" → TEMPLATED. Replace with: \"Here's what I learned when...\"
❌ \"Let's understand the basics...\" → DOC REGURGITATION. Replace with: \"Most docs skip the hard part...\"
❌ \"This technology allows us to...\" → MARKETING SPEAK. Replace with: \"I realized X only works if you also do Y...\"
❌ \"The following code shows...\" → TUTORIAL TEMPLATE. Replace with: \"When I first tried this, it broke because...\"
❌ \"In conclusion...\" or \"Summary\" headings → RIGID STRUCTURE. Let content end naturally.
❌ Passive voice like \"errors are thrown\", \"databases are queried\" → IMPERSONAL. Use \"I discovered...\", \"we debugged...\"
❌ Perfect implementations without errors shown → FAKE. Show actual error messages you hit.
❌ Feature lists like \"Pros and Cons\" → GENERIC COMPARISON. Use: \"Why I abandoned X for Y after Z failed\"

**AUTHENTICITY & UNIQUENESS (MOST CRITICAL):**
1. YOU MUST WRITE IN FIRST PERSON (\"I\", \"we\", \"my\") - This is non-negotiable. Every section should feel like personal experience.
2. INCLUDE SPECIFIC NAMES: \"My colleague Jake suggested...\", \"Our CTO Sarah insisted on...\", \"The Stripe team told us...\"
3. INCLUDE SPECIFIC DATES/TIMELINES: \"Last month\", \"Q3 2024\", \"After 6 months of...\", \"When we deployed Friday at 3pm...\"
4. INCLUDE SPECIFIC NUMBERS ABOUT YOUR EXPERIENCE: \"We scaled from 1M to 50M requests\", \"Reduced latency from 800ms to 120ms\", \"Saved $40k/month\"
5. SHARE AT LEAST ONE FAILURE STORY: \"My first approach failed because...\", \"We lost 2 days debugging why...\", \"The outage happened because I didn't...\"
6. SHARE CONFLICTING OPINIONS: \"Everyone says X, but I've found that Y works better because...\", \"The docs recommend Z, but that's wrong when...\", \"My hot take: the popular approach is actually overengineered\"
7. INCLUDE PROPRIETARY/LEARNED-THE-HARD-WAY KNOWLEDGE: Not available in docs. Examples: \"Buffer sizes need tuning on production because...\", \"The cache invalidation strategy they don't document...\", \"The scaling limits nobody mentions...\"
8. REFERENCE SPECIFIC PRODUCTION SCENARIOS: \"At 10M requests/day, our approach failed\", \"When the spike hit unexpectedly\", \"During Black Friday when everything broke\"
9. SHOW YOUR WORKFLOW/SHORTCUTS: \"Here's how I actually debug this...\", \"My tmux setup for this...\", \"The VSCode extensions I can't live without...\", \"The bash function I wrote to...\"
10. BE OPINIONATED WITH JUSTIFICATION: Don't say \"some prefer X, others prefer Y\". Say \"X is better because [experience-based reason]\", \"Y is wrong when [specific scenario]\", \"Here's why I'd never use Z again\"

**TECHNICAL DEPTH (GO 3 LEVELS DEEPER THAN OBVIOUS):**
11. Don't explain \"what it is\" - explain \"how it breaks\" and \"how I fixed it\"
12. Show BEFORE/AFTER: \"Our queries took 5 seconds, indexes reduced to 200ms, but we discovered...\"
13. Include THE EDGE CASES YOU HIT: \"It works great until you have 100k+ records, then...\", \"The docs don't mention that it fails when...\"
14. Explain ARCHITECTURAL TRADE-OFFS with real consequences: \"We chose X for speed but paid for it in memory usage...\"
15. Include DEBUGGING STEPS: \"Here's how I discovered it was actually a connection pool issue...\"
16. Show WHAT DOCUMENTATION SKIPS: \"The official docs show the happy path but miss...\"
17. Include PERFORMANCE TESTING METHODOLOGY: \"I used load-testing with Hey because...\"
18. Show SCALING PATTERNS: \"This approach works until 10k users, then you need...\"

**PRACTICAL VALUE (COPY-PASTE READY WITH CONTEXT):**
19. Every code block must be COMPLETE: imports, env vars, migrations, configs, tests shown
20. Show ACTUAL COMMAND OUTPUT: Not \"run command X\", but \"$ command\" followed by actual output with timestamps/numbers
21. Include ERROR MESSAGES: \"When you hit this error: [exact message], here's why and how to fix it\"
22. Production-ready defaults: \"Use this config in production, not the defaults\"
23. Include DEBUGGING OUTPUT: dd() dumps, var_export, log outputs, queries explained
24. Show DATABASE SCHEMA: EXPLAIN queries, index recommendations, N+1 problems found
25. Include ACTUAL NUMBERS: Response times, memory usage, query counts, cost per operation

**VOICE & PERSONALITY (THIS DETERMINES IF IT SOUNDS HUMAN):**
26. Use CONTRACTIONS: \"don't\" not \"do not\", \"can't\" not \"cannot\", \"it's\" not \"it is\" - MANDATORY for conversational tone
27. Ask RHETORICAL QUESTIONS that show thinking: \"Why would anyone design it that way?\", \"Have you ever wondered why...?\", \"What happens if you...?\"
28. Use NATURAL LANGUAGE PATTERNS: \"So here's the thing...\", \"The real issue is...\", \"What I didn't expect was...\", \"This is where it gets interesting...\"
29. Show EMOTIONAL RESPONSES: \"I was frustrated when...\", \"This surprised me...\", \"I got excited discovering...\"
30. INJECT PERSONALITY/HUMOR: \"My setup is probably overkill for what we do, but...\", \"I may have gone overboard with...\", \"This is either brilliant or I'm sleep-deprived\"
31. Tell SIDE STORIES: \"This reminds me of when...\", \"One time at my previous job...\", \"I learned this the hard way when...\"
32. Use INCLUSIVE LANGUAGE: \"We all struggle with...\", \"You've probably hit this...\", \"I'm sure you've seen...\"

**QUALITY & HONESTY (NON-NEGOTIABLE):**
33. NO MARKETING LANGUAGE: Avoid: '10x', 'game-changer', 'revolutionary', 'turbocharge', 'unlock', 'destroy competition'
34. NO UNREALISTIC CLAIMS WITHOUT PROOF: If you claim \"50x faster\", show the benchmark setup, test code, and actual numbers
35. ALWAYS MENTION TRADE-OFFS: \"It's faster but uses 3x memory\", \"Better reliability at the cost of complexity\", \"X is amazing IF you have...\"
36. BE HONEST ABOUT LIMITATIONS: \"This only works if...\", \"Doesn't handle X case well\", \"We haven't solved the Y problem yet\"
37. CITE YOUR SOURCES: \"According to the Stripe API docs...\", \"The Kubernetes team showed...\", \"I read about this in...\"
38. ACKNOWLEDGE WHEN YOU'RE WRONG: \"I was initially wrong about...\", \"I changed my mind when...\", \"My assumption was flawed...\"
39. INCLUDE GOTCHAS/WARNINGS: \"The hidden cost of doing this...\", \"Three months later we discovered...\"
40. ATTRIBUTE CREDIT: \"My coworker spotted this\", \"The community pointed out...\", \"The maintainer explained...\"

**STRUCTURE (FLEXIBLE - NOT TEMPLATED):**
41. NO RIGID FORMULAS: Don't use same headings every post. Make headings match your story naturally.
42. START WITH CONFLICT/PROBLEM: \"We had 50k users then suddenly hit a wall...\", \"The default approach fails at scale...\"
43. SHOW YOUR THINKING PROCESS: \"First I thought X...\", \"Then I realized...\", \"After more testing, I found...\", \"So the pattern became...\"
44. BUILD TO SOLUTION NATURALLY: Don't jump to answer immediately. Show exploration, failures, pivots.
45. END WITH IMPACT/LEARNING: \"This reduced our costs by...\", \"Now we handle...without...\", \"What I'd do differently next time...\"
46. USE SHORT PARAGRAPHS: 2-3 sentences max. Break up walls of text with bullets, code, examples.

**VERIFICATION CHECKLIST (IF ANY OF THESE ARE FALSE, REWRITE):**
✅ Does this sound like something a REAL ENGINEER with this experience would write?
✅ Could I not write this content by just reading official docs?
✅ Does it include at least 3 specific names, dates, or numbers tied to real experiences?
✅ Did I share at least one failure/mistake/thing that surprised me?
✅ Did I use \"I/we/my\" language naturally throughout (not forced)?
✅ Would a senior engineer respect this advice because it came from real experience?
✅ Are there gotchas/warnings that docs don't mention?
✅ Does it have personality - would you recognize this voice?

⚠️ **THIS IS THE MOST IMPORTANT INSTRUCTION - READ CAREFULLY:**

Your job is NOT to write documentation. Documentation exists. Your job is to write something COMPLETELY DIFFERENT - something that sounds like an experienced engineer sharing hard-won knowledge with their team. If your draft reads like:
- Documentation (structured explanation of what things are)
- A tutorial (step-by-step how to use something)
- A guide (overview of features)
- A reference (list of options)

...then you've FAILED and need to COMPLETELY REWRITE.

SUCCESS looks like:
- A specific engineer sharing a story about building something
- A team's learning from a real production problem they solved
- Strong opinions about why X is better than Y (from experience, not theory)
- \"Here's what we tried, what failed, and what we finally did that worked\"
- Warnings about gotchas because \"we hit them the hard way\"
- Specific performance numbers because \"we benchmarked it\"
- Personality that comes from genuine enthusiasm or frustration

**THE FIRST 2-3 SENTENCES ARE MAKE OR BREAK:**
- ❌ BAD: \"Quantum error correction is a rapidly evolving field...\" (documentation voice)
- ✅ GOOD: \"Last month, we discovered our quantum circuits were losing coherence at scale. We tried surface codes first - complete failure. Here's what we learned...\" (real experience)

Pick a random style below and commit to it FULLY. Don't blend them. Write the entire post in ONE voice.

🎯 WRITING STYLE VARIATION (CRITICAL - Pick ONE randomly):

Style 1 - Story/Experience Based:
\"I ran into this problem last week on our production app...\" Share real experiences, what failed, what worked, lessons learned. Natural flow without rigid structure.

Style 2 - Direct Technical Deep Dive:
Skip the fluff. Jump right into the technical details. Code-heavy. Explain WHY things work. Debug tips. Short intro, no formal conclusion.

Style 3 - Step-by-Step Tutorial:
\"Let's build X together.\" Conversational guide. Show outputs. Explain errors. Feel like pair programming with a friend.

Style 4 - Comparative/Analysis (USE FOR: comparison topics):
\"I tested 3 approaches...\" Compare options with REAL benchmarks (latency, throughput, cost, bundle size). When to use each. Honest pros/cons. Make a clear recommendation based on use cases. Include comparison tables, performance graphs, cost breakdowns.

Style 5 - Opinion/Best Practices (USE FOR: ranking/list topics):
\"After 5 years with this tech...\" Strong opinions. What docs don't say. Mistakes to avoid. Your workflow. Opinionated but fair. Rank options clearly with reasoning.

Style 6 - Complete Project Build (USE FOR: full project topics):
\"Let's build a production-ready project from scratch.\" Full implementation - database design, backend API, frontend, auth, deployment. Complete, working code for every step. Commit-by-commit progression. Real outputs from commands. End with deployed, working app.

Style 7 - Feature Deep Dive (USE FOR: specific feature topics):
\"Let's explore a specific feature that most developers overlook.\" Focus on ONE powerful feature. Show unique use cases. Code examples demonstrating the feature. When to use it vs alternatives. Performance implications. Real-world scenarios where it shines.

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

FLEXIBLE STRUCTURE (4000-6000+ words - COMPREHENSIVE tutorial):

⚠️ DO NOT limit yourself - write as much as needed to FULLY explain the topic.
⚠️ DO NOT use same headings every time. Be natural and varied.
⚠️ WRITE THE COMPLETE TUTORIAL - not a teaser or overview.

SAMPLE NATURAL INTRO (vary based on style):
- Story style: \"Last month, our team ran into...\"
- Technical: \"Here's how X actually works under the hood...\"
- Tutorial: \"Today we're building... Here's what you need...\"
- Comparative: \"I benchmarked 3 solutions...\"
- Opinion: \"After working with X for 2 years, here's what I learned...\"

MAIN CONTENT (distribute 3500-5500 words naturally - BE COMPREHENSIVE):
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

SEO BEST PRACTICES (CRITICAL FOR RANKING):

📌 Title Formula (Pick one based on content type):
1. How-To: \"How to [Achieve] Without [Pain]\" (Example: \"How to Learn AI Without Getting Overwhelmed\")
2. Lists: \"[Odd Number] [Noun] Every [Audience] Needs in 2025\" (Example: \"7 AI Tools Every Developer Needs in 2025\")
3. Questions: \"What Is [Concept] and Why Does It Matter?\" (Example: \"What Is Prompt Engineering and Why It Matters?\")
4. Comparisons: \"[A] vs [B]: Which [Outcome]?\" (Example: \"ChatGPT vs Claude: Which AI Is Better for Code?\")
5. Guides: \"The Complete Guide to [Topic]\" (Example: \"The Complete Guide to Prompt Engineering\")
6. News: \"[Company] [Action]: [Impact]\" (Example: \"OpenAI Releases GPT-5: Here's What Changed\")

💡 Meta Description Formula (135-160 chars exactly):
- Start with primary keyword
- Include specific benefit/outcome
- Use action word (\"Learn\", \"Discover\", \"Master\", \"Build\")
- Example: \"Master prompt engineering techniques to unlock AI's full potential. Learn 7 proven strategies used by professionals.\"

🔗 Internal Linking Strategy:
- Include 5-10 internal links per 2,000 word article
- 1 link approximately every 200-300 words
- Anchor text: Use keyword variations (never \"click here\")
- Distribution: Spread throughout, not clustered at beginning/end
- Link to: Related posts, pillar pages, category pages
- Natural placement within sentences

✍️ E-E-A-T Signals (EXPERTISE, EXPERIENCE, AUTHORITY, TRUSTWORTHINESS):
- Show credentials: \"As a senior engineer at [Company]...\" or \"With 10 years in...\"
- Share personal experience: \"We encountered this when...\" or \"I learned this the hard way...\"
- Cite credible sources: Link to official docs, GitHub repos, research papers
- Be transparent: \"Here's where I'm not an expert...\" or \"This approach has limitations...\"
- Include author credibility signals

📊 Content Length Guidelines:
- 400-600 words: Breaking news or quick tips
- 600-800 words: Short how-tos or opinion pieces
- 1,500-2,000 words: Full tutorials and guides
- 2,000-2,500 words: In-depth guides and comprehensive comparisons
- 1,800-2,200 words: Comparison articles

🎯 Keyword Integration:
- Use primary keyword in: Title (essential), first 100 words (essential), at least 1x per section
- Use keyword variations naturally: \"prompt engineering\", \"prompting techniques\", \"prompt design\"
- Don't force keywords: Write naturally first, optimize second
- Long-tail keywords: Include related 3-5 word phrases naturally
- LSI keywords: Use semantic variations (not just exact matches)

📋 Content Structure for Google:
- H1: Main title (only 1 per page)
- H2: Major sections (3-5 for 2000 word article)
- H3: Subsections (break up long sections)
- Lists/bullets: Use for scannability
- Code blocks: With language specified
- Tables: For comparisons
- Images: With descriptive alt text

FORMATTING RULES:
- Use ## for main headings (H2), ### for subheadings (H3)
- Add code blocks with language specification: ```javascript, ```python, etc.
- CRITICAL: Keep code examples clear and simple for JSON compatibility
- Use **bold** for key terms, *italics* for emphasis
- Add > blockquotes for important notes
- Use tables for comparisons
- Keep paragraphs short and punchy (2-4 sentences max)
- When in JSON mode, ensure proper escaping of all special characters
- ⚠️ DO NOT START YOUR CONTENT WITH \"Excerpt\", \"Meta Title\", \"Meta Description\", \"Keywords\", or \"Tags\" - these are METADATA fields that go in the JSON response, NOT in the article markdown itself

TONE:
- Professional and educational
- Helpful and supportive
- Enthusiastic but realistic about technology
- ALWAYS honest about trade-offs and limitations
- Like a senior developer mentoring with real-world experience
- NO hype, NO clickbait, NO exaggeration

METADATA FIELDS (DO NOT INCLUDE THESE IN THE ARTICLE CONTENT):
Generate these separately and return in the JSON response:
- **Excerpt**: Educational hook with specific benefit (150-200 chars, NO clickbait)
- **Meta title**: Professional title with specifics (60 chars max, NO hype)
- **Meta description**: Clear value prop with realistic expectations (155 chars max)
- **Keywords**: 5-7 high-intent, searchable technical terms
- **Tags**: 3-5 relevant, specific topic tags

⚠️ CRITICAL: The \"content\" field should ONLY contain the article markdown. DO NOT include the Excerpt, Meta Title, Meta Description, Keywords, or Tags in the article content itself. Those fields belong ONLY in the JSON response fields, not in the content markdown.

Return ONLY this JSON (ensure proper escaping):
{
  \"title\": \"[Professional, educational title - NO clickbait phrases like '10x', 'turbocharge', 'unlock']\",
  \"content\": \"[ONLY the full markdown article content. NO metadata, NO excerpt, NO meta fields. Start with intro heading, end with conclusion.]\",
  \"excerpt\": \"[Specific benefit that creates curiosity - SEPARATE from content, NOT included in article]\",
  \"meta_title\": \"[SEO title with benefit - SEPARATE from content]\",
  \"meta_description\": \"[Value proposition with outcome - SEPARATE from content]\",
  \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\", \"keyword4\", \"keyword5\"],
  \"tags\": [\"tag1\", \"tag2\", \"tag3\"]
}";

        $response = $this->callOpenAI([
            [
                'role' => 'system',
                'content' => 'You are a senior software engineer and technical educator known for writing comprehensive, professional, and highly practical content. Your articles are deep-research educational pieces (4000-5000 words, 15+ minute reads), honest, and packed with real working code examples and actionable insights from multiple sources. You NEVER use clickbait or exaggerated claims. You write clear, realistic, professional content that developers trust. You MUST return ONLY valid JSON with properly escaped strings. Wrap your response in ```json code blocks.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ], 8000, 0.7, false); // Increased to 8000 for deep research content (4000-5000 word articles)

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
                // Unescape JSON escape sequences (quotes, backslashes, etc.)
                $fields[$field] = stripcslashes($match[1]);
            }
        }

        // Extract content separately (it's large and has newlines)
        if (preg_match('/"content":\s*"(.*?)"\s*,\s*"excerpt"/s', $jsonStr, $match)) {
            // Unescape JSON escape sequences
            $fields['content'] = stripcslashes($match[1]);
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

Create a comprehensive, in-depth tutorial series like you'd find on Medium or high-quality tech blogs.

CRITICAL REQUIREMENTS - MEDIUM-QUALITY STANDARDS:
- Series title must be PROFESSIONAL and EDUCATIONAL (NO clickbait like '10x Faster', 'Turbocharge', 'Unlock')
- Each part should be SUBSTANTIAL - not simple commands, but real solutions to real problems
- Each part must be comprehensive enough to stand alone as a valuable article (4000-6000+ words)
- Parts should cover DIFFERENT aspects/technologies - NOT repetitive patterns
- Focus on real-world, production-ready implementations with actual code and outputs
- Include architecture decisions, trade-offs, gotchas, and best practices
- Show problem → solution → results with real metrics
- Each part covers a DISTINCT topic/technology within the overall theme

VARIETY IN PARTS - Each section should explore DIFFERENT angles:
✅ GOOD (varied parts):
  Part 1: Setting up infrastructure (Docker, Kubernetes basics)
  Part 2: Implementing service mesh (Istio/Linkerd)
  Part 3: Observability stack (Prometheus, Grafana, Jaeger)
  Part 4: CI/CD automation (GitHub Actions, ArgoCD)
  Part 5: Production hardening (security, backups, disaster recovery)

❌ BAD (repetitive):
  Part 1: Basic setup
  Part 2: Configuration
  Part 3: Advanced configuration
  Part 4: More configuration
  Part 5: Final configuration

CONTENT DEPTH - Like Medium articles:
- Don\'t just show \"run this command\" - explain WHY, show outputs, discuss alternatives
- Include real code examples with proper error handling
- Show terminal outputs, logs, query results
- Discuss what went wrong and how you fixed it
- Share production lessons learned

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

⚠️ CRITICAL - MEDIUM-STYLE QUALITY:
- This part must be SUBSTANTIAL (4000-6000+ words) - a complete article, not a simple command list
- Provide DEEP technical content like Medium\'s best engineering articles
- Cover ONE distinct aspect thoroughly rather than multiple topics superficially
- Show REAL problem-solving: what you tried, what failed, what worked, with actual outputs
- Include architecture diagrams, code examples with explanations, terminal outputs
- Each part should explore DIFFERENT technologies/approaches from other parts
- Don\'t just say \"configure X\" - show actual config files, explain each option, show results

CONTENT REQUIREMENTS:
1. **Start with WHY**: Explain the problem this part solves in production scenarios
2. **Show the JOURNEY**: What alternatives exist? Why choose this approach?
3. **Detailed IMPLEMENTATION**: Complete code with comments, configs, and actual file structures
4. **OUTPUTS Matter**: After every command/code block, show what actually happens
5. **GOTCHAS**: Share the bugs you hit, errors you got, how you debugged them
6. **RESULTS**: Show metrics, before/after comparisons, actual performance data

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
        ], 8000, 0.7, false); // Max tokens for comprehensive tutorials, lower temp for consistency

        return $this->parseAIResponse($response);
    }

    private function getBaseContentPrompt(string $conversionStrategy, string $advancedTipsExtra, string $conclusionExtra, bool $isPremium): string
    {
        return "Write a COMPLETE, IN-DEPTH tutorial - NOT a summary or overview.

⚠️ CRITICAL: This is a TUTORIAL, not a blog post. Write the FULL content.
- DO NOT write \"In the next section...\" - WRITE THE FULL SECTION NOW
- DO NOT say \"We'll cover...\" - COVER IT COMPLETELY NOW
- DO NOT summarize or give overviews - PROVIDE FULL IMPLEMENTATIONS
- Think Medium.com's LONGEST technical articles - that's your target
- COMPLETE code examples, COMPLETE explanations, COMPLETE walkthroughs

CONTENT STRATEGY:
{$conversionStrategy}

🎯 TARGET AUDIENCE:
- Mid-to-senior level developers (3+ years experience)
- Professionals who already know the basics
- Looking for production-grade, battle-tested solutions
- Need ADVANCED patterns, not beginner tutorials
- Want to learn things NOT commonly found in documentation
- Want COMPLETE tutorials they can follow end-to-end

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

FLEXIBLE STRUCTURE (4000-6000+ words - COMPREHENSIVE tutorial):

⚠️ DO NOT limit yourself - write as much as needed to FULLY explain the topic.
⚠️ DO NOT use same headings every time. Be natural and varied.
⚠️ WRITE THE COMPLETE TUTORIAL - not a teaser or overview.

SAMPLE NATURAL INTRO (vary based on style):
- Story style: \"Last month, our team ran into...\"
- Technical: \"Here's how X actually works under the hood...\"
- Tutorial: \"Today we're building... Here's what you need...\"
- Comparative: \"I benchmarked 3 solutions...\"
- Opinion: \"After working with X for 2 years, here's what I learned...\"

MAIN CONTENT (distribute 3500-5500 words naturally - BE COMPREHENSIVE):
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

SEO BEST PRACTICES (CRITICAL FOR RANKING):

📌 Title Formula (Pick one based on content type):
1. How-To: \"How to [Achieve] Without [Pain]\" (Example: \"How to Learn AI Without Getting Overwhelmed\")
2. Lists: \"[Odd Number] [Noun] Every [Audience] Needs in 2025\" (Example: \"7 AI Tools Every Developer Needs in 2025\")
3. Questions: \"What Is [Concept] and Why Does It Matter?\" (Example: \"What Is Prompt Engineering and Why It Matters?\")
4. Comparisons: \"[A] vs [B]: Which [Outcome]?\" (Example: \"ChatGPT vs Claude: Which AI Is Better for Code?\")
5. Guides: \"The Complete Guide to [Topic]\" (Example: \"The Complete Guide to Prompt Engineering\")
6. News: \"[Company] [Action]: [Impact]\" (Example: \"OpenAI Releases GPT-5: Here's What Changed\")

💡 Meta Description Formula (135-160 chars exactly):
- Start with primary keyword
- Include specific benefit/outcome
- Use action word (\"Learn\", \"Discover\", \"Master\", \"Build\")
- Example: \"Master prompt engineering techniques to unlock AI's full potential. Learn 7 proven strategies used by professionals.\"

🔗 Internal Linking Strategy:
- Include 5-10 internal links per 2,000 word article
- 1 link approximately every 200-300 words
- Anchor text: Use keyword variations (never \"click here\")
- Distribution: Spread throughout, not clustered at beginning/end
- Link to: Related posts, pillar pages, category pages
- Natural placement within sentences

✍️ E-E-A-T Signals (EXPERTISE, EXPERIENCE, AUTHORITY, TRUSTWORTHINESS):
- Show credentials: \"As a senior engineer at [Company]...\" or \"With 10 years in...\"
- Share personal experience: \"We encountered this when...\" or \"I learned this the hard way...\"
- Cite credible sources: Link to official docs, GitHub repos, research papers
- Be transparent: \"Here's where I'm not an expert...\" or \"This approach has limitations...\"
- Include author credibility signals

📊 Content Length Guidelines:
- 400-600 words: Breaking news or quick tips
- 600-800 words: Short how-tos or opinion pieces
- 1,500-2,000 words: Full tutorials and guides
- 2,000-2,500 words: In-depth guides and comprehensive comparisons
- 1,800-2,200 words: Comparison articles

🎯 Keyword Integration:
- Use primary keyword in: Title (essential), first 100 words (essential), at least 1x per section
- Use keyword variations naturally: \"prompt engineering\", \"prompting techniques\", \"prompt design\"
- Don't force keywords: Write naturally first, optimize second
- Long-tail keywords: Include related 3-5 word phrases naturally
- LSI keywords: Use semantic variations (not just exact matches)

📋 Content Structure for Google:
- H1: Main title (only 1 per page)
- H2: Major sections (3-5 for 2000 word article)
- H3: Subsections (break up long sections)
- Lists/bullets: Use for scannability
- Code blocks: With language specified
- Tables: For comparisons
- Images: With descriptive alt text

FORMATTING RULES:
- Use ## for main headings (H2), ### for subheadings (H3)
- Add code blocks with language specification: ```javascript, ```python, etc.
- CRITICAL: Keep code examples clear and simple for JSON compatibility
- Use **bold** for key terms, *italics* for emphasis
- Add > blockquotes for important notes
- Use tables for comparisons
- Keep paragraphs short and punchy (2-4 sentences max)
- When in JSON mode, ensure proper escaping of all special characters
- ⚠️ DO NOT START YOUR CONTENT WITH \"Excerpt\", \"Meta Title\", \"Meta Description\", \"Keywords\", or \"Tags\" - these are METADATA fields that go in the JSON response, NOT in the article markdown itself

TONE:
- Professional and educational
- Helpful and supportive
- Enthusiastic but realistic about technology
- ALWAYS honest about trade-offs and limitations
- Like a senior developer mentoring with real-world experience
- NO hype, NO clickbait, NO exaggeration

METADATA FIELDS (DO NOT INCLUDE THESE IN THE ARTICLE CONTENT):
Generate these separately and return in the JSON response:
- **Excerpt**: Educational hook with specific benefit (150-200 chars, NO clickbait)
- **Meta title**: Professional title with specifics (60 chars max, NO hype)
- **Meta description**: Clear value prop with realistic expectations (155 chars max)
- **Keywords**: 5-7 high-intent, searchable technical terms
- **Tags**: 3-5 relevant, specific topic tags

⚠️ CRITICAL: The \"content\" field should ONLY contain the article markdown. DO NOT include the Excerpt, Meta Title, Meta Description, Keywords, or Tags in the article content itself. Those fields belong ONLY in the JSON response fields, not in the content markdown.

Return ONLY this JSON (ensure proper escaping):
{
  \"title\": \"[Professional, educational title - NO clickbait phrases like '10x', 'turbocharge', 'unlock']\",
  \"content\": \"[ONLY the full markdown article content. NO metadata, NO excerpt, NO meta fields. Start with intro heading, end with conclusion.]\",
  \"excerpt\": \"[Specific benefit that creates curiosity - SEPARATE from content, NOT included in article]\",
  \"meta_title\": \"[SEO title with benefit - SEPARATE from content]\",
  \"meta_description\": \"[Value proposition with outcome - SEPARATE from content]\",
  \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\", \"keyword4\", \"keyword5\"],
  \"tags\": [\"tag1\", \"tag2\", \"tag3\"]
}";
    }

    /**
     * Build research context from gathered sources
     * Formats research data for inclusion in AI prompts
     */
    private function buildResearchContext(array $research): string
    {
        if (empty($research['sources']) || empty(array_filter($research['sources']))) {
            return '';
        }

        $context = "📚 RESEARCH CONTEXT (from multiple authoritative sources):\n\n";

        // Add key insights
        if (!empty($research['keyInsights'])) {
            $context .= "KEY INSIGHTS FROM RESEARCH:\n";
            foreach (array_slice($research['keyInsights'], 0, 5) as $insight) {
                $context .= "- {$insight}\n";
            }
            $context .= "\n";
        }

        // Add best practices
        if (!empty($research['bestPractices'])) {
            $context .= "BEST PRACTICES IDENTIFIED:\n";
            foreach (array_slice($research['bestPractices'], 0, 5) as $practice) {
                $context .= "- {$practice}\n";
            }
            $context .= "\n";
        }

        // Add case studies
        if (!empty($research['caseStudies'])) {
            $context .= "REAL-WORLD CASE STUDIES:\n";
            foreach (array_slice($research['caseStudies'], 0, 3) as $study) {
                $context .= "- {$study}\n";
            }
            $context .= "\n";
        }

        // Add source summary
        $sources = array_filter($research['sources']);
        if (!empty($sources)) {
            $context .= "SOURCES: Aggregated from " . count($sources) . " authoritative sources (Medium, Dev.to, HackerNews, GitHub)\n";
            $context .= "Use this research context to write a post that synthesizes these real-world insights into practical, actionable content.\n\n";
        }

        return $context;
    }
}
