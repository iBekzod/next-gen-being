<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AuditContent extends Command
{
    protected $signature = 'content:audit {--show-samples=10 : how many sample titles to print per category}';

    protected $description = 'Audit published posts for AI-template patterns and duplicate titles. Read-only.';

    /**
     * Title-pattern regexes that strongly correlate with the templated
     * AI-generated tutorials flagged in our last AdSense review.
     *
     * Each entry: [regex, human label]. A post matching ANY pattern is
     * counted as "ai-template". Patterns derived from auditing the sitemap.
     */
    protected array $aiTitlePatterns = [
        ['/\bmastering\s+.+:\s+a\s+deep\s+dive\b/i',                'Mastering X: A Deep Dive'],
        ['/\bbuilding\s+a?\s*production[- ]ready\b/i',              'Building a Production-Ready X'],
        ['/\bdeep\s+dive\b/i',                                      'Deep Dive (any)'],
        ['/\bcomplete\s+solution\b/i',                              'Complete Solution: X'],
        ['/\badvanced\s+tutorial\b/i',                              'Advanced Tutorial: X'],
        ['/\bwhat\s+we\s+learned\s+scaling\b/i',                    'What We Learned Scaling to NMk'],
        ['/\bwhat\s+\d+\s+years?\s+of\s+production\s+(taught|learning)\b/i', 'What N Years of Production Taught Me'],
        ['/\bhow\s+we\s+scaled\s+to\s+\d+[mk]\b/i',                 'How We Scaled to NMk'],
        ['/\bproduction\s+(journey|deep\s+dive)\b/i',               'Production Journey / Deep Dive'],
        ['/\bcomprehensive\s+(guide|comparison|tutorial)\b/i',      'Comprehensive Guide/Comparison'],
        ['#\d+m\s*requests?[/-]?day\b#i',                           'NM requests/day'],
        ['#\d+m\s*queries?[/-]?day\b#i',                            'NM queries/day'],
        ['/\d+k\s*monthly\s+readers\b/i',                           'Nk monthly readers'],
        ['/\bcut\s+(our\s+)?[\w ]*\s+by\s+\d+%/i',                  'Cut X by N%'],
        ['/\b\d+\s+essential\s+\w+/i',                              '10 Essential X'],
    ];

    public function handle(): int
    {
        $sampleSize = (int) $this->option('show-samples');

        $posts = Post::query()
            ->where('status', 'published')
            ->select('id', 'title', 'slug', 'published_at', 'noindex')
            ->orderBy('published_at')
            ->get();

        $this->info("Auditing {$posts->count()} published posts");
        $this->newLine();

        $this->auditAiTemplates($posts, $sampleSize);
        $this->newLine();
        $this->auditDuplicates($posts, $sampleSize);

        return self::SUCCESS;
    }

    protected function auditAiTemplates(Collection $posts, int $sampleSize): void
    {
        $matches = collect();
        $hitsByPattern = [];

        foreach ($posts as $post) {
            $hit = null;
            foreach ($this->aiTitlePatterns as [$regex, $label]) {
                if (preg_match($regex, $post->title)) {
                    $hit = $label;
                    break;
                }
            }
            if ($hit !== null) {
                $hitsByPattern[$hit][] = $post->title;
                $matches->push($post);
            }
        }

        $this->line('<fg=yellow>=== AI-template title patterns ===</>');
        $this->line(sprintf('Matched: %d / %d (%.1f%%)', $matches->count(), $posts->count(),
            $posts->isNotEmpty() ? $matches->count() / $posts->count() * 100 : 0));

        $rows = [];
        foreach ($hitsByPattern as $label => $titles) {
            $rows[] = [$label, count($titles)];
        }
        usort($rows, fn ($a, $b) => $b[1] <=> $a[1]);
        $this->table(['Pattern', 'Hits'], $rows);

        $this->line('<fg=yellow>Sample matched titles:</>');
        foreach ($matches->take($sampleSize) as $post) {
            $tag = $post->noindex ? ' [noindex]' : '';
            $this->line("  • {$post->title}{$tag}");
        }
    }

    protected function auditDuplicates(Collection $posts, int $sampleSize): void
    {
        // Series parts (e.g. "Building a REST API with Laravel - Part 1/2/3")
        // are NEVER duplicates of each other. Exclude any post with a
        // series_title from the dedup pool.
        $candidates = $posts->reject(fn (Post $p) => !empty($p->series_title))->values();
        $excluded = $posts->count() - $candidates->count();

        // Canonicalize titles (strip template noise + stopwords) and then do
        // pairwise similar_text comparison. Cluster anything with >= 80%
        // similarity (conservative — 70% had too many false positives from
        // posts that just shared an AI-template prefix).
        $rows = $candidates->map(fn (Post $p) => [
            'post' => $p,
            'canon' => $this->canonicalize($p->title),
        ])->values()->all();

        $n = count($rows);
        $clusters = [];
        $assigned = array_fill(0, $n, -1);
        for ($i = 0; $i < $n; $i++) {
            if ($assigned[$i] !== -1) continue;
            $clusters[] = [$i];
            $cidx = count($clusters) - 1;
            $assigned[$i] = $cidx;
            for ($j = $i + 1; $j < $n; $j++) {
                if ($assigned[$j] !== -1) continue;
                similar_text($rows[$i]['canon'], $rows[$j]['canon'], $pct);
                if ($pct >= 80.0) {
                    $clusters[$cidx][] = $j;
                    $assigned[$j] = $cidx;
                }
            }
        }
        $dupClusters = array_values(array_filter($clusters, fn ($c) => count($c) > 1));

        $this->line('<fg=yellow>=== Duplicate clusters (title similarity >= 80%, series excluded) ===</>');
        $this->line(sprintf('Excluded %d series-part posts from dedup pool', $excluded));
        $this->line(sprintf('Found %d clusters covering %d posts', count($dupClusters),
            array_sum(array_map('count', $dupClusters))));

        foreach (array_slice($dupClusters, 0, $sampleSize) as $idx => $cluster) {
            $this->line("");
            $this->line("  cluster #{$idx} (" . count($cluster) . " posts):");
            foreach ($cluster as $i) {
                /** @var Post $post */
                $post = $rows[$i]['post'];
                $tag = $post->noindex ? ' [noindex]' : '';
                $this->line("    [{$post->id}]  {$post->title}{$tag}");
            }
        }
    }

    /**
     * Normalize a title for similarity comparison: lowercase, drop punctuation,
     * stopwords, and AI-template noise so the residual is the actual topic.
     */
    protected function canonicalize(string $title): string
    {
        $s = strtolower($title);
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s);
        $s = preg_replace(
            '/\b(a|an|the|and|or|of|to|in|for|with|from|on|by|using|deep|dive|mastering|production|ready|complete|solution|advanced|tutorial|comprehensive|guide|comparison|building|scaling|journey|essential|essentials|years|learning|learned|what|how|we|i|part|\d+)\b/',
            '', $s
        );
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }
}
