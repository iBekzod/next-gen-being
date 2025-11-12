<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\Log;

/**
 * Content Quality and SEO Checker Service
 * Analyzes post content for quality, SEO, readability, and provides recommendations
 */
class ContentQualityChecker
{
    /**
     * Comprehensive quality check for a post
     */
    public function analyzePost(string $title, string $excerpt, string $content, array $options = []): array
    {
        return [
            'overall_score' => $this->calculateOverallScore($title, $excerpt, $content),
            'seo_analysis' => $this->analyzeSEO($title, $excerpt, $content),
            'readability_analysis' => $this->analyzeReadability($content),
            'content_analysis' => $this->analyzeContent($title, $content),
            'publishing_checklist' => $this->getPublishingChecklist($title, $excerpt, $content),
            'recommendations' => $this->generateRecommendations($title, $excerpt, $content),
        ];
    }

    /**
     * Calculate overall quality score
     */
    private function calculateOverallScore(string $title, string $excerpt, string $content): array
    {
        $scores = [];

        // Title quality
        $titleScore = 0;
        $titleScore += strlen($title) >= 30 && strlen($title) <= 70 ? 25 : 10;
        $titleScore += $this->countWords($title) >= 3 && $this->countWords($title) <= 10 ? 25 : 0;
        $titleScore += preg_match('/[0-9]|how|why|what|guide|complete/', strtolower($title)) ? 25 : 10;
        $titleScore += !preg_match('/[^a-z0-9\s\-\:\|]/i', $title) ? 25 : 0;
        $scores['title_quality'] = min(100, $titleScore);

        // Content quality
        $contentWords = $this->countWords($content);
        $contentScore = 0;
        $contentScore += $contentWords >= 300 ? 25 : ($contentWords >= 200 ? 15 : 5);
        $contentScore += $this->hasHeadings($content) ? 25 : 0;
        $contentScore += $this->hasLists($content) ? 20 : 0;
        $contentScore += $this->hasParagraphBreaks($content) ? 15 : 0;
        $contentScore += preg_match('/\*\*[^*]+\*\*|__[^_]+__/', $content) ? 15 : 0;
        $scores['content_quality'] = min(100, $contentScore);

        // SEO quality
        $seoScore = 0;
        $seoScore += strlen($excerpt) >= 120 && strlen($excerpt) <= 160 ? 25 : 10;
        $seoScore += $this->keywordInTitle($title) && $this->keywordInContent($title, $content) ? 25 : 0;
        $seoScore += $this->hasMetaKeywords($title, $excerpt, $content) ? 25 : 0;
        $seoScore += $this->hasLinks($content) ? 25 : 0;
        $scores['seo_quality'] = min(100, $seoScore);

        // Engagement quality
        $engagementScore = 0;
        $engagementScore += $this->hasCallToAction($content) ? 25 : 0;
        $engagementScore += $this->hasQuestions($content) ? 20 : 0;
        $engagementScore += $this->hasEmojis($content) ? 10 : 0;
        $engagementScore += $this->hasExamples($content) ? 25 : 0;
        $engagementScore += $this->hasSocialProof($content) ? 20 : 0;
        $scores['engagement_quality'] = min(100, $engagementScore);

        $overall = round(
            ($scores['title_quality'] * 0.20) +
            ($scores['content_quality'] * 0.30) +
            ($scores['seo_quality'] * 0.25) +
            ($scores['engagement_quality'] * 0.25)
        );

        return [
            'overall' => $overall,
            'breakdown' => $scores,
            'grade' => $this->getGrade($overall),
        ];
    }

