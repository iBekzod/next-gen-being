<?php

namespace App\Filament\Widgets;

use App\Models\CollectedContent;
use App\Models\ContentAggregation;
use App\Models\ContentSource;
use App\Models\Post;
use App\Models\TutorialCollection;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class ContentCurationStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.content-curation-stats';

    protected int | string | array $columnSpan = 'full';

    public function getStats(): array
    {
        $stats = Cache::remember('content_curation_stats', 300, function () {
            $now = now();
            $today = $now->startOfDay();

            return [
                'total_sources' => ContentSource::count(),
                'active_sources' => ContentSource::where('scraping_enabled', true)->count(),
                'articles_today' => CollectedContent::whereDate('created_at', $today)->count(),
                'articles_total' => CollectedContent::count(),
                'aggregations_pending' => ContentAggregation::whereNull('curated_at')->count(),
                'aggregations_total' => ContentAggregation::count(),
                'curated_posts' => Post::where('is_curated', true)->count(),
                'draft_posts' => Post::where('is_curated', true)->where('status', 'draft')->count(),
                'published_posts' => Post::where('is_curated', true)->where('status', 'published')->count(),
                'translations_count' => Post::where('is_curated', true)->where('base_post_id', '!=', null)->count(),
                'tutorials_total' => TutorialCollection::count(),
                'tutorials_published' => TutorialCollection::where('status', 'published')->count(),
                'avg_confidence_score' => round(
                    Post::where('is_curated', true)
                        ->whereNotNull('paraphrase_confidence_score')
                        ->avg('paraphrase_confidence_score') * 100,
                    1
                ),
                'fact_verified_count' => Post::where('is_curated', true)->where('is_fact_verified', true)->count(),
                'last_scrape' => ContentSource::whereNotNull('last_scraped_at')
                    ->orderByDesc('last_scraped_at')
                    ->value('last_scraped_at'),
            ];
        });

        return $stats;
    }

    public function getAggregationStats(): array
    {
        $aggregations = ContentAggregation::all();

        $highConfidence = $aggregations->filter(fn ($a) => $a->confidence_score >= 0.85)->count();
        $mediumConfidence = $aggregations->filter(fn ($a) => $a->confidence_score >= 0.75 && $a->confidence_score < 0.85)->count();
        $lowConfidence = $aggregations->filter(fn ($a) => $a->confidence_score < 0.75)->count();

        return [
            'high_confidence' => $highConfidence,
            'medium_confidence' => $mediumConfidence,
            'low_confidence' => $lowConfidence,
        ];
    }

    public function getPipelineStatus(): array
    {
        $stats = $this->getStats();

        return [
            'has_pending_aggregations' => $stats['aggregations_pending'] > 0,
            'has_draft_posts' => $stats['draft_posts'] > 0,
            'sources_active' => $stats['active_sources'] > 0,
            'last_scrape_ago' => $stats['last_scrape']
                ? $stats['last_scrape']->diffForHumans()
                : 'Never',
        ];
    }
}
