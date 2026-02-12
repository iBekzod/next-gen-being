<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WebResearchService;

class DeepResearchContentService
{
    /**
     * Trending topics in AI, programming, and high-tech
     */
    private array $trendingTopics = [
        // AI & LLMs
        'Prompt engineering techniques for production LLMs',
        'Fine-tuning open source LLMs vs API-based models',
        'RAG systems and vector databases in production',
        'LLM hallucinations: causes and mitigation strategies',
        'Cost optimization for LLM APIs at scale',
        'Building AI agents with autonomous capabilities',
        'LangChain vs LlamaIndex: architectural deep dive',
        'Evaluating and benchmarking LLM performance',

        // Advanced Architecture
        'Event sourcing vs CQRS: real-world trade-offs',
        'Distributed tracing in microservices',
        'Building resilient systems with chaos engineering',
        'Database sharding strategies at 100M+ scale',
        'Edge computing for latency-critical applications',
        'GraphQL federation at enterprise scale',

        // Performance & Optimization
        'Database query optimization: the 80/20 rule nobody tells you',
        'Memory leaks in Node.js: debugging techniques',
        'WebAssembly performance gains: real vs marketing',
        'CPU optimization for backend services',
        'Caching strategies beyond Redis',

        // DevOps & Infrastructure
        'Kubernetes cost optimization in production',
        'Observability: metrics vs logs vs traces',
        'Container security best practices',
        'GitOps workflows and tools comparison',
        'Disaster recovery and backup strategies',

        // Programming Patterns
        'Domain-driven design in modern applications',
        'Hexagonal architecture implementation guide',
        'SOLID principles in Python: practical examples',
        'Functional programming concepts in JavaScript',
        'Design patterns that scale',

        // Web Technologies
        'WebSockets vs Server-Sent Events vs polling',
        'React hooks performance pitfalls',
        'Next.js caching strategies for CMS content',
        'TypeScript runtime type checking approaches',
        'Progressive Web Apps in 2024',

        // Security Deep Dives
        'OAuth 2.0 flows and when to use each',
        'Zero-knowledge proofs: practical applications',
        'Supply chain security in open source',
        'API security at scale',
        'Database encryption best practices',
    ];

    /**
     * Generate deep research-based blog post
     */
    public function generateDeepResearchPost(?string $topic = null): array
    {
        $selectedTopic = $topic ?? $this->selectTrendingTopic();

        Log::info('Starting deep research post generation', [
            'topic' => $selectedTopic
        ]);

        // Step 1: Gather research from multiple sources
        $this->info("🔍 Gathering research from multiple sources for: {$selectedTopic}");
        $research = $this->gatherResearch($selectedTopic);

        // Step 2: Synthesize and create original content
        $this->info("✍️  Synthesizing research into original deep content");
        $postContent = $this->synthesizeContent($selectedTopic, $research);

        // Step 3: Ensure minimum depth (15+ min read)
        $this->info("📏 Ensuring content meets depth requirements (15+ min read)");
        $postContent = $this->ensureDepth($postContent, $selectedTopic);

        // Step 4: Add practical examples
        $this->info("💡 Adding practical, runnable examples");
        $postContent = $this->addPracticalExamples($postContent);

        // Step 5: Format and prepare final post
        $finalPost = $this->formatFinalPost($postContent, $selectedTopic);

        return $finalPost;
    }

    /**
     * Select a trending topic
     */
    private function selectTrendingTopic(): string
    {
        return $this->trendingTopics[array_rand($this->trendingTopics)];
    }

    /**
     * Gather research from multiple sources
     * Uses WebResearchService to scrape articles from Medium, Dev.to, HackerNews, etc.
     */
    private function gatherResearch(string $topic): array
    {
        $researchService = app(WebResearchService::class);
        return $researchService->gatherResearch($topic, limit: 3);
    }

    /**
     * Synthesize research into original content
     * Uses Claude to combine multiple sources into unique perspective
     */
    private function synthesizeContent(string $topic, array $research): string
    {
        $prompt = $this->buildSynthesisPrompt($topic, $research);

        // Call Claude API to synthesize
        $response = Http::timeout(180)
            ->withHeader('x-api-key', config('services.anthropic.key'))
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-opus-4-6',
                'max_tokens' => 8000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to synthesize content: ' . $response->body());
        }

