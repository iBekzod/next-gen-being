<?php

namespace App\Services;

use App\Models\CollectedContent;
use App\Models\TutorialCollection;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentAggregatorService
{
    /**
     * Aggregate tutorials from multiple sources into one collection
     */
    public function aggregateTutorials(
        string $topic,
        int $maxSources = 5,
        string $skillLevel = 'intermediate'
    ): ?TutorialCollection {
        Log::info("Aggregating tutorials for topic: {$topic}");

        // Find tutorial content on this topic
        $tutorials = $this->findRelatedTutorials($topic, $maxSources);

        if ($tutorials->isEmpty()) {
            Log::warning("No tutorials found for topic: {$topic}");
            return null;
        }

        try {
            // Extract steps from each tutorial
            $allSteps = [];
            $codeExamples = [];
            $bestPractices = [];
            $commonPitfalls = [];
            $sourceIds = [];
            $contentIds = [];

            foreach ($tutorials as $tutorial) {
                $sourceIds[] = $tutorial->content_source_id;
                $contentIds[] = $tutorial->id;

                $steps = $this->extractSteps($tutorial->full_content);
                $allSteps = array_merge($allSteps, $steps);

                $code = $this->extractCodeExamples($tutorial->full_content);
                $codeExamples = array_merge($codeExamples, $code);

                $practices = $this->extractBestPractices($tutorial->full_content);
                $bestPractices = array_merge($bestPractices, $practices);

                $pitfalls = $this->extractCommonPitfalls($tutorial->full_content);
                $commonPitfalls = array_merge($commonPitfalls, $pitfalls);
            }

            // Deduplicate and consolidate
            $allSteps = $this->consolidateSteps($allSteps);
            $codeExamples = $this->consolidateCodeExamples($codeExamples);
            $bestPractices = array_unique($bestPractices);
            $commonPitfalls = array_unique($commonPitfalls);

            // Calculate reading time
            $totalContent = implode(' ', array_column($tutorials->toArray(), 'full_content'));
            $readingTime = $this->calculateReadingTime($totalContent);
            $estimatedHours = ceil($readingTime / 60);

            // Create references
            $references = $tutorials->map(function ($tutorial) {
                return [
                    'title' => $tutorial->title,
                    'url' => $tutorial->external_url,
                    'source' => $tutorial->source->name,
                    'author' => $tutorial->author,
                ];
            })->toArray();

            // Compile full content
            $compiledContent = $this->compileContent(
                $topic,
                $allSteps,
                $codeExamples,
                $bestPractices,
                $commonPitfalls
            );

            // Create tutorial collection
            $collection = TutorialCollection::create([
                'title' => $this->generateTitle($topic, $skillLevel),
                'slug' => Str::slug($topic) . '-' . Str::random(6),
                'description' => $this->generateDescription($topic, count($tutorials)),
                'topic' => $topic,
                'source_ids' => $sourceIds,
                'collected_content_ids' => $contentIds,
                'references' => $references,
                'steps' => $allSteps,
                'code_examples' => $codeExamples,
                'best_practices' => $bestPractices,
                'common_pitfalls' => $commonPitfalls,
                'skill_level' => $skillLevel,
                'language' => 'en',
                'estimated_hours' => $estimatedHours,
                'reading_time_minutes' => $readingTime,
                'compiled_content' => $compiledContent,
                'status' => 'draft',
            ]);

            Log::info("Created tutorial collection: {$collection->id} ({$collection->title})");

            return $collection;

        } catch (\Exception $e) {
            Log::error("Failed to aggregate tutorials: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Find related tutorial content
     */
    private function findRelatedTutorials(string $topic, int $limit): \Illuminate\Database\Eloquent\Collection
    {
        return CollectedContent::where('content_type', 'tutorial')
            ->where(function ($query) use ($topic) {
                $words = explode(' ', $topic);
                foreach ($words as $word) {
                    $query->orWhere('title', 'like', "%{$word}%")
                          ->orWhere('excerpt', 'like', "%{$word}%");
                }
            })
            ->where('is_duplicate', false)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Extract tutorial steps from content
     */
    private function extractSteps(string $content): array
    {
        $steps = [];

        // Look for numbered steps
        if (preg_match_all('/(?:^|\n)(\d+)\.\s*(.+?)(?=\n\d+\.|$)/is', $content, $matches)) {
            foreach ($matches[0] as $index => $match) {
                $steps[] = [
                    'number' => (int) $matches[1][$index],
                    'title' => trim($matches[2][$index]),
                    'description' => trim($match),
                ];
            }
        }

        // Look for header-based steps
        if (preg_match_all('/^#{1,3}\s+(.+?)$/m', $content, $matches)) {
            foreach ($matches[1] as $index => $title) {
                if (!$this->isStepAlreadyCaptured($title, $steps)) {
                    $steps[] = [
                        'number' => count($steps) + 1,
                        'title' => trim($title),
                        'description' => $title,
                    ];
                }
            }
        }

        return $steps;
    }

    /**
     * Extract code examples
     */
    private function extractCodeExamples(string $content): array
    {
        $examples = [];

        // Look for code blocks
        if (preg_match_all('/```(.+?)\n([\s\S]*?)```/i', $content, $matches)) {
            foreach ($matches[0] as $index => $block) {
                $language = trim($matches[1][$index]) ?: 'plaintext';
                $code = trim($matches[2][$index]);

                $examples[] = [
                    'language' => $language,
                    'code' => $code,
                    'quality_score' => $this->scoreCodeExample($code),
                ];
            }
        }

        // Sort by quality
        usort($examples, function ($a, $b) {
            return $b['quality_score'] <=> $a['quality_score'];
        });

        return $examples;
    }

    /**
     * Extract best practices
     */
    private function extractBestPractices(string $content): array
    {
        $practices = [];

        // Look for best practice indicators
        $patterns = [
            '/(?:best practice|recommended|should|must|important):\s*(.+?)(?:\n|$)/i',
            '/✓\s*(.+?)(?:\n|$)/',
            '/→\s*(.+?)(?:\n|$)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $practices = array_merge($practices, $matches[1]);
            }
        }

        return array_filter(array_map('trim', $practices));
    }

    /**
     * Extract common pitfalls
     */
    private function extractCommonPitfalls(string $content): array
    {
        $pitfalls = [];

        $patterns = [
            '/(?:avoid|don\'t|common mistake|pitfall):\s*(.+?)(?:\n|$)/i',
            '/✗\s*(.+?)(?:\n|$)/',
            '/⚠\s*(.+?)(?:\n|$)/',
            '/warning:\s*(.+?)(?:\n|$)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $pitfalls = array_merge($pitfalls, $matches[1]);
            }
        }

        return array_filter(array_map('trim', $pitfalls));
    }

    /**
     * Consolidate and deduplicate steps
     */
    private function consolidateSteps(array $steps): array
    {
        $consolidated = [];
        $seen = [];

        foreach ($steps as $step) {
            $key = strtolower($step['title']);

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $consolidated[] = [
                    'step_num' => count($consolidated) + 1,
                    'title' => $step['title'],
                    'description' => $step['description'] ?? '',
                ];
            }
        }

        return $consolidated;
    }

    /**
     * Consolidate code examples
     */
    private function consolidateCodeExamples(array $examples): array
    {
        $consolidated = [];
        $byLanguage = [];

        // Group by language
        foreach ($examples as $example) {
            $lang = $example['language'] ?? 'plaintext';
            if (!isset($byLanguage[$lang])) {
                $byLanguage[$lang] = [];
            }
            $byLanguage[$lang][] = $example;
        }

        // Keep best example per language
        foreach ($byLanguage as $lang => $examplesOfLang) {
            usort($examplesOfLang, function ($a, $b) {
                return ($b['quality_score'] ?? 0) <=> ($a['quality_score'] ?? 0);
            });

            $best = array_shift($examplesOfLang);
            $consolidated[] = [
                'language' => $lang,
                'code' => $best['code'],
            ];
        }

        return $consolidated;
    }

    /**
     * Score code example quality
     */
    private function scoreCodeExample(string $code): float
    {
        $score = 0.5;

        // More code = better
        $lines = count(explode("\n", $code));
        $score += min(0.2, $lines / 100);

        // Has comments
        if (preg_match('/#|\/\/|\/\*/', $code)) {
            $score += 0.1;
        }

        // No error messages
        if (!preg_match('/error|warning|fatal/i', $code)) {
            $score += 0.1;
        }

        // Has meaningful variable names
        if (preg_match('/\b[a-z]{4,}\b/', $code)) {
            $score += 0.1;
        }

        return min(1.0, $score);
    }

    /**
     * Compile complete tutorial content
     */
    private function compileContent(
        string $topic,
        array $steps,
        array $codeExamples,
        array $bestPractices,
        array $pitfalls
    ): string {
        $html = "<div class='tutorial-content'>";

        // Introduction
        $html .= "<section class='tutorial-intro'>";
        $html .= "<h1>Complete Guide to {$topic}</h1>";
        $html .= "<p>This comprehensive guide aggregates the best practices and approaches for {$topic} from multiple authoritative sources.</p>";
        $html .= "</section>";

        // Steps
        if (!empty($steps)) {
            $html .= "<section class='tutorial-steps'>";
            $html .= "<h2>Steps</h2>";
            $html .= "<ol>";
            foreach ($steps as $step) {
                $html .= "<li>";
                $html .= "<h3>" . e($step['title']) . "</h3>";
                $html .= "<p>" . e($step['description']) . "</p>";
                $html .= "</li>";
            }
            $html .= "</ol>";
            $html .= "</section>";
        }

        // Code Examples
        if (!empty($codeExamples)) {
            $html .= "<section class='tutorial-code'>";
            $html .= "<h2>Code Examples</h2>";
            foreach ($codeExamples as $example) {
                $html .= "<div class='code-block'>";
                $html .= "<p class='language-label'>" . ucfirst(e($example['language'])) . "</p>";
                $html .= "<pre><code class='language-" . e($example['language']) . "'>" .
                        e($example['code']) .
                        "</code></pre>";
                $html .= "</div>";
            }
            $html .= "</section>";
        }

        // Best Practices
        if (!empty($bestPractices)) {
            $html .= "<section class='tutorial-practices'>";
            $html .= "<h2>Best Practices</h2>";
            $html .= "<ul>";
            foreach ($bestPractices as $practice) {
                $html .= "<li>" . e($practice) . "</li>";
            }
            $html .= "</ul>";
            $html .= "</section>";
        }

        // Common Pitfalls
        if (!empty($pitfalls)) {
            $html .= "<section class='tutorial-pitfalls'>";
            $html .= "<h2>Common Pitfalls to Avoid</h2>";
            $html .= "<ul class='pitfalls-list'>";
            foreach ($pitfalls as $pitfall) {
                $html .= "<li>" . e($pitfall) . "</li>";
            }
            $html .= "</ul>";
            $html .= "</section>";
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * Generate title for tutorial collection
     */
    private function generateTitle(string $topic, string $skillLevel): string
    {
        $prefix = match ($skillLevel) {
            'beginner' => 'Getting Started with',
            'intermediate' => 'Complete Guide to',
            'advanced' => 'Mastering',
            default => 'Guide to',
        };

        return "{$prefix} {$topic}";
    }

    /**
     * Generate description
     */
    private function generateDescription(string $topic, int $sourceCount): string
    {
        return "A comprehensive guide to {$topic}, compiled from {$sourceCount} authoritative sources. " .
               "Learn step-by-step approaches, see code examples, and discover best practices from industry experts.";
    }

    /**
     * Calculate reading time
     */
    private function calculateReadingTime(string $content): int
    {
        $wordsPerMinute = 200;
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Check if step is already in array
     */
    private function isStepAlreadyCaptured(string $title, array $steps): bool
    {
        foreach ($steps as $step) {
            if (strtolower($step['title']) === strtolower($title)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Publish tutorial collection
     */
    public function publishCollection(TutorialCollection $collection, User $reviewer): void
    {
        $collection->publish($reviewer, 'Auto-published by aggregator service');

        Log::info("Published tutorial collection: {$collection->id}");
    }

    /**
     * Get aggregation statistics
     */
    public function getStatistics(): array
    {
        $collections = TutorialCollection::all();

        return [
            'total_collections' => $collections->count(),
            'published' => $collections->where('status', 'published')->count(),
            'draft' => $collections->where('status', 'draft')->count(),
            'pending_review' => $collections->where('status', 'review')->count(),
            'avg_sources_per_collection' => round(
                $collections->average(function ($c) {
                    return count($c->source_ids ?? []);
                }),
                2
            ),
        ];
    }
}
