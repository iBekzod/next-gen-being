<?php

namespace App\Services;

use App\Models\ContentAggregation;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParaphrasingService
{
    private const CLAUDE_MODEL = 'claude-3-5-sonnet-20241022';
    private const API_TIMEOUT = 120;
    private const MAX_RETRIES = 3;
    private const MIN_CONFIDENCE_SCORE = 0.75;

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
     * Paraphrase an aggregation into a curated post
     */
    public function paraphraseAggregation(
        ContentAggregation $aggregation,
        string $language = 'en',
        ?User $author = null
    ): ?Post {
        Log::info("Starting paraphrasing for aggregation: {$aggregation->topic}");

        try {
            // Get the author (system user for curated content)
            if (!$author) {
                $author = $this->getOrCreateCurationAuthor();
            }

            // Extract source information
            $sources = $aggregation->collectedContent()
                ->get()
                ->map(function ($content) {
                    return [
                        'title' => $content->title,
                        'source' => $content->source->name,
                        'url' => $content->external_url,
                        'excerpt' => $content->excerpt,
                        'content' => $this->limitContent($content->full_content, 500),
                    ];
                })
                ->toArray();

            // Generate paraphrased content
            $result = $this->paraphraseWithRetry($aggregation->topic, $sources, $language);

            if (!$result) {
                Log::error("Failed to paraphrase aggregation: {$aggregation->id}");
                return null;
            }

            // Validate fact preservation
            $validation = $this->validateFactPreservation($sources, $result['content']);
            if ($validation['confidence_score'] < self::MIN_CONFIDENCE_SCORE) {
                Log::warning("Low confidence score ({$validation['confidence_score']}) for aggregation: {$aggregation->id}");
            }

            // Create the post
            $post = $this->createCuratedPost(
                title: $result['title'] ?? $aggregation->topic,
                excerpt: $result['excerpt'] ?? $this->generateExcerpt($result['content']),
                content: $result['content'],
                aggregation: $aggregation,
                sources: $sources,
                author: $author,
                language: $language,
                confidenceScore: $validation['confidence_score'],
                verificationNotes: $validation['notes']
            );

            Log::info("Successfully paraphrased aggregation into post: {$post->id}");

            return $post;

        } catch (\Exception $e) {
            Log::error("Paraphrasing failed for aggregation {$aggregation->id}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Paraphrase with retry logic
     */
    private function paraphraseWithRetry(
        string $topic,
        array $sources,
        string $language,
        int $attempt = 1
    ): ?array {
        try {
            $sourcesText = $this->formatSourcesForPrompt($sources);
            $languageInstruction = $language === 'en' ? '' : "\n\nWrite in $language language.";

            $prompt = "You are a tech blogger who curates and elaborates on technology news and trends.

I have collected these sources about the topic: {$topic}

SOURCES:
{$sourcesText}

Your task:
1. Create a comprehensive, well-written article that paraphrases and elaborates on these sources
2. Preserve ALL factual information from the sources
3. Add explanation of why readers should care about this topic
4. Explain technical concepts in clear language
5. Provide context and implications
6. Do NOT add information not present in the sources
7. Cite sources naturally within the text using [Source Name] references
8. Make it engaging and valuable to readers{$languageInstruction}

Format your response as JSON:
{
  \"title\": \"A compelling headline\",
  \"excerpt\": \"One paragraph summary (max 150 words)\",
  \"content\": \"Full article in HTML or markdown\"
}

Ensure the content is at least 1000 words and covers all key points from the sources.";

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
                    Log::warning("Attempt {$attempt} failed for paraphrasing, retrying...");
                    sleep(2 ** $attempt); // Exponential backoff
                    return $this->paraphraseWithRetry($topic, $sources, $language, $attempt + 1);
                }
                return null;
            }

            // Extract the response
            $content = $response->json('content.0.text');
            if (!$content) {
                return null;
            }

            // Parse JSON response
            try {
                $parsed = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Try to extract JSON from response
                    if (preg_match('/\{[^{}]*"title"[^{}]*\}/s', $content, $matches)) {
                        $parsed = json_decode($matches[0], true);
                    }
                }

                if (!isset($parsed['content'])) {
                    return null;
                }

                return $parsed;

            } catch (\Exception $e) {
                Log::error("Failed to parse Claude response: {$e->getMessage()}");
                return null;
            }

        } catch (\Exception $e) {
            if ($attempt < self::MAX_RETRIES) {
                Log::warning("Attempt {$attempt} failed, retrying: {$e->getMessage()}");
                sleep(2 ** $attempt);
                return $this->paraphraseWithRetry($topic, $sources, $language, $attempt + 1);
            }
            throw $e;
        }
    }

    /**
     * Validate that facts from sources are preserved in paraphrased content
     */
    public function validateFactPreservation(array $sources, string $paraphrasedContent): array
    {
        $validation = [
            'confidence_score' => 1.0,
            'missing_facts' => [],
            'notes' => '',
        ];

        try {
            // Extract key facts from sources
            $keyFacts = $this->extractKeyFacts($sources);

            if (empty($keyFacts)) {
                return $validation;
            }

            // Check how many facts appear in paraphrased content
            $foundFacts = 0;
            $missingFacts = [];

            foreach ($keyFacts as $fact) {
                if ($this->factExists($fact, $paraphrasedContent)) {
                    $foundFacts++;
                } else {
                    $missingFacts[] = $fact;
                }
            }

            // Calculate confidence score
            $preservationRate = $foundFacts / count($keyFacts);
            $validation['confidence_score'] = $preservationRate;
            $validation['missing_facts'] = $missingFacts;

            if ($preservationRate >= 0.95) {
                $validation['notes'] = "Excellent fact preservation. All major points included.";
            } elseif ($preservationRate >= 0.85) {
                $validation['notes'] = "Good fact preservation. Minor details may have been omitted.";
            } elseif ($preservationRate >= 0.75) {
                $validation['notes'] = "Adequate fact preservation. Some key facts may be missing.";
            } else {
                $validation['notes'] = "Poor fact preservation. Consider rewriting.";
            }

            return $validation;

        } catch (\Exception $e) {
            Log::error("Fact validation failed: {$e->getMessage()}");
            return $validation;
        }
    }

    /**
     * Extract key facts from sources
     */
    private function extractKeyFacts(array $sources): array
    {
        $facts = [];

        foreach ($sources as $source) {
            // Extract sentences (simplified)
            $content = $source['excerpt'] . ' ' . $source['content'];
            $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);

            // Take first 3 sentences as key facts
            foreach (array_slice($sentences, 0, 3) as $sentence) {
                $sentence = trim($sentence);
                if (strlen($sentence) > 20) {
                    $facts[] = $sentence;
                }
            }
        }

        return array_unique($facts);
    }

    /**
     * Check if a fact exists in content
     */
    private function factExists(string $fact, string $content): bool
    {
        // Extract key terms from fact
        $words = array_filter(explode(' ', strtolower($fact)), function ($word) {
            return strlen($word) > 3; // Only significant words
        });

        if (empty($words)) {
            return true;
        }

        // Check if at least 60% of words appear in content
        $matchedWords = 0;
        $contentLower = strtolower($content);

        foreach ($words as $word) {
            if (strpos($contentLower, $word) !== false) {
                $matchedWords++;
            }
        }

        $matchRate = $matchedWords / count($words);
        return $matchRate >= 0.6;
    }

    /**
     * Create a curated post from paraphrased content
     */
    private function createCuratedPost(
        string $title,
        string $excerpt,
        string $content,
        ContentAggregation $aggregation,
        array $sources,
        User $author,
        string $language,
        float $confidenceScore,
        ?string $verificationNotes = null
    ): Post {
        $category = $this->getOrCreateCategory();

        $post = Post::create([
            'title' => $title,
            'excerpt' => $excerpt,
            'content' => $content,
            'status' => 'draft',
            'is_curated' => true,
            'content_source_type' => 'aggregated',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'content_aggregation_id' => $aggregation->id,
            'source_ids' => $aggregation->source_ids,
            'references' => $this->extractReferences($sources),
            'base_language' => $language,
            'paraphrase_confidence_score' => $confidenceScore,
            'verification_notes' => $verificationNotes,
            'allow_comments' => true,
            'is_premium' => false,
        ]);

        // Tag with topic
        $this->tagPost($post, $aggregation->topic);

        // Mark aggregation as processed
        $aggregation->update(['processed_at' => now()]);

        return $post;
    }

    /**
     * Extract references from sources
     */
    private function extractReferences(array $sources): array
    {
        return array_map(function ($source) {
            return [
                'title' => $source['title'],
                'url' => $source['url'],
                'source' => $source['source'],
            ];
        }, $sources);
    }

    /**
     * Format sources for the prompt
     */
    private function formatSourcesForPrompt(array $sources): string
    {
        $formatted = [];

        foreach ($sources as $index => $source) {
            $formatted[] = "Source " . ($index + 1) . ": {$source['source']}\n" .
                          "Title: {$source['title']}\n" .
                          "URL: {$source['url']}\n" .
                          "Content:\n{$source['content']}\n";
        }

        return implode("\n---\n", $formatted);
    }

    /**
     * Limit content to word count
     */
    private function limitContent(string $content, int $maxWords): string
    {
        $words = str_word_count($content, 1);
        if (count($words) <= $maxWords) {
            return $content;
        }

        return implode(' ', array_slice($words, 0, $maxWords)) . '...';
    }

    /**
     * Generate excerpt from content
     */
    private function generateExcerpt(string $content, int $length = 150): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        $excerpt = substr($text, 0, $length);
        $lastSpace = strrpos($excerpt, ' ');

        if ($lastSpace > 0) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }

        return $excerpt . '...';
    }

    /**
     * Get or create curation author
     */
    private function getOrCreateCurationAuthor(): User
    {
        $author = User::firstOrCreate(
            ['email' => 'curator@system.local'],
            [
                'name' => 'Content Curator',
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        return $author;
    }

    /**
     * Get or create content category
     */
    private function getOrCreateCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'curated-technology'],
            [
                'name' => 'Curated Technology',
                'description' => 'Hand-curated technology news and insights',
            ]
        );
    }

    /**
     * Tag post with topic
     */
    private function tagPost(Post $post, string $topic): void
    {
        try {
            // Extract main keywords from topic
            $words = explode(' ', strtolower($topic));
            $tags = array_filter($words, function ($word) {
                return strlen($word) > 3; // Only significant words
            });

            foreach (array_slice($tags, 0, 5) as $tag) {
                $tagModel = \App\Models\Tag::firstOrCreate(
                    ['slug' => Str::slug($tag)],
                    ['name' => ucfirst($tag)]
                );

                $post->tags()->attach($tagModel);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to tag post: {$e->getMessage()}");
        }
    }

    /**
     * Elaborate on content (enhance readability)
     */
    public function elaborateContent(string $content, string $language = 'en'): string
    {
        // Add section breaks where appropriate
        $content = $this->addSectionBreaks($content);

        // Enhance formatting
        $content = $this->enhanceFormatting($content);

        return $content;
    }

    /**
     * Add section breaks for readability
     */
    private function addSectionBreaks(string $content): string
    {
        // Add breaks after certain keywords
        $breakKeywords = [
            'however,' => '</p><p><strong>However,</strong>',
            'moreover,' => '</p><p><strong>Moreover,</strong>',
            'furthermore,' => '</p><p><strong>Furthermore,</strong>',
            'in summary,' => '</p><p><strong>In summary,</strong>',
            'as a result,' => '</p><p><strong>As a result,</strong>',
        ];

        foreach ($breakKeywords as $keyword => $replacement) {
            $content = str_ireplace($keyword, $replacement, $content);
        }

        return $content;
    }

    /**
     * Enhance content formatting
     */
    private function enhanceFormatting(string $content): string
    {
        // Wrap paragraphs in <p> tags if not already
        if (!preg_match('/<p[^>]*>/', $content)) {
            $paragraphs = explode("\n\n", $content);
            $content = '<p>' . implode('</p><p>', $paragraphs) . '</p>';
        }

        return $content;
    }

    /**
     * Get paraphrasing statistics
     */
    public function getStatistics(): array
    {
        $curatedPosts = Post::where('is_curated', true)->get();

        return [
            'total_curated_posts' => $curatedPosts->count(),
            'avg_confidence_score' => round($curatedPosts->avg('paraphrase_confidence_score'), 3),
            'high_confidence' => $curatedPosts->where('paraphrase_confidence_score', '>=', 0.9)->count(),
            'fact_verified' => $curatedPosts->where('is_fact_verified', true)->count(),
            'by_language' => $curatedPosts->groupBy('base_language')->map->count(),
        ];
    }
}
