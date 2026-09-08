<?php

namespace App\Services\Content;

use App\Models\CollectedContent;
use Illuminate\Support\Collection;

/**
 * Yig'ilgan kontentdan trend mavzular navbatini yasaydi.
 *
 * `collected_content` da hech qanday jalb qilish signali yo'q — na ovoz,
 * na yulduz, na izoh. Shuning uchun trend signali sifatida MUSTAQIL
 * MANBALAR TASDIG'I ishlatiladi: bir mavzu bir necha alohida manbada bir
 * vaqtda chiqsa, bu ta'rifi bo'yicha trend (spec §6).
 *
 * Klasterlash bu yerda yozilmaydi: ContentDeduplicationService allaqachon
 * `is_duplicate` va `duplicate_of` ustunlarini to'ldiradi, ya'ni bir mavzu
 * haqidagi maqolalar guruhi tayyor.
 */
class TopicQueueService
{
    /** Trend deb hisoblash uchun kerak bo'lgan eng kam MUSTAQIL manba soni. */
    public const MIN_INDEPENDENT_SOURCES = 2;

    public const DEFAULT_WINDOW_DAYS = 7;

    /**
     * @return Collection<int, array{title:string,category:string,score:float,cluster_size:int,sources:array}>
     */
    public function topCandidates(int $limit = 10, int $windowDays = self::DEFAULT_WINDOW_DAYS): Collection
    {
        $since = now()->subDays($windowDays);

        // Oyna `published_at` YOKI `created_at` bo'yicha ochiladi.
        //
        // Sabab: ContentDeduplicationService qatorlarni `created_at` bo'yicha
        // tanlaydi, bu yerda esa ilgari faqat `published_at` filtrlanardi.
        // Feed eski `pubDate` bergan maqola (masalan hafta oldin nashr
        // qilingan, lekin bugun yig'ilgan) deduplikatsiyadan o'tib klaster
        // hosil qilardi-yu, ranglash uchun ko'rinmay qolardi — ya'ni
        // deduplikator qilgan ishi ranglashga hech qachon yetib bormasdi.
        $rows = CollectedContent::with('source')
            ->where(function ($q) use ($since) {
                $q->where('published_at', '>=', $since)
                    ->orWhere('created_at', '>=', $since);
            })
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        // Klaster kaliti: dublikat bo'lsa — asl yozuv, aks holda o'zi.
        $clusters = $rows->groupBy(fn (CollectedContent $c) => $c->duplicate_of ?: $c->id);

        return $clusters
            ->map(fn (Collection $items) => $this->scoreCluster($items, $windowDays))
            ->filter()
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /** @param Collection<int, CollectedContent> $items */
    private function scoreCluster(Collection $items, int $windowDays): ?array
    {
        $sourceIds = $items->pluck('content_source_id')->unique();

        // Yagona manba o'zini trendga aylantira olmaydi.
        if ($sourceIds->count() < self::MIN_INDEPENDENT_SOURCES) {
            return null;
        }

        $primary = $items->firstWhere('duplicate_of', null) ?? $items->first();

        $meanTrust = (float) $items
            ->map(fn (CollectedContent $c) => (int) ($c->source->trust_level ?? 50))
            ->avg();

        $newest = $items->max('published_at');
        $ageDays = $newest ? max(0.0, (float) $newest->diffInDays(now(), true)) : (float) $windowDays;
        $recency = max(0.1, 1.0 - ($ageDays / max(1, $windowDays)));

        $score = $sourceIds->count() * $meanTrust * $recency;

        return [
            'title' => (string) $primary->title,
            'category' => (string) ($primary->source->category ?? 'news'),
            'score' => round($score, 2),
            'cluster_size' => $items->count(),
            'sources' => $items->map(fn (CollectedContent $c) => [
                'title' => (string) $c->title,
                'url' => (string) $c->external_url,
                'published_at' => optional($c->published_at)->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