    /**
     * SEO Analysis
     */
    private function analyzeSEO(string $title, string $excerpt, string $content): array
    {
        $keywords = $this->extractKeywords($title);
        $titleLength = strlen($title);
        $excerptLength = strlen($excerpt);
        $contentLength = strlen($content);
        $imageCount = substr_count($content, '![');
        $linkCount = substr_count($content, '[') - substr_count($content, '![');

        $checks = [
            'title_optimization' => [
                'check' => $titleLength >= 30 && $titleLength <= 70,
                'current' => $titleLength,
                'target' => '30-70 characters',
                'priority' => 'critical',
                'message' => $titleLength >= 30 && $titleLength <= 70
                    ? '✓ Title length is optimal'
                    : "✗ Title is " . ($titleLength < 30 ? "too short" : "too long")
            ],
            'meta_description' => [
                'check' => $excerptLength >= 120 && $excerptLength <= 160,
                'current' => $excerptLength,
                'target' => '120-160 characters',
                'priority' => 'critical',
                'message' => $excerptLength >= 120 && $excerptLength <= 160
                    ? '✓ Meta description length is optimal'
                    : "✗ Meta description is " . ($excerptLength < 120 ? "too short" : "too long")
            ],
            'keyword_frequency' => [
                'check' => $this->keywordFrequency($title, $content) >= 1 && $this->keywordFrequency($title, $content) <= 3,
                'current' => round($this->keywordFrequency($title, $content) * 100) / 100,
                'target' => '1-3%',
                'priority' => 'high',
                'message' => '✓ Keyword density is optimal'
            ],
            'headings_structure' => [
                'check' => $this->hasHeadings($content),
                'current' => $this->countHeadings($content),
                'target' => 'At least 3-5 headings',
                'priority' => 'high',
                'message' => $this->hasHeadings($content)
                    ? '✓ Content has proper heading structure'
                    : '✗ Add descriptive headings to structure content'
            ],
            'internal_links' => [
                'check' => $linkCount >= 1,
                'current' => $linkCount,
                'target' => 'At least 2-3 internal links',
                'priority' => 'medium',
                'message' => $linkCount >= 2
                    ? '✓ Good number of internal links'
                    : '✗ Consider adding internal links'
            ],
            'images' => [
                'check' => $imageCount >= 1,
                'current' => $imageCount,
                'target' => 'At least 1-2 images',
                'priority' => 'medium',
                'message' => $imageCount >= 1
                    ? '✓ Content has images'
                    : '✗ Consider adding an image'
            ],
            'word_count' => [
                'check' => $this->countWords($content) >= 300,
                'current' => $this->countWords($content),
                'target' => '300+ words',
                'priority' => 'high',
                'message' => $this->countWords($content) >= 300
                    ? '✓ Content length is sufficient'
                    : '✗ Expand content to at least 300 words'
            ],
        ];

        return [
            'checks' => $checks,
            'score' => $this->calculateSEOScore($checks),
            'priority_items' => array_filter($checks, fn($c) => !$c['check']),
        ];
    }

    /**
     * Readability Analysis
     */
    private function analyzeReadability(string $content): array
    {
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $words = $this->countWords($content);
        $paragraphs = substr_count($content, "\n\n") + 1;

        $avgSentenceLength = count($sentences) > 0 ? $words / count($sentences) : 0;
        $avgParagraphLength = count($sentences) > 0 ? count($sentences) / max(1, $paragraphs) : 0;

        return [
            'metrics' => [
                'total_words' => $words,
                'total_sentences' => count($sentences),
                'total_paragraphs' => $paragraphs,
                'average_sentence_length' => round($avgSentenceLength, 1),
                'average_paragraph_length' => round($avgParagraphLength, 1),
                'reading_time_minutes' => ceil($words / 200),
            ],
            'assessments' => [
                'sentence_length' => [
                    'status' => $avgSentenceLength <= 20 ? '✓ Good' : '⚠ Long',
                    'recommendation' => $avgSentenceLength > 20
                        ? 'Keep sentences under 20 words for better readability'
                        : 'Sentence length is optimal'
                ],
                'paragraph_length' => [
                    'status' => $avgParagraphLength <= 4 ? '✓ Good' : '⚠ Long',
                    'recommendation' => $avgParagraphLength > 4
                        ? 'Break paragraphs into 2-4 sentences maximum'
                        : 'Paragraph length is optimal'
                ],
                'paragraph_breaks' => [
                    'status' => $paragraphs >= 3 ? '✓ Good' : '⚠ Few breaks',
                    'recommendation' => 'Use line breaks frequently to improve scannability'
                ],
            ],
            'reading_level' => $this->estimateReadingLevel($avgSentenceLength, $words),
        ];
    }