        return $response->json('content.0.text');
    }

    /**
     * Build synthesis prompt for Claude
     */
    private function buildSynthesisPrompt(string $topic, array $research): string
    {
        return <<<PROMPT
You are a senior technical writer creating a comprehensive, research-backed blog post.

TOPIC: {$topic}

RESEARCH SUMMARY:
{$this->formatResearch($research)}

REQUIREMENTS FOR THIS POST:
1. **Length**: 15-20 minute read (approximately 4000-5000 words)
2. **Structure**:
   - Compelling hook (why this matters RIGHT NOW)
   - Problem statement with real-world context
   - 5-7 major sections exploring different angles
   - Practical implementation section with code
   - Common pitfalls and how to avoid them
   - Performance/cost trade-offs
   - Conclusion with forward-looking perspective

3. **Authenticity**:
   - Write in first person ("I discovered", "we learned")
   - Include specific numbers/metrics
   - Share one failure story
   - Be opinionated with justification
   - Reference real production scenarios

4. **Technical Depth**:
   - Explain NOT just "what" but "how it breaks"
   - Show before/after scenarios
   - Include edge cases
   - Explain architectural trade-offs
   - Show debugging methodology

5. **Practical Examples**:
   - Include 3-5 runnable code examples
   - Show common mistakes and corrections
   - Provide configuration snippets
   - Include monitoring/debugging tips

6. **Research Integration**:
   - Synthesize the research without plagiarizing
   - Cite specific sources where appropriate
   - Add your unique perspective
   - Combine multiple viewpoints into cohesive narrative

7. **Engagement**:
   - Use clear headings (H2 and H3)
   - Include code blocks with syntax highlighting
   - Add callout boxes for important points
   - Use metrics and real data
   - Tell stories, don't just explain concepts

TONE: Professional but accessible, opinionated, practical, authentic

Generate the complete blog post now:
PROMPT;
    }

    /**
     * Format research for inclusion in prompt
     */
    private function formatResearch(array $research): string
    {
        // In production, format scraped research data
        // For now, return placeholder
        return "Research sources and key findings would be included here.";
    }

    /**
     * Ensure content meets depth requirements (15+ min read)
     */
    private function ensureDepth(string $content, string $topic): string
    {
        $wordCount = str_word_count(strip_tags($content));
        $minWords = 4000; // ~15 min read

        if ($wordCount < $minWords) {
            $prompt = <<<PROMPT
The following blog post about "{$topic}" is only {$wordCount} words but needs to be at least {$minWords} words (15+ minute read).

Expand the content by:
1. Adding more practical examples with code
2. Including case studies or real-world scenarios
3. Explaining technical concepts in more depth
4. Adding troubleshooting/debugging sections
5. Including performance testing results
6. Adding security considerations
7. Including cost/scaling implications

ORIGINAL CONTENT:
{$content}

EXPANDED CONTENT:
PROMPT;

            $response = Http::timeout(180)
                ->withHeader('x-api-key', config('services.anthropic.key'))
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-opus-4-6',
                    'max_tokens' => 8000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
            }
        }

        return $content;
    }

    /**
     * Add practical, runnable examples
     */
    private function addPracticalExamples(string $content): string
    {
        // If content doesn't have enough code examples, enhance it
        $codeBlockCount = preg_match_all('/```[a-z]*\n/i', $content);

        if ($codeBlockCount < 3) {
            $prompt = <<<PROMPT
Add 3-5 practical, production-ready code examples to this content.

Each example should:
1. Show actual implementation
2. Include error handling
3. Demonstrate common pitfalls and how to avoid them
4. Include configuration/setup steps
5. Show performance optimization tips

Content:
{$content}

Enhanced with practical examples:
PROMPT;

            $response = Http::timeout(180)
                ->withHeader('x-api-key', config('services.anthropic.key'))
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-opus-4-6',
                    'max_tokens' => 4000,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text');
            }
        }

        return $content;
    }

    /**
     * Format final post with metadata
     */
    private function formatFinalPost(string $content, string $topic): array
    {
        $wordCount = str_word_count(strip_tags($content));
        $readTime = max(15, ceil($wordCount / 250)); // Reading speed ~250 words/min

        // Generate excerpt
        $excerpt = substr(strip_tags($content), 0, 500) . '...';

        // Generate slug
        $slug = \Illuminate\Support\Str::slug($topic);

        // Extract first image or use placeholder
        $featuredImage = null;
        if (preg_match('/!\[.*?\]\((.*?)\)/', $content, $matches)) {
            $featuredImage = $matches[1];
        }

        return [
            'title' => $topic,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $featuredImage,
            'read_time' => $readTime,
            'word_count' => $wordCount,
            'status' => 'draft',
            'category' => $this->getRelevantCategory($topic),
            'tags' => $this->generateTags($topic, $content),
        ];
    }

    /**
     * Get relevant category for topic
     */
    private function getRelevantCategory(string $topic): string
    {
        $categoryKeywords = [
            'AI' => ['ai', 'llm', 'gpt', 'langchain', 'agent'],
            'Programming' => ['programming', 'pattern', 'design', 'architecture', 'ddd'],
            'Performance' => ['performance', 'optimization', 'caching', 'database', 'query'],
            'DevOps' => ['devops', 'kubernetes', 'docker', 'infrastructure', 'observability'],
            'Security' => ['security', 'oauth', 'encryption', 'authentication'],
            'Web' => ['web', 'react', 'websocket', 'graphql', 'next.js'],
        ];

        $topicLower = strtolower($topic);
        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($topicLower, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'Technology';
    }

    /**
     * Generate relevant tags
     */
    private function generateTags(string $topic, string $content): array
    {
        $tags = [];

        // Extract key technical terms
        $technicalTerms = [
            'llm', 'ai', 'machine-learning', 'python', 'javascript', 'typescript',
            'react', 'kubernetes', 'docker', 'postgresql', 'redis', 'aws',
            'microservices', 'ddd', 'event-sourcing', 'graphql', 'rest-api',
            'testing', 'performance', 'security', 'devops', 'ci-cd',
        ];

        $contentLower = strtolower($content);
        foreach ($technicalTerms as $term) {
            if (strpos($contentLower, $term) !== false) {
                $tags[] = $term;
            }
        }

        return array_unique(array_slice($tags, 0, 10));
    }

    /**
     * Info log (for CLI output)
     */
    private function info(string $message): void
    {
        Log::info($message);
    }
}
