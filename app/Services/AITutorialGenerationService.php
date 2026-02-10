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
    private const CLAUDE_MODEL = 'claude-sonnet-4-5-20250929';
    private const API_TIMEOUT = 180;  // 3 minutes - allows for large token responses (8000 tokens)
    private const MAX_RETRIES = 3;

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
     * Wrapper method for backward compatibility
     * Generate comprehensive tutorial and return as array format
     */
    public function generateComprehensiveTutorial(string $topic, int $parts = 8): array
    {
        $posts = $this->generateComprehensiveSeries($topic, $parts, true);

        // Convert to content array format for compatibility
        return array_map(function ($post) {
            return [
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
            ];
        }, $posts);
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

SERIES METADATA:
- Title: {$seriesTitle}
- Topic: {$topic}
- Part: {$partNumber} of {$totalParts}
- Focus: {$partFocus}

CONTENT REQUIREMENTS:

1. **Audience & Quality** (CRITICAL):
   - Write for INTERMEDIATE to ADVANCED developers
   - Assume audience understands basic concepts
   - Focus on real-world implementation patterns
   - Include common pitfalls and solutions
   - Reference industry best practices

2. **Code Examples** (CRITICAL):
   - Every example must be COMPLETE and RUNNABLE
   - Include full code, not snippets
   - Add error handling and validation
   - Show multiple approaches when relevant
   - Include database migrations, configuration files
   - Add inline comments explaining WHY not just WHAT
   - Use recent versions (Laravel 12, PHP 8.4, etc)

3. **Content Structure**:
   - Clear table of contents at top
   - Logical progression through topic
   - Real-world problem solving
   - Step-by-step implementation guide
   - Architecture diagrams in ASCII when helpful
   - Key takeaways at end
   - Preview of next part

4. **Technical Depth**:
   - Cover architecture patterns
   - Discuss performance implications
   - Address security considerations
   - Include scaling strategies
   - Troubleshooting common issues
   - Integration with popular tools/services

5. **Format & Style**:
   - Use proper Markdown
   - Include ### Headers, #### Subheaders
   - Use code blocks with language tags
   - Bold important concepts (**concept**)
   - Create tables for comparisons
   - Include terminal/CLI examples
   - Add estimated read time (roughly {$this->estimatePartReadTime($partNumber, $totalParts)} minutes)

6. **Length & Completeness** (CRITICAL):
   - MINIMUM 2500 words (10+ minute read)
   - TARGET 3000-4000 words (13-18 minute read)
   - Enough code for full understanding and implementation
   - Practical enough to implement immediately into production
   - Thoroughly answer "how", "why", and "when" questions
   - Include edge cases, gotchas, and lessons learned
   - Every section must add value - no filler content

PART SPECIFICS FOR {$partNumber}:

{$this->getPartSpecificGuidance($partNumber, $totalParts)}

Now generate Part {$partNumber} of "{$seriesTitle}" focusing on {$partFocus}.

Write production-grade content that senior developers would publish on their blogs.
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
                'max_tokens' => 8000,
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

            return $data['content'][0]['text'];

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
        return $wordCount >= 1000; // Minimum 1000 words
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

        return Post::create([
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
            'series_part' => $partNumber,
            'series_total_parts' => $totalParts,
            'series_description' => "A comprehensive {$totalParts}-part tutorial series on {$topic}.",
            'seo_meta' => [
                'title' => $title,
                'description' => $excerpt,
                'keywords' => $this->generateKeywords($topic, $partNumber),
            ],
        ]);
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
        if ($partNumber === 1) {
            return "Include comprehensive setup guide, architecture overview, technology choices, and initial project setup with examples.";
        } elseif ($partNumber === $totalParts) {
            return "Focus on production readiness, deployment strategies, monitoring, and next steps for scaling.";
        } else {
            return "Dive deep into implementation details with complete code examples and best practices.";
        }
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
        return User::firstOrCreate(
            ['email' => 'ai-tutorials@' . config('app.domain', 'example.com')],
            [
                'name' => 'AI Tutorial Generator',
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
                'role' => 'admin',
            ]
        );
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