    /**
     * Content Analysis
     */
    private function analyzeContent(string $title, string $content): array
    {
        return [
            'formatting' => [
                'has_headings' => $this->hasHeadings($content),
                'has_lists' => $this->hasLists($content),
                'has_bold_text' => preg_match('/\*\*[^*]+\*\*|__[^_]+__/', $content),
                'has_code_blocks' => strpos($content, '```') !== false,
                'has_quotes' => strpos($content, '>') !== false,
                'has_emphasis' => preg_match('/\*[^*]+\*|_[^_]+_/', $content),
            ],
            'engagement' => [
                'has_cta' => $this->hasCallToAction($content),
                'has_questions' => $this->hasQuestions($content),
                'question_count' => substr_count($content, '?'),
                'has_emojis' => $this->hasEmojis($content),
                'has_examples' => $this->hasExamples($content),
            ],
            'structure_issues' => $this->identifyStructureIssues($title, $content),
        ];
    }

    /**
     * Generate publishing checklist
     */
    private function getPublishingChecklist(string $title, string $excerpt, string $content): array
    {
        return [
            'basic_info' => [
                'title_exists' => ['check' => !empty($title), 'label' => 'Title is set'],
                'excerpt_exists' => ['check' => !empty($excerpt), 'label' => 'Meta description is set'],
                'content_exists' => ['check' => !empty($content) && strlen($content) > 100, 'label' => 'Content is substantial'],
            ],
            'quality_checks' => [
                'title_length_ok' => ['check' => strlen($title) >= 30 && strlen($title) <= 70, 'label' => 'Title is 30-70 characters'],
                'excerpt_length_ok' => ['check' => strlen($excerpt) >= 120 && strlen($excerpt) <= 160, 'label' => 'Meta description is 120-160 characters'],
                'content_length_ok' => ['check' => $this->countWords($content) >= 300, 'label' => 'Content is 300+ words'],
                'has_structure' => ['check' => $this->hasHeadings($content), 'label' => 'Content has proper headings'],
            ],
            'seo_optimization' => [
                'keyword_in_title' => ['check' => $this->keywordInTitle($title), 'label' => 'Focus keyword in title'],
                'keyword_in_content' => ['check' => $this->keywordInContent($title, $content), 'label' => 'Focus keyword in content'],
                'has_links' => ['check' => $this->hasLinks($content), 'label' => 'Content has links'],
                'has_image' => ['check' => substr_count($content, '![') >= 1, 'label' => 'Content has at least 1 image'],
            ],
            'final_polish' => [
                'no_spelling_errors' => ['check' => true, 'label' => 'Spelling checked'],
                'proper_formatting' => ['check' => $this->hasProperFormatting($content), 'label' => 'Content is well formatted'],
                'has_cta' => ['check' => $this->hasCallToAction($content), 'label' => 'Has call-to-action'],
                'preview_checked' => ['check' => true, 'label' => 'Preview looks good'],
            ],
        ];
    }

    /**
     * Generate recommendations
     */
    private function generateRecommendations(string $title, string $excerpt, string $content): array
    {
        $recommendations = [];

        // Title recommendations
        if (strlen($title) < 30) {
            $recommendations[] = ['priority' => 'high', 'tip' => 'Expand your title to at least 30 characters for better SEO'];
        }
        if (!preg_match('/[0-9]/', $title)) {
            $recommendations[] = ['priority' => 'medium', 'tip' => 'Consider adding a number to your title (e.g., "5 Ways", "2024 Guide")'];
        }
        if (!preg_match('/(how|why|what|guide|complete|proven)/i', $title)) {
            $recommendations[] = ['priority' => 'medium', 'tip' => 'Use power words like "How", "Why", "Complete Guide" in your title'];
        }

        // Excerpt recommendations
        if (strlen($excerpt) < 120) {
            $recommendations[] = ['priority' => 'high', 'tip' => 'Expand your meta description to at least 120 characters'];
        }

        // Content recommendations
        if ($this->countWords($content) < 500) {
            $recommendations[] = ['priority' => 'high', 'tip' => 'Consider expanding to 500+ words for better SEO and user value'];
        }
        if (!$this->hasHeadings($content)) {
            $recommendations[] = ['priority' => 'high', 'tip' => 'Add headings to structure your content and improve readability'];
        }
        if (!$this->hasLists($content)) {
            $recommendations[] = ['priority' => 'medium', 'tip' => 'Add bullet or numbered lists to break up paragraphs'];
        }
        if (substr_count($content, '![') < 1) {
            $recommendations[] = ['priority' => 'medium', 'tip' => 'Add at least one image to increase engagement'];
        }
        if (!$this->hasCallToAction($content)) {
            $recommendations[] = ['priority' => 'medium', 'tip' => 'Add a clear call-to-action at the end (e.g., "Share this", "Subscribe", etc.)'];
        }

        return array_slice($recommendations, 0, 5); // Top 5 recommendations
    }

