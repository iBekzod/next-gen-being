<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AITutorialGenerationService
{
    private const CLAUDE_MODEL = 'claude-sonnet-4-5';
    private const API_TIMEOUT = 240;  // 4 minutes - allows for large token responses (12000+ tokens)
    private const MAX_RETRIES = 3;
    private const TOKEN_BUDGET = 16000;  // Claude Sonnet 4.5: 16K for complete 4000-6000 word tutorial parts

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
     * Generate complete tutorial series with multiple parts
     *
     * @param string $topic - Tutorial topic
     * @param int $parts - Number of parts (3, 5, 8)
     * @param bool $publish - Auto-publish or save as draft
     * @return array - Created posts array
     */
    public function generateComprehensiveSeries(
        string $topic,
        int $parts = 8,
        bool $publish = false
    ): array {
        Log::info("Starting tutorial generation", ['topic' => $topic, 'parts' => $parts]);

        $author = $this->getOrCreateAuthor();
        $category = $this->getOrCreateCategory();
        $seriesTitle = $this->generateSeriesTitle($topic, $parts);
        $createdPosts = [];

        for ($partNumber = 1; $partNumber <= $parts; $partNumber++) {
            try {
                Log::info("Generating part {$partNumber}/{$parts}", ['topic' => $topic]);

                // Generate content with retry logic
                $content = $this->generatePartWithRetry($topic, $partNumber, $parts, $seriesTitle);

                if (!$content) {
                    Log::error("Failed to generate part {$partNumber} after retries", ['topic' => $topic]);
                    continue;
                }

                // Validate content quality
                if (!$this->validateContentQuality($content)) {
                    Log::warning("Content quality check failed for part {$partNumber}", ['topic' => $topic]);
                    // Still create post but mark as draft
                    $publish = false;
                }

                // Create post
                $post = $this->createPost(
                    author: $author,
                    category: $category,
                    topic: $topic,
                    partNumber: $partNumber,
                    totalParts: $parts,
                    content: $content,
                    seriesTitle: $seriesTitle,
                    publish: $publish
                );

                $createdPosts[] = $post;

                Log::info("Created post", [
                    'post_id' => $post->id,
                    'part' => $partNumber,
                    'title' => $post->title,
                ]);

            } catch (\Exception $e) {
                Log::error("Error generating part {$partNumber}", [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }

            // Rate limiting - wait between requests
            if ($partNumber < $parts) {
                sleep(2);
            }
        }

        Log::info("Tutorial generation complete", [
            'topic' => $topic,
            'created_posts' => count($createdPosts),
        ]);

        return $createdPosts;
    }

    /**
     * Generate comprehensive tutorial content for manual creation and enhancement
     * Does NOT create posts - returns content array for caller to handle
     */
    public function generateComprehensiveTutorial(string $topic, int $parts = 8): array
    {
        $seriesTitle = $this->generateSeriesTitle($topic, $parts);
        $contentParts = [];

        for ($partNumber = 1; $partNumber <= $parts; $partNumber++) {
            try {
                Log::info("Generating content for part {$partNumber}/{$parts}", ['topic' => $topic]);

                // Generate content with retry logic
                $content = $this->generatePartWithRetry($topic, $partNumber, $parts, $seriesTitle);

                if (!$content) {
                    Log::error("Failed to generate content for part {$partNumber} after retries", ['topic' => $topic]);
                    continue;
                }

                // Validate content quality
                if (!$this->validateContentQuality($content)) {
                    Log::warning("Content quality check failed for part {$partNumber}", ['topic' => $topic]);
                }

                $contentParts[] = [
                    'title' => $this->extractTitle($topic, $partNumber, $parts),
                    'excerpt' => $this->extractExcerpt($content),
                    'content' => $content,
                ];

                // Rate limiting - wait between requests
                if ($partNumber < $parts) {
                    sleep(2);
                }

            } catch (\Exception $e) {
                Log::error("Error generating content for part {$partNumber}", [
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("Tutorial content generation complete", [
            'topic' => $topic,
            'generated_parts' => count($contentParts),
        ]);

        return $contentParts;
    }

    /**
     * Generate single part with retry logic
     */
    private function generatePartWithRetry(
        string $topic,
        int $partNumber,
        int $totalParts,
        string $seriesTitle,
        int $attempt = 1
    ): ?string {
        try {
            $prompt = $this->buildPrompt($topic, $partNumber, $totalParts, $seriesTitle);
            return $this->callClaudeAPI($prompt);
        } catch (\Exception $e) {
            if ($attempt < self::MAX_RETRIES) {
                Log::warning("API call failed, retrying", [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
                sleep(5 * $attempt); // Exponential backoff
                return $this->generatePartWithRetry($topic, $partNumber, $totalParts, $seriesTitle, $attempt + 1);
            }
            throw $e;
        }
    }

    /**
     * Build comprehensive prompt for Claude
     */
    private function buildPrompt(
        string $topic,
        int $partNumber,
        int $totalParts,
        string $seriesTitle
    ): string {
        $partFocus = $this->getPartFocus($topic, $partNumber, $totalParts);

        return <<<PROMPT
You are a senior software engineer creating a comprehensive production-grade tutorial series.

AUTHOR & REPOSITORY INFO:
- GitHub: https://github.com/iBekzod
- Blog: https://nextgenbeing.com
- IMPORTANT: Only reference these EXISTING repositories on iBekzod GitHub - do not invent new repo names:
  * https://github.com/iBekzod/laravel-saas-platform (SaaS tutorials)
  * https://github.com/iBekzod/laravel-rest-api-tutorial (REST API tutorials)
  * https://github.com/iBekzod/laravel-ecommerce-platform (E-commerce tutorials)
  * https://github.com/iBekzod/laravel-scalable-api (general Laravel scaling)
  * https://github.com/iBekzod/aws-lambda-cloud-native (Lambda/serverless)
  * https://github.com/iBekzod/aws-serverless-api-gateway (API Gateway)
- If your tutorial topic does NOT map cleanly to one of these repos, OMIT the repo link entirely. Do not invent a new repo name.
- The companion blog (https://nextgenbeing.com) is always safe to link.
- NEVER use placeholder text like "your-repo", "@yourusername", "your-invite" - always use the real iBekzod GitHub profile

SERIES METADATA:
- Title: {$seriesTitle}
- Topic: {$topic}
- Part: {$partNumber} of {$totalParts}
- Focus: {$partFocus}

CONTENT REQUIREMENTS:

1. **Audience & Quality** (CRITICAL):
   - Write for INTERMEDIATE to ADVANCED developers (3+ years experience)
   - Assume audience understands basic concepts, not for beginners
   - Focus on real-world implementation patterns used in production
   - Include common pitfalls and solutions (what we've learned the hard way)
   - Reference industry best practices from companies like Netflix, Stripe, Uber
   - Share lessons learned from actual production deployments

2. **Code Examples** (CRITICAL - THIS IS THE HEART OF THE TUTORIAL):
   - EVERY code block must be COMPLETE, RUNNABLE, and COPY-PASTE READY
   - Show FULL code with imports, configuration, setup - not snippets
   - Include: error handling, validation, logging, and monitoring code
   - Show MULTIPLE approaches with pros/cons explained
   - Include database migrations, config files, environment setup
   - Add inline comments explaining WHY this approach, not just WHAT it does
   - Use current stable versions (Laravel 12, PHP 8.4, AWS SDK 3.x, etc)
   - Include actual command output and terminal examples
   - Show debugging techniques and troubleshooting steps
   - Add performance testing code or load-testing examples

3. **Content Structure** (MUST BE COMPREHENSIVE):
   - Table of contents with quick navigation at top
   - Logical progression: foundation → complexity → optimization
   - Real-world problem scenario that motivates the solution
   - Step-by-step implementation guide you can follow exactly
   - Architecture diagrams in ASCII/text when helpful
   - Before/after comparisons with actual metrics/numbers
   - Common mistakes section (what NOT to do)
   - Key takeaways and lessons learned
   - Preview/teaser of next part

4. **Technical Depth** (GO DEEPER THAN OBVIOUS):
   - Explain architecture patterns and WHY they work
   - Discuss performance implications with actual numbers
   - Address security considerations (not just "use HTTPS")
   - Include scaling strategies for real-world growth
   - Troubleshooting section: common issues and solutions
   - Integration patterns with popular tools/services
   - Edge cases and gotchas specific to this technology
   - When to use this solution vs alternatives

5. **Format & Style** (PROFESSIONAL TUTORIAL QUALITY):
   - Proper Markdown with ### Headers, #### Subheaders
   - Use code blocks with language tags (```php, ```sql, ```bash, etc)
   - **Bold** important concepts and critical points
   - > Blockquotes for important notes and warnings
   - Use tables for comparisons and specification matrices
   - Include actual terminal/CLI examples with $ prompts and output
   - Use consistent code style throughout
   - Estimated read time: ~{$this->estimatePartReadTime($partNumber, $totalParts)} minutes

6. **Length & Completeness** (CRITICAL FOR PRODUCTION-GRADE):
   - MINIMUM 2500 words (this is non-negotiable for this length)
   - TARGET 3500-4500 words (18-22 minute deep dive)
   - Enough code to FULLY understand and implement feature
   - Must be immediately implementable into production systems
   - Thoroughly answer: WHAT (what is it), WHY (why do this), HOW (how to implement), WHEN (when to use)
   - Include edge cases, gotchas, lessons learned
   - Address common questions before people ask them
   - Every section MUST add value - NO filler, fluff, or repetition

PART SPECIFICS FOR {$partNumber}:

{$this->getPartSpecificGuidance($partNumber, $totalParts)}

PRODUCTION READINESS CHECKLIST (EVERY CODE EXAMPLE MUST HAVE):
✅ Proper error handling (try-catch, if statements, validation)
✅ Logging statements for debugging
✅ Configuration management (environment variables, config files)
✅ Security considerations (input validation, authorization checks)
✅ Performance implications mentioned
✅ Database migration or schema shown
✅ Tests or verification steps
✅ Real-world context and use case

**ANTI-FABRICATION RULES (CRITICAL - GOOGLE AND READERS WILL CATCH THIS):**

Inventing technical facts is the fastest way to destroy credibility. The following lead to INSTANT FAIL:

A. **VERSION NUMBERS MUST BE REAL.** Never invent version numbers. If you are unsure of a version, write 'a recent stable version' instead. Examples of forbidden invention: 'Pyston 1.2' (Pyston was discontinued, no such version), 'Laravel 13' (does not exist as of writing), 'PHP 8.5' (check first).

B. **API METHODS AND CLASS NAMES MUST BE REAL.** Do not invent methods like 'graph.add_nodes()' that look plausible but are not in the actual library. If you are not 100 percent sure a method exists, either: (1) reference the official docs URL where it lives, or (2) use a different, simpler example with stdlib functions you are certain about.

C. **STATS AND BENCHMARKS MUST BE SHOWN, NOT CLAIMED.** Forbidden: 'reduced energy waste by 15 percent', '10,000 requests per second with sub-10ms latency'. If you write a number, you MUST also show: the test setup, hardware/instance specs, the command used, and the raw output. If you cannot show all three, REMOVE the number.

D. **NEVER FABRICATE COMPANY CASE STUDIES.** Forbidden: 'A utility company we worked with reduced costs by 30 percent.' If the story is not directly verifiable, frame it as a hypothesis ('Here is the math on how this would work for a utility with N customers...') rather than as a past event.

E. **DATES MUST BE PLAUSIBLE.** Do not date posts in the future. Do not reference events that have not happened.

**ANTI-REPETITION RULES (DESTROYS QUALITY SCORE):**

F. **NO SCENARIO MAY REPEAT.** A scenario like 'Suppose a utility company wants to optimize energy distribution' or 'To illustrate the power of X, let us consider an example' may appear AT MOST ONCE in the entire article. If you find yourself reusing a setup, you are padding - cut it and move on.

G. **NO PARAGRAPH TEMPLATE MAY BE REUSED.** If section 3 starts with 'To illustrate, let us consider...' then section 5 cannot start the same way. Vary openings.

H. **EVERY CODE EXAMPLE MUST BE DIFFERENT.** Do not show the same RabbitMQ pub/sub pattern twice with cosmetic changes. Each code block must demonstrate something the previous ones did not.

I. **WORD-LEVEL UNIQUENESS:** Avoid using these phrases more than twice in the whole article: 'in conclusion', 'furthermore', 'moreover', 'to illustrate', 'real-world example', 'as developers'.

**SOURCE CITATION RULES:**

J. When citing external information, link to the canonical URL ('https://laravel.com/docs/12.x/eloquent') not a vague reference ('the Laravel documentation'). If you cannot produce a real URL, do not cite.

K. Inline links are preferred over a 'References' section. If you have a references list, every entry must be a working URL.

CRITICAL SUCCESS FACTORS:
1. Every code block MUST be copy-paste ready and runnable
2. Include actual command output, not just "here's what happens"
3. Show what the user will see, not just what the code does
4. Explain trade-offs and why you chose this approach
5. Make it obvious what to change for their specific use case
6. Include troubleshooting section for common issues

Now generate Part {$partNumber} of "{$seriesTitle}" focusing on {$partFocus}.

Generate comprehensive, production-grade tutorial content that senior developers would reference in their projects.
Focus on depth, practical code, and real-world applicability. Make it so valuable they bookmark and share it.
PROMPT;
    }

    /**
     * Call Claude API with proper error handling
     */
    private function callClaudeAPI(string $prompt): ?string
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->timeout(self::API_TIMEOUT)
            ->post($this->baseUrl, [
                'model' => self::CLAUDE_MODEL,
                'max_tokens' => self::TOKEN_BUDGET,  // Increased for deeper, more comprehensive tutorial content
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Claude API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception("API returned status {$response->status()}");
            }

            $data = $response->json();

            if (!isset($data['content'][0]['text'])) {
                throw new \Exception('Invalid API response format');
            }

            $text = $data['content'][0]['text'];
            $stopReason = $data['stop_reason'] ?? '';

            // Auto-continue if response was truncated by max_tokens
            $continuationCount = 0;
            while ($stopReason === 'max_tokens' && $continuationCount < 2) {
                $continuationCount++;
                Log::info('Tutorial response truncated, requesting continuation', ['attempt' => $continuationCount]);

                $contResponse = Http::withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->timeout(self::API_TIMEOUT)
                ->post($this->baseUrl, [
                    'model' => self::CLAUDE_MODEL,
                    'max_tokens' => self::TOKEN_BUDGET,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                        ['role' => 'assistant', 'content' => $text],
                        ['role' => 'user', 'content' => 'Continue exactly where you left off. Do not repeat anything. Do not add any introduction or summary. Just continue the text directly.'],
                    ],
                ]);

                if (!$contResponse->successful()) {
                    break;
                }

                $contData = $contResponse->json();
                if (!isset($contData['content'][0]['text'])) {
                    break;
                }

                $text .= $contData['content'][0]['text'];
                $stopReason = $contData['stop_reason'] ?? '';
            }

            return $text;

        } catch (\Exception $e) {
            Log::error('Claude API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate content quality metrics
     */
    private function validateContentQuality(string $content): bool
    {
        $checks = [
            'has_headers' => $this->hasProperHeaders($content),
            'has_code_blocks' => $this->hasCodeBlocks($content),
            'sufficient_length' => $this->checkLength($content),
            'has_sections' => $this->hasSections($content),
        ];

        $passedChecks = array_filter($checks);
        $passRate = count($passedChecks) / count($checks);

        Log::info('Content quality validation', [
            'checks' => $checks,
            'pass_rate' => $passRate,
        ]);

        // Require 75% of checks to pass
        return $passRate >= 0.75;
    }

    private function hasProperHeaders(string $content): bool
    {
        return preg_match('/^#{1,4}\s+.+$/m', $content) > 0;
    }

    private function hasCodeBlocks(string $content): bool
    {
        return preg_match('/```[\w]*\n.+\n```/s', $content) > 0;
    }

    private function checkLength(string $content): bool
    {
        $wordCount = str_word_count(strip_tags($content));
        // Strengthened requirement: minimum 2000 words for production-grade tutorial
        $isValid = $wordCount >= 2000;

        if (!$isValid) {
            Log::warning("Tutorial content below minimum word count", [
                'required' => 2000,
                'actual' => $wordCount,
            ]);
        }

        return $isValid;
    }

    private function hasSections(string $content): bool
    {
        $headingCount = preg_match_all('/^#{2,4}\s+.+$/m', $content);
        return $headingCount >= 3; // At least 3 sections
    }

    /**
     * Create post in database
     */
    private function createPost(
        User $author,
        Category $category,
        string $topic,
        int $partNumber,
        int $totalParts,
        string $content,
        string $seriesTitle,
        bool $publish
    ): Post {
        $title = $this->extractTitle($topic, $partNumber, $totalParts);
        $excerpt = $this->extractExcerpt($content);
        $readTime = $this->estimateReadTime($content);

        $seriesSlug = \Illuminate\Support\Str::slug($seriesTitle);
        $featuredImage = $this->generateFeaturedImageForPart($title, $topic);

        $postData = [
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => $title,
            'slug' => null, // Auto-generated
            'excerpt' => $excerpt,
            'content' => $content,
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? now() : null,
            'is_featured' => $partNumber === 1,
            'allow_comments' => true,
            'is_premium' => false,
            'read_time' => $readTime,
            'series_title' => $seriesTitle,
            'series_slug' => $seriesSlug,
            'series_part' => $partNumber,
            'series_total_parts' => $totalParts,
            'series_description' => "A comprehensive {$totalParts}-part tutorial series on {$topic}.",
            'seo_meta' => [
                'title' => $title,
                'description' => $excerpt,
                'keywords' => $this->generateKeywords($topic, $partNumber),
            ],
        ];

        if ($featuredImage && isset($featuredImage['url'])) {
            $postData['featured_image'] = $featuredImage['url'];
        }

        return Post::create($postData);
    }

    private function generateFeaturedImageForPart(string $title, string $topic): ?array
    {
        try {
            $imageService = app(\App\Services\ImageGenerationService::class);
            if (!$imageService->isAvailable()) {
                return null;
            }
            return $imageService->generateFeaturedImage($title, $topic);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Tutorial featured image failed', [
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Helper methods for title/excerpt/metadata generation
     */
    private function generateSeriesTitle(string $topic, int $parts): string
    {
        return "{$topic} - Complete {$parts}-Part Production Guide";
    }

    private function getPartFocus(string $topic, int $partNumber, int $totalParts): string
    {
        $focusMap = [
            1 => 'Architecture, Setup & Foundations',
            2 => 'Core Implementation & Design Patterns',
            3 => 'Advanced Features & Configuration',
            4 => 'Integration & Third-party Services',
            5 => 'Containerization & Deployment',
            6 => 'Scaling, Performance & Optimization',
            7 => 'Testing, Security & Best Practices',
            8 => 'Production Deployment & Monitoring',
        ];

        return $focusMap[$partNumber] ?? "Advanced Topics - Part {$partNumber}";
    }

    private function getPartSpecificGuidance(int $partNumber, int $totalParts): string
    {
        $guidanceMap = [
            1 => "PART 1 - FOUNDATIONS & SETUP:\n"
                . "- Explain WHY this technology matters and when to use it\n"
                . "- Architecture overview with ASCII diagrams\n"
                . "- Complete environment setup with all dependencies\n"
                . "- Initial project configuration and scaffolding\n"
                . "- First working example (small but complete)\n"
                . "- Common setup mistakes and how to avoid them",

            2 => "PART 2 - CORE IMPLEMENTATION:\n"
                . "- Deep dive into main functionality\n"
                . "- Complete working code with error handling\n"
                . "- Design patterns used and WHY\n"
                . "- Database schema, migrations, and queries\n"
                . "- Authentication and authorization implementation\n"
                . "- API endpoints with full request/response examples",

            3 => "PART 3 - ADVANCED FEATURES:\n"
                . "- Complex features and edge cases\n"
                . "- Integration with third-party services\n"
                . "- Caching strategies and implementation\n"
                . "- Background jobs and queue processing\n"
                . "- WebSocket or real-time updates\n"
                . "- Advanced configuration options",

            4 => "PART 4 - SCALING & OPTIMIZATION:\n"
                . "- Performance optimization techniques\n"
                . "- Database query optimization (EXPLAIN analysis)\n"
                . "- Caching layers and CDN integration\n"
                . "- Load balancing strategies\n"
                . "- Horizontal scaling considerations\n"
                . "- Benchmarking and metrics",

            5 => "PART 5 - DEPLOYMENT & DEVOPS:\n"
                . "- Containerization with Docker\n"
                . "- Kubernetes orchestration (if applicable)\n"
                . "- CI/CD pipeline setup\n"
                . "- Infrastructure as Code\n"
                . "- Blue-green deployments\n"
                . "- Monitoring and alerting setup",

            6 => "PART 6 - SECURITY & BEST PRACTICES:\n"
                . "- Security considerations and hardening\n"
                . "- Input validation and sanitization\n"
                . "- OWASP top 10 relevance\n"
                . "- Data protection and encryption\n"
                . "- Rate limiting and DDoS protection\n"
                . "- Security testing and auditing",

            7 => "PART 7 - TESTING & RELIABILITY:\n"
                . "- Unit testing with complete examples\n"
                . "- Integration testing strategies\n"
                . "- Load testing and stress testing\n"
                . "- Error handling and recovery\n"
                . "- Debugging techniques\n"
                . "- Logging and tracing",

            8 => "PART 8 - PRODUCTION & BEYOND:\n"
                . "- Production deployment checklist\n"
                . "- Monitoring in production\n"
                . "- Incident response procedures\n"
                . "- Cost optimization\n"
                . "- Scaling strategies beyond current setup\n"
                . "- Next steps and advanced topics to explore"
        ];

        return $guidanceMap[$partNumber] ?? "PART {$partNumber} - ADVANCED TOPICS:\n"
            . "- Dive deep into implementation details with complete code examples\n"
            . "- Include best practices, patterns, and anti-patterns\n"
            . "- Reference other parts of the series\n"
            . "- Prepare transition to next topic";
    }

    private function estimatePartReadTime(int $partNumber, int $totalParts): int
    {
        if ($partNumber === 1) return 25;
        if ($partNumber === $totalParts) return 30;
        return 20 + ($partNumber % 3) * 5;
    }

    private function extractTitle(string $topic, int $partNumber, int $totalParts): string
    {
        $focus = $this->getPartFocus($topic, $partNumber, $totalParts);
        return "{$topic} - Part {$partNumber}: {$focus}";
    }

    private function extractExcerpt(string $content): string
    {
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '#') && strlen($line) > 80) {
                return substr($line, 0, 160) . '...';
            }
        }
        return "Comprehensive production-grade tutorial with code examples and best practices.";
    }

    private function estimateReadTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return ceil($wordCount / 200);
    }

    private function generateKeywords(string $topic, int $partNumber): string
    {
        return implode(',', [
            'production',
            'tutorial',
            'implementation',
            'best practices',
            Str::lower($topic),
            "part {$partNumber}",
        ]);
    }

    private function getOrCreateAuthor(): User
    {
        // Pick a random blogger (users with 'blogger' role)
        $author = User::whereHas('roles', function ($query) {
            $query->where('slug', 'blogger');
        })->inRandomOrder()->first();

        // Fallback: any active seeded blogger
        if (!$author) {
            $author = User::where('email', 'like', '%@nextgenbeing.com')
                ->where('is_active', true)
                ->inRandomOrder()
                ->first();
        }

        // Fallback: create a default author
        if (!$author) {
            $author = User::firstOrCreate(
                ['email' => 'ai-tutorials@' . config('app.domain', 'example.com')],
                [
                    'name' => 'Tech Editorial Team',
                    'password' => bcrypt(Str::random(32)),
                    'email_verified_at' => now(),
                ]
            );
        }

        return $author;
    }

    private function getOrCreateCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'comprehensive-tutorials'],
            [
                'name' => 'Comprehensive Tutorials',
                'is_active' => true,
            ]
        );
    }
}
