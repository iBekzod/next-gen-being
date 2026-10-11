<?php

namespace App\Services\Content;

/**
 * Light gate for the short-form daily "AI News" post (post_type=news_brief).
 *
 * The long-form PublishGate (1500 unique words, quality review) makes no sense
 * for a roundup of 3-8 short items, so the brief has its own deterministic
 * rules instead of skipping checks: every item needs a primary source link,
 * a 2-4 sentence English summary, and nothing that reads as invented first-person
 * experience. Moderation (ContentModerationService, shortForm) still runs on top.
 *
 * The server cannot verify facts against the digest it came from; that check
 * (source domain must appear in the digest text) lives in the sender, ai-news.js.
 */
class NewsBriefGate
{
    public const MIN_ITEMS = 2;
    public const MAX_ITEMS = 8;
    public const MIN_SENTENCES = 2;
    public const MAX_SENTENCES = 4;
    public const MIN_WORDS_PER_ITEM = 15;
    public const MAX_WORDS_PER_ITEM = 120;

    private const SHORTENERS = ['bit.ly', 't.co', 'tinyurl.com', 'goo.gl', 'ow.ly', 'is.gd', 'buff.ly'];

    /** Words that only occur in Uzbek (Latin) text; the site is English-only. */
    private const NON_ENGLISH = ['va', 'bilan', 'uchun', 'lekin', 'bugun', 'yangilik', 'yangilanish', 'manba', 'ham', 'degan', 'bo\'ldi', 'qilingan', 'chiqqan', 'sizga', 'foydasi'];

    /**
     * @param array<int, array{headline?:mixed, summary?:mixed, source_url?:mixed, source_name?:mixed}> $items
     * @return string[] failure codes; empty = publishable
     */
    public function itemFailures(array $items): array
    {
        $failures = [];

        if (count($items) < self::MIN_ITEMS) {
            $failures[] = 'too_few_items';
        }
        if (count($items) > self::MAX_ITEMS) {
            $failures[] = 'too_many_items';
        }

        $seen = [];
        foreach ($items as $i => $item) {
            $n = $i + 1;
            $headline = trim((string) ($item['headline'] ?? ''));
            $summary = trim((string) ($item['summary'] ?? ''));
            $url = trim((string) ($item['source_url'] ?? ''));

            if (mb_strlen($headline) < 8 || mb_strlen($headline) > 140) {
                $failures[] = "item{$n}:bad_headline";
            }

            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            if (! preg_match('#^https://#i', $url) || $host === '' || ! str_contains($host, '.')
                || filter_var($url, FILTER_VALIDATE_URL) === false
                || in_array(preg_replace('/^www\./', '', $host), self::SHORTENERS, true)) {
                $failures[] = "item{$n}:no_valid_source";
            } elseif (isset($seen[$url])) {
                $failures[] = "item{$n}:duplicate_source";
            }
            $seen[$url] = true;

            $words = str_word_count($summary);
            if ($words < self::MIN_WORDS_PER_ITEM || $words > self::MAX_WORDS_PER_ITEM) {
                $failures[] = "item{$n}:summary_length";
            }
            $sentences = count(preg_split('/(?<=[.!?])\s+/', $summary, -1, PREG_SPLIT_NO_EMPTY) ?: []);
            if ($sentences < self::MIN_SENTENCES || $sentences > self::MAX_SENTENCES) {
                $failures[] = "item{$n}:sentence_count";
            }

            if (! $this->looksEnglish($headline . ' ' . $summary)) {
                $failures[] = "item{$n}:not_english";
            }
            if (app(PublishGate::class)->fabricatedExperience($summary) !== []) {
                $failures[] = "item{$n}:fabricated_experience";
            }
        }

        return $failures;
    }

    /** Failures of an already-built post body (used by PublishGate for news_brief posts). */
    public function contentFailures(string $content): array
    {
        $failures = [];

        $sources = preg_match_all('#\]\((https://[^)\s]+)\)#', $content);
        $sections = preg_match_all('/^## /m', $content);
        if ($sections < self::MIN_ITEMS) {
            $failures[] = 'too_few_items';
        }
        if ($sources < $sections) {
            $failures[] = 'missing_source_links';
        }
        if (! $this->looksEnglish($content)) {
            $failures[] = 'not_english';
        }
        if (app(PublishGate::class)->fabricatedExperience($content) !== []) {
            $failures[] = 'fabricated_experience';
        }
        if (substr_count($content, '```') % 2 !== 0) {
            $failures[] = 'unbalanced_fences';
        }

        return $failures;
    }

    public function looksEnglish(string $text): bool
    {
        $letters = preg_match_all('/\p{L}/u', $text);
        if ($letters === 0) {
            return false;
        }
        // Allow a little non-ASCII punctuation/names, not a different script.
        $ascii = preg_match_all('/[A-Za-z]/', $text);
        if ($ascii / $letters < 0.97) {
            return false;
        }

        $tokens = preg_split('/[^a-z\']+/', strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $hits = 0;
        foreach ($tokens as $t) {
            if (in_array($t, self::NON_ENGLISH, true)) {
                $hits++;
            }
        }

        return $hits <= 1;
    }
}
