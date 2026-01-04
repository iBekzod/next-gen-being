<?php

namespace App\Services;

use App\Models\Post;
use App\Models\ContentAggregation;
use App\Models\SourceReference;
use App\Models\CollectedContent;
use Illuminate\Support\Facades\Log;

class ReferenceTrackingService
{
    /**
     * Extract and track references from an aggregation
     */
    public function extractReferencesFromAggregation(ContentAggregation $aggregation, Post $post): int
    {
        Log::info("Extracting references for aggregation {$aggregation->id} into post {$post->id}");

        $referenceCount = 0;
        $position = 1;

        // Get all content in the aggregation
        $contents = CollectedContent::whereIn('id', $aggregation->collected_content_ids ?? [])
            ->with('source')
            ->get();

        foreach ($contents as $content) {
            try {
                $reference = SourceReference::create([
                    'post_id' => $post->id,
                    'collected_content_id' => $content->id,
                    'title' => $content->title,
                    'url' => $content->external_url,
                    'author' => $content->author,
                    'published_at' => $content->published_at,
                    'accessed_at' => now(),
                    'domain' => $content->getDomain(),
                    'citation_style' => 'inline',
                    'position_in_post' => $position++,
                ]);

                $referenceCount++;

            } catch (\Exception $e) {
                Log::error("Failed to create reference for content {$content->id}: {$e->getMessage()}");
            }
        }

        Log::info("Created {$referenceCount} references for post {$post->id}");

        return $referenceCount;
    }

    /**
     * Add a reference to a post
     */
    public function addReference(
        Post $post,
        string $title,
        string $url,
        ?string $author = null,
        ?\DateTime $publishedAt = null,
        string $citationStyle = 'inline'
    ): SourceReference {
        $domain = $this->extractDomain($url);

        return SourceReference::create([
            'post_id' => $post->id,
            'title' => $title,
            'url' => $url,
            'author' => $author,
            'published_at' => $publishedAt,
            'accessed_at' => now(),
            'domain' => $domain,
            'citation_style' => $citationStyle,
            'position_in_post' => $this->getNextPosition($post),
        ]);
    }

    /**
     * Format all references for a post
     */
    public function formatReferences(Post $post, string $style = 'inline'): string
    {
        $references = $post->sourceReferences()
            ->ordered()
            ->get();

        if ($references->isEmpty()) {
            return '';
        }

        $formatted = [];

        foreach ($references as $reference) {
            $formatted[] = $reference->formatCitation();
        }

        return $this->buildReferenceList($formatted, $style);
    }

