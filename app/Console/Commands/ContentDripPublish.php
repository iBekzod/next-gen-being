<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Content\PublishGate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Publishes long-form drafts at a steady rhythm: at most DRIP_DAILY_LIMIT per
 * calendar day, at least DRIP_MIN_GAP_HOURS apart, OLDEST gate-passing draft
 * first. Posts and tutorials share one queue.
 *
 * Only drafts that clear PublishGate are eligible (editor review, length,
 * moderation, fabrication checks) — the gate is never relaxed, only the
 * cadence. Before 2026-10-11 this was 1 post / 5 days + 1 tutorial / 7 days,
 * which at ~9 gate-passing drafts meant weeks of silence even with a backlog.
 *
 * Short-form daily AI News (post_type=news_brief) is published by its own
 * endpoint and is ignored here, so it neither blocks nor counts against this.
 *
 * Scheduled twice a day; the limits are self-regulated from published_at, so
 * running late/twice never over-publishes.
 */
class ContentDripPublish extends Command
{
    protected $signature = 'content:drip {--dry-run : Show what would publish without publishing}';
    protected $description = 'Publish quality-gated drafts: max 2/day, 5h apart, oldest first';

    public function __construct(private readonly PublishGate $gate)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        if (! $this->isDue()) {
            $this->info('Nothing published (daily limit reached or last publish too recent).');

            return self::SUCCESS;
        }

        $post = $this->pickPublishable();
        if (! $post) {
            $this->info('Nothing published (no gate-passing draft available).');

            return self::SUCCESS;
        }

        $label = ($post->series_title ? 'tutorial' : 'post') . ": {$post->title}";
        $this->publish($post, $dry);
        $this->info($dry ? "DRY RUN — would publish: {$label}" : "Published: {$label}");
        if (! $dry) {
            Log::info('content:drip published', ['items' => [$label]]);
        }

        return self::SUCCESS;
    }

    private function longForm()
    {
        return Post::where('post_type', '!=', Post::TYPE_NEWS_BRIEF);
    }

    /** Under the daily limit and past the minimum gap since the last publish? */
    private function isDue(): bool
    {
        $todayCount = $this->longForm()
            ->where('status', 'published')
            ->where('published_at', '>=', now()->startOfDay())
            ->count();
        if ($todayCount >= PublishGate::DRIP_DAILY_LIMIT) {
            return false;
        }

        $last = $this->longForm()->where('status', 'published')->max('published_at');

        return ! $last || Carbon::parse($last)->lte(now()->subHours(PublishGate::DRIP_MIN_GAP_HOURS));
    }

    /** Oldest gate-passing draft first, so the backlog drains in order. */
    private function pickPublishable(): ?Post
    {
        return $this->longForm()
            ->where('status', 'draft')
            ->orderBy('created_at')
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
