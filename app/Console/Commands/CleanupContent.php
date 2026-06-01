<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CleanupContent extends Command
{
    protected $signature = 'content:cleanup
        {--noindex-ai-templates : flag AI-template posts noindex (reversible)}
        {--delete-duplicates : delete the older post in each duplicate cluster (keeps newest)}
        {--apply : actually apply changes; without this flag everything is a dry-run}
        {--limit=0 : only act on the first N matches (0 = unlimited)}';

    protected $description = 'Cleanup AI-generated content: noindex templated posts and/or delete duplicates. Dry-run by default — pass --apply to actually write changes.';

    protected array $aiTitlePatterns = [
        '/\bmastering\s+.+:\s+a\s+deep\s+dive\b/i',
        '/\bbuilding\s+a?\s*production[- ]ready\b/i',
        '/\bdeep\s+dive\b/i',
        '/\bcomplete\s+solution\b/i',
        '/\badvanced\s+tutorial\b/i',
        '/\bwhat\s+we\s+learned\s+scaling\b/i',
        '/\bwhat\s+\d+\s+years?\s+of\s+production\s+(taught|learning)\b/i',
        '/\bhow\s+we\s+scaled\s+to\s+\d+[mk]\b/i',
        '/\bproduction\s+(journey|deep\s+dive)\b/i',
        '/\bcomprehensive\s+(guide|comparison|tutorial)\b/i',
        '#\d+m\s*requests?[/-]?day\b#i',
        '#\d+m\s*queries?[/-]?day\b#i',
        '/\d+k\s*monthly\s+readers\b/i',
        '/\bcut\s+(our\s+)?[\w ]*\s+by\s+\d+%/i',
        '/\b\d+\s+essential\s+\w+/i',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');
        $modeLabel = $apply ? '<fg=red>APPLY MODE</>' : '<fg=green>DRY RUN</>';
        $this->line("Running cleanup in {$modeLabel}");
        $this->newLine();

        if (!$this->option('noindex-ai-templates') && !$this->option('delete-duplicates')) {
            $this->error('Pick at least one action: --noindex-ai-templates and/or --delete-duplicates');
            return self::INVALID;
        }

        $posts = Post::query()
            ->where('status', 'published')
            ->select('id', 'title', 'slug', 'published_at', 'noindex', 'noindex_reason')
            ->orderBy('published_at')
            ->get();

        if ($this->option('noindex-ai-templates')) {
            $this->applyNoindex($posts, $apply, $limit);
            $this->newLine();
        }
        if ($this->option('delete-duplicates')) {
            $this->deleteDuplicates($posts, $apply, $limit);
            $this->newLine();
        }

        if ($apply) {
            Cache::forget('seo:sitemap.xml');
            $this->info('Sitemap cache cleared.');
        } else {
            $this->warn('Dry run — no changes written. Re-run with --apply to commit.');
        }

        return self::SUCCESS;
    }

    protected function applyNoindex(Collection $posts, bool $apply, int $limit): void
    {
        $matches = $posts->filter(function (Post $post) {
            if ($post->noindex) return false; // already done
            foreach ($this->aiTitlePatterns as $regex) {
                if (preg_match($regex, $post->title)) {
                    return true;
                }
            }
            return false;
        })->values();

        if ($limit > 0) {
            $matches = $matches->take($limit);
        }

        $this->line("<fg=yellow>=== Noindex AI-template posts ===</>");
        $this->line("Would mark {$matches->count()} posts as noindex");
        foreach ($matches->take(20) as $post) {
            $this->line("  • [{$post->id}] {$post->title}");
        }
        if ($matches->count() > 20) {
            $this->line('  …and '.($matches->count() - 20).' more');
        }

        if ($apply && $matches->isNotEmpty()) {
            Post::whereIn('id', $matches->pluck('id'))->update([
                'noindex' => true,
                'noindex_reason' => 'ai-template',
            ]);
            $this->info("Marked {$matches->count()} posts noindex.");
        }
    }

    protected function deleteDuplicates(Collection $posts, bool $apply, int $limit): void
    {
        // Series parts are NEVER dupes; exclude them from the pool.
        $candidates = $posts->reject(fn (Post $p) => !empty($p->series_title))->values();
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

        // Keep the NEWEST in each cluster, delete the rest.
        $toDelete = collect();
        foreach ($dupClusters as $cluster) {
            $members = array_map(fn ($i) => $rows[$i]['post'], $cluster);
            usort($members, fn ($a, $b) => optional($b->published_at)->getTimestamp() <=> optional($a->published_at)->getTimestamp());
            foreach (array_slice($members, 1) as $post) {
                $toDelete->push($post);
            }
        }

        if ($limit > 0) {
            $toDelete = $toDelete->take($limit);
        }

        $this->line("<fg=yellow>=== Delete older duplicates ===</>");
        $this->line(sprintf('Found %d clusters → would delete %d older posts (keeping newest of each)',
            count($dupClusters), $toDelete->count()));
        foreach ($toDelete->take(20) as $post) {
            $this->line("  • DELETE [{$post->id}] {$post->title}");
        }
        if ($toDelete->count() > 20) {
            $this->line('  …and '.($toDelete->count() - 20).' more');
        }

        if ($apply && $toDelete->isNotEmpty()) {
            $ids = $toDelete->pluck('id');
            // Soft delete if model uses SoftDeletes; else hard delete.
            $deleted = Post::whereIn('id', $ids)->delete();
            $this->info("Deleted {$deleted} duplicate posts.");
        }
    }

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