    /**
     * Build reference list
     */
    private function buildReferenceList(array $citations, string $style): string
    {
        if (empty($citations)) {
            return '';
        }

        $html = '<div class="post-references"><h3>Sources</h3>';

        if ($style === 'inline') {
            $html .= '<ol class="references-list">';
            foreach ($citations as $citation) {
                $html .= '<li>' . $citation . '</li>';
            }
            $html .= '</ol>';
        } else {
            $html .= '<ul class="references-list">';
            foreach ($citations as $citation) {
                $html .= '<li>' . $citation . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate inline citations in content
     */
    public function addInlineCitations(Post $post, string $content): string
    {
        $references = $post->sourceReferences()
            ->ordered()
            ->get();

        if ($references->isEmpty()) {
            return $content;
        }

        // This is a simple implementation - in production, you'd want more sophisticated placement
        // For now, we'll add reference markers at natural breaking points

        $referenceMarkers = [];
        foreach ($references as $index => $reference) {
            $referenceMarkers[$index + 1] = '<sup><a href="#ref' . ($index + 1) . '">[' . ($index + 1) . ']</a></sup>';
        }

        // Add reference list at end
        $content .= '<hr class="references-divider">';
        $content .= '<div id="references" class="post-references">';
        $content .= '<h3>Sources</h3>';
        $content .= '<ol class="references-list">';

        foreach ($references as $index => $reference) {
            $content .= '<li id="ref' . ($index + 1) . '">' . $reference->formatCitation() . '</li>';
        }

        $content .= '</ol>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Create footnotes for references
     */
    public function createFootnotes(Post $post): string
    {
        $references = $post->sourceReferences()
            ->ordered()
            ->get();

        if ($references->isEmpty()) {
            return '';
        }

        $html = '<footer class="post-footnotes">';
        $html .= '<ol>';

        foreach ($references as $index => $reference) {
            $html .= '<li id="fn' . ($index + 1) . '">';
            $html .= $reference->formatCitation();
            $html .= ' <a href="#fnref' . ($index + 1) . '">↩</a>';
            $html .= '</li>';
        }

        $html .= '</ol>';
        $html .= '</footer>';

        return $html;
    }

    /**
     * Get reference as HTML link
     */
    public function getReferenceAsLink(SourceReference $reference): string
    {
        return '<a href="' . e($reference->url) . '" target="_blank" rel="noopener noreferrer">' .
               e($reference->title) . '</a>' .
               ' <span class="reference-source">(' . e($reference->getSourceName()) . ')</span>';
    }

    /**
     * Get all unique domains referenced in a post
     */
    public function getUniqueDomains(Post $post): array
    {
        return $post->sourceReferences()
            ->distinct()
            ->pluck('domain')
            ->toArray();
    }

    /**
     * Count references by domain
     */
    public function getReferenceCountByDomain(Post $post): array
    {
        return $post->sourceReferences()
            ->groupBy('domain')
            ->selectRaw('domain, count(*) as count')
            ->pluck('count', 'domain')
            ->toArray();
    }

    /**
     * Get most cited sources
     */
    public function getMostCitedSources(int $limit = 10): array
    {
        return SourceReference::groupBy('domain')
            ->selectRaw('domain, count(*) as citation_count')
            ->orderByDesc('citation_count')
            ->limit($limit)
            ->pluck('citation_count', 'domain')
            ->toArray();
    }

    /**
     * Update reference access time
     */
    public function recordAccess(SourceReference $reference): void
    {
        $reference->update(['accessed_at' => now()]);
    }

    /**
     * Get recently accessed references
     */
    public function getRecentlyAccessedReferences(int $days = 7): array
    {
        return SourceReference::where('accessed_at', '>=', now()->subDays($days))
            ->orderByDesc('accessed_at')
            ->get()
            ->toArray();
    }

    /**
     * Check if URL is already referenced in a post
     */
    public function isAlreadyReferenced(Post $post, string $url): bool
    {
        return $post->sourceReferences()
            ->where('url', $url)
            ->exists();
    }

    /**
     * Get references by source
     */
    public function getReferencesBySource(Post $post, string $sourceName): array
    {
        return $post->sourceReferences()
            ->whereHas('collectedContent.source', function ($query) use ($sourceName) {
                $query->where('name', $sourceName);
            })
            ->get()
            ->toArray();
    }

    /**
     * Export references as bibliography
     */
    public function exportAsBibliography(Post $post, string $format = 'html'): string
    {
        $references = $post->sourceReferences()
            ->ordered()
            ->get();

        if ($references->isEmpty()) {
            return '';
        }

        return match ($format) {
            'html' => $this->bibliographyAsHtml($references),
            'markdown' => $this->bibliographyAsMarkdown($references),
            'plaintext' => $this->bibliographyAsPlaintext($references),
            'bibtex' => $this->bibliographyAsBibtex($references),
            default => $this->bibliographyAsHtml($references),
        };
    }

    private function bibliographyAsHtml($references): string
    {
        $html = '<div class="bibliography"><h3>Bibliography</h3><ol>';

        foreach ($references as $ref) {
            $html .= '<li>' . $ref->formatCitation() . '</li>';
        }

        $html .= '</ol></div>';

        return $html;
    }

    private function bibliographyAsMarkdown($references): string
    {
        $md = "## Bibliography\n\n";

        foreach ($references as $index => $ref) {
            $md .= ($index + 1) . ". " . $ref->formatCitation() . "\n";
        }

        return $md;
    }

    private function bibliographyAsPlaintext($references): string
    {
        $text = "Bibliography\n\n";

        foreach ($references as $index => $ref) {
            $text .= ($index + 1) . ". " . $ref->formatCitation() . "\n";
        }

        return $text;
    }

    private function bibliographyAsBibtex($references): string
    {
        $bibtex = "";

        foreach ($references as $index => $ref) {
            $bibtex .= "@article{ref" . $index . ",\n";
            $bibtex .= "  title={" . $ref->title . "},\n";
            $bibtex .= "  author={" . ($ref->author ?? 'Unknown') . "},\n";
            $bibtex .= "  url={" . $ref->url . "},\n";

            if ($ref->published_at) {
                $bibtex .= "  year={" . $ref->published_at->year . "},\n";
            }

            $bibtex .= "}\n\n";
        }

        return $bibtex;
    }

    /**
     * Get reference statistics
     */
    public function getStatistics(): array
    {
        $allReferences = SourceReference::all();

        return [
            'total_references' => $allReferences->count(),
            'unique_sources' => $allReferences->distinct('domain')->count(),
            'avg_references_per_post' => round($allReferences->count() / Post::where('is_curated', true)->count(), 2),
            'most_cited_domain' => $allReferences->groupBy('domain')->map->count()->sort()->last(),
            'recently_accessed' => $allReferences->where('accessed_at', '>=', now()->subDays(7))->count(),
        ];
    }

    /**
     * Extract domain from URL
     */
    private function extractDomain(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? 'unknown';
    }

    /**
     * Get next position for reference
     */
    private function getNextPosition(Post $post): int
    {
        $last = $post->sourceReferences()
            ->orderByDesc('position_in_post')
            ->first();

        return ($last?->position_in_post ?? 0) + 1;
    }

    /**
     * Validate references
     */
    public function validateReferences(Post $post): array
    {
        $references = $post->sourceReferences()->get();

        $validation = [
            'total' => $references->count(),
            'valid' => 0,
            'invalid_urls' => [],
            'issues' => [],
        ];

        foreach ($references as $reference) {
            if (filter_var($reference->url, FILTER_VALIDATE_URL)) {
                $validation['valid']++;
            } else {
                $validation['invalid_urls'][] = $reference->url;
            }
        }

        if (empty($references->pluck('author')->filter())) {
            $validation['issues'][] = 'Some references missing author information';
        }

        return $validation;
    }
}
