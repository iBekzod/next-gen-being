<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InternalLinkPostsCommand extends Command
{
    protected $signature = 'posts:internal-link
        {--dry-run : Show what would change without saving}
        {--id= : Process only this post ID}
        {--max-links=4 : Max new internal links to add per post}';

    protected $description = 'Auto-link mentions of other posts inside published post content for SEO topic-clusters.';

    public function handle(): int
    {
        $maxLinksPerPost = (int) $this->option('max-links');
        $dryRun = $this->option('dry-run');

        // Build a map of distinct "linkable phrases" → slug.
        // We use the cleaned title minus the part-N suffix to avoid weird matches.
        $linkable = Post::where('status', 'published')
            ->whereNotNull('slug')
            ->select('id', 'title', 'slug', 'series_title')
            ->get()
            ->mapWithKeys(function ($p) {
                $phrase = $p->series_title
                    ? trim(preg_replace('/\s*-\s*(Complete\s+\d+-Part.*|Part\s+\d+:.*)$/i', '', $p->title))
                    : $p->title;
                // Strip trailing colon-fluff like "X: a deep dive" so we match the core noun phrase
                $phrase = trim(preg_replace('/:\s+(a\s+)?(deep\s+dive|comparative|production|complete).*$/i', '', $phrase));
                return strlen($phrase) >= 15
                    ? [$phrase => ['slug' => $p->slug, 'id' => $p->id]]
                    : [];
            })
            ->filter();

        // Sort by length desc so longer phrases match first
        $linkable = $linkable->sortByDesc(fn($v, $k) => mb_strlen($k));

        $this->info("Linkable phrases: " . $linkable->count());

        $query = Post::where('status', 'published')->whereNotNull('content');
        if ($id = $this->option('id')) $query->where('id', $id);

        $totalLinks = 0;
        $postsTouched = 0;

        $query->chunk(25, function ($posts) use ($linkable, $maxLinksPerPost, $dryRun, &$totalLinks, &$postsTouched) {
            foreach ($posts as $post) {
                $content = $post->content;
                $original = $content;

                // Split content into code-block vs prose segments to avoid linking inside code
                $parts = preg_split('/(```[\s\S]*?```|`[^`\n]+`)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

                $linksAddedHere = 0;
                $usedPhrases = [];

                foreach ($parts as $i => $part) {
                    if ($i % 2 === 1) continue; // odd indices are code, skip
                    foreach ($linkable as $phrase => $info) {
                        if ($linksAddedHere >= $maxLinksPerPost) break 2;
                        if ($info['id'] === $post->id) continue; // don't self-link
                        if (isset($usedPhrases[$phrase])) continue;

                        // Skip if phrase already appears inside an existing markdown link
                        $existingLinkRegex = '/\[[^\]]*' . preg_quote($phrase, '/') . '[^\]]*\]\([^)]+\)/i';
                        if (preg_match($existingLinkRegex, $part)) {
                            $usedPhrases[$phrase] = true; // already linked, mark used
                            continue;
                        }

                        // Match whole phrase, case-insensitive, with word boundaries
                        $pattern = '/(?<![\w\/])(' . preg_quote($phrase, '/') . ')(?![\w\/])/i';
                        $url = '/posts/' . $info['slug'];
                        $replacement = '[$1](' . $url . ')';

                        $newPart = preg_replace($pattern, $replacement, $part, 1, $count);
                        if ($count > 0) {
                            $parts[$i] = $newPart;
                            $part = $newPart;
                            $usedPhrases[$phrase] = true;
                            $linksAddedHere++;
                        }
                    }
                }

                if ($linksAddedHere > 0) {
                    $newContent = implode('', $parts);
                    if (!$dryRun) {
                        $post->content = $newContent;
                        $post->saveQuietly(); // skip touching read_time recalc cascade etc
                    }
                    $totalLinks += $linksAddedHere;
                    $postsTouched++;
                    $this->line("  [#{$post->id}] +{$linksAddedHere}: " . Str::limit($post->title, 60));
                }
            }
        });

        $this->info('');
        $this->info($dryRun ? '=== DRY RUN ===' : '=== DONE ===');
        $this->info("Posts touched: {$postsTouched}");
        $this->info("Total links added: {$totalLinks}");

        return self::SUCCESS;
    }
}
