<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Log;

class ContentQualityService
{
    /**
     * Quality scoring thresholds
     */
    private const MIN_QUALITY_SCORE = 60;  // Increased from 50 for stricter quality
    private const MIN_WORD_COUNT = 2500;   // Increased from 1500 for deeper content (10+ min reads)
    private const MIN_HEADERS = 4;         // Increased from 3 for better structure
    private const MIN_CODE_BLOCKS = 1;     // Increased from 0 - tutorials must have code

    /**
     * Evaluate all posts and unpublish low-quality ones
     */
    public function evaluateAllPosts(bool $dryRun = false): array
    {
        Log::info('Starting content quality evaluation', ['dry_run' => $dryRun]);

        $posts = Post::where('status', 'published')->get();
        $results = [
            'total_evaluated' => 0,
            'high_quality' => 0,
            'poor_quality' => 0,
            'unpublished' => [],
            'kept' => [],
            'scores' => [],
        ];

        foreach ($posts as $post) {
            $score = $this->scorePost($post);
            $results['total_evaluated']++;
            $results['scores'][] = [
                'id' => $post->id,
                'title' => $post->title,
                'score' => $score,
                'status' => $score < self::MIN_QUALITY_SCORE ? 'POOR' : 'GOOD',
            ];

            if ($score < self::MIN_QUALITY_SCORE) {
                $results['poor_quality']++;

                if (!$dryRun) {
                    // Unpublish poor quality post
                    $post->update([
                        'status' => 'draft',
                        'seo_meta' => array_merge(
                            $post->seo_meta ?? [],
                            ['unpublished_reason' => "Low quality score: {$score}/100", 'unpublished_at' => now()]
                        ),
                    ]);

                    $results['unpublished'][] = [
                        'id' => $post->id,
                        'title' => $post->title,
                        'score' => $score,
                    ];

                    Log::warning("Unpublished low-quality post", [
                        'post_id' => $post->id,
                        'title' => $post->title,
                        'score' => $score,
                    ]);
                } else {
                    $results['unpublished'][] = [
                        'id' => $post->id,
                        'title' => $post->title,
                        'score' => $score,
                        'dry_run' => true,
                    ];
                }
            } else {
                $results['high_quality']++;
                $results['kept'][] = [
                    'id' => $post->id,
                    'title' => $post->title,
                    'score' => $score,
                ];
            }
        }

        Log::info('Content quality evaluation complete', [
            'total' => $results['total_evaluated'],
            'high_quality' => $results['high_quality'],
            'poor_quality' => $results['poor_quality'],
            'unpublished' => count($results['unpublished']),
        ]);

        return $results;
    }

    /**
     * Score a single post (0-100)
     */
    public function scorePost(Post $post): int
    {
        $score = 0;

        // 1. Word count (30 points max)
        $wordCount = str_word_count(strip_tags($post->content));
        if ($wordCount >= self::MIN_WORD_COUNT) {
            $score += 30;
        } elseif ($wordCount >= 1000) {
            $score += 20;
        } elseif ($wordCount >= 500) {
            $score += 10;
        }

        // 2. Headers/structure (20 points)
        $headerCount = preg_match_all('/^#+\s/m', $post->content);
        if ($headerCount >= self::MIN_HEADERS) {
            $score += 20;
        } elseif ($headerCount >= 2) {
            $score += 10;
        }

        // 3. Code blocks (15 points)
        $codeBlockCount = preg_match_all('/```/m', $post->content);
        if ($codeBlockCount >= 2) {
            $score += 15;
        } elseif ($codeBlockCount >= 1) {
            $score += 10;
        }

        // 4. Uniqueness/AI-generated signals (15 points)
        if ($this->hasUniqueContent($post->content)) {
            $score += 15;
        } elseif ($this->hasTableOfContents($post->content)) {
            $score += 10; // Enhanced content gets bonus
        }

        // 5. Series/depth (10 points)
        if ($post->series_title) {
            $score += 10;
        }

        // 6. SEO signals (10 points)
        if ($post->category_id && $post->tags->count() > 0) {
            $score += 10;
        }

        // Penalties
        // -10 for suspicious patterns (spam-like)
        if ($this->hasSpamPatterns($post->content)) {
            $score -= 10;
        }

        // -15 for very short content
        if ($wordCount < 500) {
            $score -= 15;
        }

        // Cap score at 0-100
        return max(0, min(100, $score));
    }

    /**
     * Check if content has unique/high-quality signals
     */
    private function hasUniqueContent(string $content): bool
    {
        // Check for diversity of content (not just repeated text)
        $lines = array_filter(explode("\n", $content));
        $uniqueLines = array_unique($lines);

        return (count($uniqueLines) / max(1, count($lines))) > 0.7;
    }

    /**
     * Check if content has table of contents (quality indicator)
     */
    private function hasTableOfContents(string $content): bool
    {
        return str_contains(strtolower($content), 'table of contents') ||
               str_contains(strtolower($content), 'contents') ||
               str_contains(strtolower($content), 'outline');
    }

    /**
     * Check for spam/low-quality patterns
     */
    private function hasSpamPatterns(string $content): bool
    {
        $spamPatterns = [
            '/viagra|casino|poker/i',
            '/click here|buy now|limited time/i',
            '/[a-z0-9]+@[a-z0-9]+\.(com|net)/i', // Email addresses
            '/\{\{.*?\}\}/i', // Template variables
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                Log::warning("Spam pattern detected", [
                    'pattern' => $pattern,
                    'match' => $matches[0] ?? '',
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Get quality report for a post
     */
    public function getQualityReport(Post $post): array
    {
        $wordCount = str_word_count(strip_tags($post->content));
        $headerCount = preg_match_all('/^#+\s/m', $post->content);
        $codeBlockCount = preg_match_all('/```/m', $post->content);

        return [
            'post_id' => $post->id,
            'title' => $post->title,
            'status' => $post->status,
            'word_count' => $wordCount,
            'headers' => $headerCount,
            'code_blocks' => (int)($codeBlockCount / 2), // Each block has 2 backticks
            'has_series' => !is_null($post->series_title),
            'has_tags' => $post->tags->count() > 0,
            'quality_score' => $this->scorePost($post),
            'meets_minimum' => $this->scorePost($post) >= self::MIN_QUALITY_SCORE,
            'recommendation' => $this->scorePost($post) >= self::MIN_QUALITY_SCORE ? 'KEEP' : 'UNPUBLISH',
        ];
    }
}
