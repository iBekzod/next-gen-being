<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Content\AffiliateLinker;
use Illuminate\Console\Command;

/**
 * Adds contextual /go/<slug> partner links, one CTA box and the disclosure to
 * published posts (see AffiliateLinker for the rules). Idempotent: running it
 * again changes nothing. Scheduled daily in routes/console.php; when a new
 * AFFILIATE_<X>_URL is set in .env the next run links that tool everywhere it
 * is a real subject of a post.
 */
class InsertAffiliateLinksCommand extends Command
{
    protected $signature = 'revenue:insert-affiliate-links
        {--limit=0 : Max posts to change (0 = all)}
        {--post=* : Only these post ids}
        {--max-links=4 : Max distinct /go/ links per post}
        {--include-planned : Also link programs without a referral URL yet (they redirect to the vendor homepage, earn nothing)}
        {--dry-run : Show what would change, save nothing}';

    protected $description = 'Insert tracked affiliate links, a CTA box and the disclosure into published posts';

    /** Programs we may link: referral URL set (earning), or planned when asked. */
    private function programs(bool $includePlanned): array
    {
        return array_filter(
            config('affiliate.links', []),
            fn ($c) => ! empty($c['patterns']) && ($includePlanned || ! empty($c['url']))
        );
    }

    public function handle(AffiliateLinker $linker): int
    {
        $programs = $this->programs((bool) $this->option('include-planned'));
        if ($programs === []) {
            $this->warn('No affiliate programs with a referral URL yet. Set AFFILIATE_*_URL in .env.');

            return self::SUCCESS;
        }

        $dry = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $ids = array_filter(array_map('intval', (array) $this->option('post')));
        $base = rtrim((string) config('app.url'), '/');
        if (str_contains($base, 'localhost')) {
            $base = 'https://nextgenbeing.com';
        }

        $query = Post::where('status', 'published')
            ->where(fn ($q) => $q->whereNull('post_type')->orWhere('post_type', 'article'))
            ->orderBy('id');
        if ($ids) {
            $query->whereIn('id', $ids);
        }

        $changed = 0;
        $linksAdded = 0;
        $query->each(function (Post $post) use ($linker, $programs, $base, $dry, $limit, &$changed, &$linksAdded) {
            if ($limit > 0 && $changed >= $limit) {
                return false;
            }
            $r = $linker->apply((string) $post->title, (string) $post->content, $programs, $base, (int) $this->option('max-links'));
            if (! $r['changed']) {
                return;
            }
            $changed++;
            $linksAdded += count($r['added']);
            $this->line(sprintf(
                '%s #%d %s | links: [%s]%s%s',
                $dry ? 'WOULD UPDATE' : 'UPDATED',
                $post->id,
                $post->title,
                implode(', ', $r['added']),
                $r['cta'] ? ' +CTA' : '',
                $r['disclosure'] ? ' +disclosure' : ''
            ));
            if ($dry) {
                $this->showDiff((string) $post->content, $r['content']);
            } else {
                // Quietly: no post.updated webhook storm, no status change.
                $post->content = $r['content'];
                $post->saveQuietly();
            }
        });

        $this->info(($dry ? '[dry-run] ' : '') . "Posts changed: {$changed}, new links: {$linksAdded}");

        return self::SUCCESS;
    }

    private function showDiff(string $old, string $new): void
    {
        $oldLines = explode("\n", $old);
        foreach (array_diff(explode("\n", $new), $oldLines) as $line) {
            $this->line('    + ' . mb_substr($line, 0, 400));
        }
    }
}
