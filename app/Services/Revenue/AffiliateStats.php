<?php

namespace App\Services\Revenue;

use App\Models\OutboundClick;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

/**
 * Click numbers for /go/<slug> (outbound_clicks). Bots are never logged, so
 * these are human clicks. Conversions are NOT known here: PartnerStack and
 * the other networks report sales in their own dashboards, and no API key is
 * configured, so the report says "clicks only" instead of inventing a number.
 */
class AffiliateStats
{
    public function summary(int $days = 7, int $topPosts = 5): array
    {
        $since = now()->subDays($days);
        $prev = now()->subDays($days * 2);

        $perSlug = OutboundClick::query()
            ->where('created_at', '>=', $since)
            ->select('slug', 'is_affiliate', DB::raw('count(*) as clicks'), DB::raw('count(distinct ip_hash) as visitors'))
            ->groupBy('slug', 'is_affiliate')
            ->orderByDesc('clicks')
            ->get()
            ->map(fn ($r) => [
                'slug' => $r->slug,
                'earning' => (bool) $r->is_affiliate,
                'clicks' => (int) $r->clicks,
                'visitors' => (int) $r->visitors,
            ])->all();

        $prevTotal = OutboundClick::where('created_at', '>=', $prev)->where('created_at', '<', $since)->count();
        $total = OutboundClick::where('created_at', '>=', $since)->count();

        $top = OutboundClick::query()
            ->where('created_at', '>=', $since)
            ->whereNotNull('post_id')
            ->select('post_id', DB::raw('count(*) as clicks'))
            ->groupBy('post_id')
            ->orderByDesc('clicks')
            ->limit($topPosts)
            ->get();
        $titles = Post::whereIn('id', $top->pluck('post_id'))->pluck('title', 'id');

        return [
            'days' => $days,
            'total_clicks' => $total,
            'previous_period_clicks' => $prevTotal,
            'earning_clicks' => (int) collect($perSlug)->where('earning', true)->sum('clicks'),
            'per_slug' => $perSlug,
            'top_posts' => $top->map(fn ($r) => [
                'post_id' => (int) $r->post_id,
                'title' => (string) ($titles[$r->post_id] ?? '?'),
                'clicks' => (int) $r->clicks,
            ])->all(),
            'conversions' => null,
            'conversions_note' => 'Not available: networks report sales in their own dashboards (no API key configured). Clicks only.',
        ];
    }

    /** Plain-text version for the owner DM / artisan output. */
    public function format(array $s): string
    {
        $delta = $s['total_clicks'] - $s['previous_period_clicks'];
        $lines = [sprintf(
            'Affiliate clicks, last %d days: %d (%s%d vs previous period). Earning-program clicks: %d.',
            $s['days'], $s['total_clicks'], $delta >= 0 ? '+' : '', $delta, $s['earning_clicks']
        )];
        if ($s['per_slug'] === []) {
            $lines[] = 'No clicks logged.';
        }
        foreach ($s['per_slug'] as $r) {
            $lines[] = sprintf('  %s: %d clicks, %d visitors%s', $r['slug'], $r['clicks'], $r['visitors'], $r['earning'] ? '' : ' (no referral URL yet, earns nothing)');
        }
        if ($s['top_posts']) {
            $lines[] = 'Top posts by clicks:';
            foreach ($s['top_posts'] as $p) {
                $lines[] = sprintf('  #%d %s: %d', $p['post_id'], mb_substr($p['title'], 0, 70), $p['clicks']);
            }
        }
        $lines[] = 'Conversions: not tracked here (clicks only; check the PartnerStack dashboard).';

        return implode("\n", $lines);
    }
}
