<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Content\PublishGate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Publishes drafts on a controlled cadence — 1 regular post every 5 days and
 * 1 tutorial every 7 days — but ONLY drafts that clear strict quality gates.
 *
 * This drains the draft backlog slowly (never a slop-dump) and keeps a steady
 * publishing rhythm feeding SEO, while the gates keep short/truncated AI drafts
 * out of the public corpus (important for Google HCU + AdSense).
 *
 * Scheduled daily; the per-type cadence is self-regulated from published_at, so
 * running late/twice never over-publishes.
 */
class ContentDripPublish extends Command
{
    protected $signature = 'content:drip {--dry-run : Show what would publish without publishing}';
    protected $description = 'Publish quality-gated drafts on cadence (1 post / 5 days, 1 tutorial / 7 days)';

    public function __construct(private readonly PublishGate $gate)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $done = [];

        // Regular post — 1 every 5 days.
        if ($this->isDue(false, PublishGate::POST_INTERVAL_DAYS)) {
            if ($post = $this->pickPublishable(false)) {
                $this->publish($post, $dry);
                $done[] = "post: {$post->title}";
            } else {
                $this->line('No gate-passing regular draft available.');
            }
        }

        // Tutorial — 1 every 7 days.
        if ($this->isDue(true, PublishGate::TUTORIAL_INTERVAL_DAYS)) {
            if ($tut = $this->pickPublishable(true)) {
                $this->publish($tut, $dry);
                $done[] = "tutorial: {$tut->title}";
            } else {
                $this->line('No gate-passing tutorial draft available.');
            }
        }

        if (empty($done)) {
            $this->info($dry ? 'DRY RUN: nothing due/publishable right now.' : 'Nothing published (not due or no qualifying drafts).');
        } else {
            $this->info(($dry ? 'DRY RUN — would publish:' : 'Published:'));
            foreach ($done as $d) {
                $this->line("  - {$d}");
            }
            if (! $dry) {
                Log::info('content:drip published', ['items' => $done]);
            }
        }

        return self::SUCCESS;
    }

    /** Is a fresh item of this type due (no published one within the interval)? */
    private function isDue(bool $tutorial, int $days): bool
    {
        $last = Post::where('status', 'published')
            ->when($tutorial, fn ($q) => $q->whereNotNull('series_title'))
            ->when(! $tutorial, fn ($q) => $q->whereNull('series_title'))
            ->max('published_at');

        return ! $last || Carbon::parse($last)->lt(now()->subDays($days));
    }

    /** Oldest→newest is fine, but prefer the freshest gate-passing draft. */
    private function pickPublishable(bool $tutorial): ?Post
    {
        return Post::where('status', 'draft')
            ->when($tutorial, fn ($q) => $q->whereNotNull('series_title'))
            ->when(! $tutorial, fn ($q) => $q->whereNull('series_title'))
            ->orderByDesc('created_at')
            ->get()
            ->first(fn (Post $p): bool => $this->gate->passes($p));
    }

    private function publish(Post $post, bool $dry): void
    {
        if ($dry) {
            return;
        }
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