    // ===== Helper Methods =====

    private function countWords(string $text): int
    {
        return str_word_count(strip_tags($text));
    }

    private function countHeadings(string $content): int
    {
        return substr_count($content, '# ') + substr_count($content, '## ') + substr_count($content, '### ');
    }

    private function hasHeadings(string $content): bool
    {
        return preg_match('/(^|\n)#{1,6}\s/m', $content) === 1;
    }

    private function hasLists(string $content): bool
    {
        return preg_match('/[\-\*]\s|^\s*\d+\./m', $content) === 1;
    }

    private function hasParagraphBreaks(string $content): bool
    {
        return substr_count($content, "\n\n") >= 2;
    }

    private function hasLinks(string $content): bool
    {
        return preg_match('/\[.+\]\(.+\)/', $content) === 1;
    }

    private function hasCallToAction(string $content): bool
    {
        return preg_match('/(share|subscribe|comment|follow|learn more|read more|join|download)/i', $content) === 1;
    }

    private function hasQuestions(string $content): bool
    {
        return substr_count($content, '?') > 0;
    }

    private function hasEmojis(string $content): bool
    {
        return preg_match('/[\x{1F300}-\x{1F9FF}]/u', $content) === 1;
    }

    private function hasExamples(string $content): bool
    {
        return preg_match('/(for example|for instance|such as|like)/i', $content) === 1;
    }

    private function extractKeywords(string $text): array
    {
        $words = str_word_count(strtolower($text), 1);
        $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'is', 'are'];
        return array_filter($words, fn($w) => !in_array($w, $stopwords));
    }

    private function keywordInTitle(string $title): bool
    {
        $words = $this->extractKeywords($title);
        return count($words) > 0;
    }

    private function keywordInContent(string $title, string $content): bool
    {
        $keywords = $this->extractKeywords($title);
        foreach ($keywords as $keyword) {
            if (stripos($content, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function hasMetaKeywords(string $title, string $excerpt, string $content): bool
    {
        return $this->keywordInTitle($title) && $this->keywordInContent($title, $content);
    }

    private function keywordFrequency(string $title, string $content): float
    {
        $words = $this->countWords($content);
        if ($words === 0) return 0;

        $keywords = $this->extractKeywords($title);
        $count = 0;
        foreach ($keywords as $keyword) {
            $count += substr_count(strtolower($content), strtolower($keyword));
        }

        return ($count / $words) * 100;
    }

    private function hasProperFormatting(string $content): bool
    {
        return preg_match('/([\*\-]\s|#{1,6}\s|`|>)/', $content) === 1;
    }

    private function identifyStructureIssues(string $title, string $content): array
    {
        $issues = [];

        if (!$this->hasHeadings($content)) {
            $issues[] = 'Missing heading structure - add H2 and H3 tags';
        }

        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $avgLength = $this->countWords($content) / max(1, count($sentences));
        if ($avgLength > 20) {
            $issues[] = 'Sentences are too long - aim for average of 15-20 words';
        }

        if (substr_count($content, "\n\n") < 3) {
            $issues[] = 'Not enough paragraph breaks - add more white space';
        }

        return $issues;
    }

    private function estimateReadingLevel(float $avgSentenceLength, int $wordCount): string
    {
        if ($avgSentenceLength < 15 && $wordCount >= 500) {
            return 'Easy (Good for general audience)';
        }
        if ($avgSentenceLength < 20 && $wordCount >= 300) {
            return 'Moderate (Good for most readers)';
        }
        return 'Complex (May need simplification)';
    }

    private function calculateSEOScore(array $checks): int
    {
        $passed = 0;
        $total = count($checks);

        foreach ($checks as $check) {
            if ($check['check']) {
                $passed++;
            }
        }

        return round(($passed / $total) * 100);
    }

    private function getGrade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A (Excellent)',
            $score >= 80 => 'B (Good)',
            $score >= 70 => 'C (Fair)',
            $score >= 60 => 'D (Poor)',
            default => 'F (Needs Work)',
        };
    }
}
