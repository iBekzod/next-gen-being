<?php

namespace App\Services;

use App\Models\CollectedContent;
use App\Models\ContentAggregation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentDeduplicationService
{
    /**
     * Minimum term-frequency cosine at which two articles are treated as the
     * same subject.
     *
     * Derived from measurement, not taste — but the two sides of that
     * measurement do NOT rest on equally solid ground, and the difference
     * matters when reading these numbers:
     *
     * NEGATIVE SIDE — calibrated on real production text. Over the 15
     * articles production has actually scraped, all 75 cross-source pairs
     * peaked at 0.2545 (mean 0.0511) and none reached 0.35. 0.35 therefore
     * sits about 0.095 above every real negative. Same-source pairs went
     * higher (30 pairs, peak 0.3920 between two unrelated CSS-Tricks
     * articles that share the site's boilerplate), which is why
     * findAllDuplicates() excludes same-source pairs outright rather than
     * raising this number.
     *
     * POSITIVE SIDE — fixtures only, not real data. Three hand-written pairs
     * of independent write-ups of one subject measured 0.5417, 0.5234 and
     * 0.5159 (see tests/Feature/Content/DeduplicationIntegrationTest.php,
     * which asserts the band). No real corroborating pair exists to measure
     * yet: only 3 of 10 sources currently return articles and they cover
     * different beats, so production has never produced two publications
     * writing up the same subject. The positive band is a designed estimate
     * and should be re-measured the moment real corroboration appears.
     *
     * 0.35 is deliberately placed above the midpoint of the two bands: a
     * false cluster invents a "corroborated" topic out of unrelated
     * articles, which is worse than missing one.
     *
     * The old value of 0.75 was unreachable for anything but near-identical
     * text, and byte-identical syndication never reaches this code because
     * the `external_url` unique constraint rejects it at insert.
     */
    private const MIN_SIMILARITY_THRESHOLD = 0.35;

    private const HIGH_CONFIDENCE_THRESHOLD = 0.85; // 85% = definitely same/very similar

    /**
     * How much of `full_content` feeds the comparison.
     *
     * The excerpt alone is 84-228 chars on real rows while bodies run
     * 4,558-9,707, which left roughly a dozen non-stopword terms per document
     * to compare. But reading the whole body is worse than reading none of it:
     * scraped `full_content` carries the publication's standing furniture
     * (newsletter pitch, related-links block, licensing notice), which is
     * byte-identical across every article that publication runs. Measured on
     * production-length documents, the separation between the positive and
     * negative controls peaks around 2,000-2,500 chars and then inverts —
     * unbounded, two unrelated articles from the SAME source scored 0.74
     * while the genuine cross-source pair scored 0.43.
     *
     * 2,000 chars keeps the subject-dense opening of a typical article,
     * stays inside the prose even for the shortest production body observed,
     * and costs about a third of the unbounded vector build in the O(n^2)
     * pairwise loop.
     */
    private const BODY_CHARS_FOR_COMPARISON = 2000;

    /**
     * Find all duplicate/similar content from the last N hours
     */
    public function findAllDuplicates(int $sinceHours = 24): int
    {
        Log::info("Starting deduplication process (last {$sinceHours} hours)");

        $unprocessed = CollectedContent::unprocessed()
            ->where('created_at', '>=', now()->subHours($sinceHours))
            ->notDuplicate()
            ->get();

        if ($unprocessed->isEmpty()) {
            Log::info("No unprocessed content to deduplicate");
            return 0;
        }

        $aggregationsCreated = 0;

        // Compare each content against all others
        foreach ($unprocessed as $content) {
            if ($content->is_duplicate) {
                continue; // Skip if already marked as duplicate
            }

            $similarContents = [];

            foreach ($unprocessed as $potentialDuplicate) {
                if ($content->id === $potentialDuplicate->id || $potentialDuplicate->is_duplicate) {
                    continue;
                }

                // Never merge two articles from the same source.
                //
                // Corroboration is the whole point of clustering, and
                // TopicQueueService requires two DISTINCT sources before a
                // cluster becomes a topic — so a same-source merge has no
                // upside whatsoever. It does have a downside: merging sets
                // is_duplicate = true on the absorbed row, and the query above
                // filters notDuplicate(), so a wrong same-source merge removes
                // that article from every future comparison and makes it
                // inherit the wrong cluster's primary title. It can therefore
                // hide a genuine cross-source pairing that would have formed
                // later.
                //
                // This is also where the metric's only real-data failure lives.
                // Over the 15 articles production actually scraped: 75
                // cross-source pairs peaked at 0.2545 (none over the 0.35
                // threshold), while 30 same-source pairs peaked at 0.3920 — a
                // single crossing, between two unrelated CSS-Tricks articles
                // that share that site's boilerplate. Excluding same-source
                // pairs removes that false positive without touching the
                // threshold.
                if ($content->content_source_id === $potentialDuplicate->content_source_id) {
                    continue;
                }

                $similarity = $this->calculateSimilarity($content, $potentialDuplicate);

                if ($similarity >= self::MIN_SIMILARITY_THRESHOLD) {
                    $similarContents[] = [
                        'content' => $potentialDuplicate,
                        'similarity' => $similarity,
                    ];
                }
            }

            // If we found similar content, create an aggregation
            if (!empty($similarContents)) {
                $aggregationsCreated += $this->createAggregation($content, $similarContents);
            }
        }

        Log::info("Deduplication complete. Created {$aggregationsCreated} aggregations");

        return $aggregationsCreated;
    }

    /**
     * Calculate similarity between two content pieces.
     *
     * This is a term-frequency cosine over title + excerpt + the opening of
     * the body, with stopwords removed. It is NOT TF-IDF: this method is a
     * pairwise API called from a loop, so there is no corpus in scope, and
     * computing document frequency over just the two documents being compared
     * would give every shared word log(2/2) = 0 and erase exactly the signal
     * we are looking for.
     */
    public function calculateSimilarity(CollectedContent $content1, CollectedContent $content2): float
    {
        // Exact URL match = duplicate
        if ($content1->external_url === $content2->external_url) {
            return 1.0;
        }

        $similarity = $this->cosineSimilarity(
            $this->getTermFrequencyVector($this->comparisonText($content1)),
            $this->getTermFrequencyVector($this->comparisonText($content2))
        );

        return min(1.0, max(0.0, $similarity));
    }

    /**
     * Build the normalized text that represents one article for comparison.
     *
     * Title and excerpt on their own are too thin to compare (see
     * BODY_CHARS_FOR_COMPARISON), so the opening of the body is appended.
     */
    private function comparisonText(CollectedContent $content): string
    {
        return $this->normalizeText(
            $content->title . ' '
            . $content->excerpt . ' '
            . $this->truncateOnWordBoundary((string) $content->full_content, self::BODY_CHARS_FOR_COMPARISON)
        );
    }

    /**
     * Cut text to at most $limit characters without splitting the final word.
     */
    private function truncateOnWordBoundary(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($cut, ' ');

        return $lastSpace === false ? $cut : mb_substr($cut, 0, $lastSpace);
    }

    /**
     * Create an aggregation from similar content
     */
    public function createAggregation(CollectedContent $primaryContent, array $similarContents): int
    {
        try {
            // Determine the topic from primary content
            $topic = $this->extractTopic($primaryContent->title);

            // Collect all content IDs and source IDs
            $contentIds = [$primaryContent->id];
            $sourceIds = [$primaryContent->content_source_id];

            $maxSimilarity = 0;

            // Sort by similarity (descending) to include most relevant articles first
            usort($similarContents, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            // Include top similar items (max 5 to get rich content without noise)
            $maxItems = min(5, count($similarContents));
            $selectedItems = array_slice($similarContents, 0, $maxItems);

            foreach ($selectedItems as $item) {
                $contentIds[] = $item['content']->id;
                if (!in_array($item['content']->content_source_id, $sourceIds)) {
                    $sourceIds[] = $item['content']->content_source_id;
                }
                $maxSimilarity = max($maxSimilarity, $item['similarity']);

                // Mark as duplicate
                $item['content']->markAsDuplicate($primaryContent->id);
            }

            // Mark remaining non-selected similar items as duplicates too
            $remainingItems = array_slice($similarContents, $maxItems);
            foreach ($remainingItems as $item) {
                $item['content']->markAsDuplicate($primaryContent->id);
            }

            // Create aggregation
            $aggregation = ContentAggregation::create([
                'topic' => $topic,
                'description' => "Aggregation of {$primaryContent->title} from " . count(array_unique($sourceIds)) . " sources",
                'source_ids' => $sourceIds,
                'collected_content_ids' => $contentIds,
                'primary_source_id' => $primaryContent->content_source_id,
                'confidence_score' => $this->calculateConfidenceScore(
                    count($similarContents),
                    $maxSimilarity
                ),
            ]);

            Log::info("Created aggregation: {$aggregation->topic} ({$aggregation->id}) with " .
                     count($contentIds) . " content items from " . count(array_unique($sourceIds)) . " sources");

            return 1;

        } catch (\Exception $e) {
            Log::error("Failed to create aggregation: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Calculate confidence score for aggregation
     */
    private function calculateConfidenceScore(int $similarContentCount, float $maxSimilarity): float
    {
        // More similar items = higher confidence
        // Higher individual similarity = higher confidence
        $countWeight = min(1.0, $similarContentCount / 5); // 5 similar items = max confidence from count
        $similarityWeight = $maxSimilarity; // 0-1

        $confidence = ($countWeight * 0.3) + ($similarityWeight * 0.7);

        return min(1.0, max(0.0, $confidence));
    }

    /**
     * Extract topic from title
     */
    private function extractTopic(string $title): string
    {
        // Remove common prefixes
        $title = preg_replace('/^(Breaking:|News:|NEW:|How to:|A guide to:|Top \d+:)/i', '', $title);
        $title = trim($title);

        // Remove common suffixes
        $title = preg_replace('/\s*\|.*$/', '', $title);
        $title = preg_replace('/\s*-\s*\w+\s*$/', '', $title);

        return Str::limit($title, 500);
    }

    /**
     * Normalize text for comparison
     */
    private function normalizeText(string $text): string
    {
        // Convert to lowercase
        $text = strtolower($text);

        // Remove HTML tags
        $text = strip_tags($text);

        // Remove special characters, keep only letters, numbers, spaces
        $text = preg_replace('/[^a-z0-9\s]/i', ' ', $text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Get the term-frequency vector for a normalized text.
     *
     * Deliberately plain TF with stopword filtering — no IDF. The previous
     * version multiplied TF by log(1000 / (array_search($word, $words) + 1)),
     * i.e. by the word's first POSITION IN THE ARRAY rather than by any
     * document frequency, so the same word carried a different weight
     * depending on where in the article it happened to appear first.
     */
    private function getTermFrequencyVector(string $text): array
    {
        $words = array_filter(explode(' ', $text));
        $vector = [];

        $totalWords = count($words);
        if ($totalWords === 0) {
            return [];
        }

        foreach (array_count_values($words) as $word => $count) {
            // Skip very common words
            if ($this->isStopWord((string) $word)) {
                continue;
            }

            $vector[$word] = $count / $totalWords;
        }

        return $vector;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosineSimilarity(array $vector1, array $vector2): float
    {
        if (empty($vector1) || empty($vector2)) {
            return 0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        // Calculate dot product and magnitudes
        $allKeys = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));

        foreach ($allKeys as $key) {
            $val1 = $vector1[$key] ?? 0;
            $val2 = $vector2[$key] ?? 0;

            $dotProduct += $val1 * $val2;
            $magnitude1 += pow($val1, 2);
            $magnitude2 += pow($val2, 2);
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 === 0 || $magnitude2 === 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Check if word is a stop word (common words to ignore)
     */
    private function isStopWord(string $word): bool
    {
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'be', 'are', 'were',
            'been', 'that', 'this', 'these', 'those', 'i', 'you', 'he', 'she',
            'it', 'we', 'they', 'what', 'which', 'who', 'when', 'where', 'why',
            'how', 'can', 'could', 'would', 'should', 'will', 'do', 'does', 'did',
            'have', 'has', 'had', 'not', 'no', 'yes', 'all', 'each', 'every',
            'my', 'his', 'her', 'its', 'our', 'their', 'about', 'your', 'there',
            'also', 'just', 'very', 'more', 'most', 'many', 'much', 'some', 'any',
        ];

        return in_array($word, $stopWords);
    }

    /**
     * Get all aggregations with their statistics
     */
    public function getAggregationStats()
    {
        $aggregations = ContentAggregation::all();

        return [
            'total_aggregations' => $aggregations->count(),
            'high_confidence' => $aggregations->where('confidence_score', '>=', self::HIGH_CONFIDENCE_THRESHOLD)->count(),
            'medium_confidence' => $aggregations->whereBetween('confidence_score', [self::MIN_SIMILARITY_THRESHOLD, self::HIGH_CONFIDENCE_THRESHOLD])->count(),
            'total_content_items' => CollectedContent::count(),
            'total_duplicates' => CollectedContent::where('is_duplicate', true)->count(),
            'avg_confidence' => round($aggregations->avg('confidence_score'), 3),
            'avg_sources_per_aggregation' => round($aggregations->average(function ($agg) {
                return count($agg->source_ids ?? []);
            }), 2),
        ];
    }

    /**
     * Find similar aggregations and merge them
     */
    public function mergeRelatedAggregations(): int
    {
        $aggregations = ContentAggregation::all();
        $merged = 0;

        for ($i = 0; $i < count($aggregations); $i++) {
            for ($j = $i + 1; $j < count($aggregations); $j++) {
                $agg1 = $aggregations[$i];
                $agg2 = $aggregations[$j];

                if ($this->shouldMergeAggregations($agg1, $agg2)) {
                    $this->mergeAggregations($agg1, $agg2);
                    $merged++;
                }
            }
        }

        return $merged;
    }

    /**
     * Check if two aggregations should be merged
     */
    private function shouldMergeAggregations(ContentAggregation $agg1, ContentAggregation $agg2): bool
    {
        // Similar topics
        $topicSimilarity = similar_text($agg1->topic, $agg2->topic) / max(strlen($agg1->topic), strlen($agg2->topic));

        if ($topicSimilarity >= 0.8) {
            return true;
        }

        // Significant overlap in source content
        $sourceIds1 = $agg1->source_ids ?? [];
        $sourceIds2 = $agg2->source_ids ?? [];

        if (count(array_intersect($sourceIds1, $sourceIds2)) >= 2) {
            return true;
        }

        return false;
    }

    /**
     * Merge two aggregations
     */
    private function mergeAggregations(ContentAggregation $primary, ContentAggregation $secondary): void
    {
        $sourceIds = array_unique(array_merge(
            $primary->source_ids ?? [],
            $secondary->source_ids ?? []
        ));

        $contentIds = array_unique(array_merge(
            $primary->collected_content_ids ?? [],
            $secondary->collected_content_ids ?? []
        ));

        $primary->update([
            'source_ids' => $sourceIds,
            'collected_content_ids' => $contentIds,
        ]);

        $secondary->delete();

        Log::info("Merged aggregation {$secondary->id} into {$primary->id}");
    }
}
