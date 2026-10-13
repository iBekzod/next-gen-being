<?php

namespace App\Services\Content;

/**
 * Places contextual /go/<slug> partner links into a post's markdown.
 *
 * Rules (owner decision 2026-10-13: posts must earn, but not spam):
 *  - at most MAX_LINKS distinct /go/ links per post (existing ones count);
 *  - a tool is linked only if the post is really about it (named in the title
 *    or at least MIN_MENTIONS times), never for a passing mention;
 *  - the link goes on the first mention in running prose: never inside code
 *    fences, inline code, headings, table rows, existing links or images;
 *  - one CTA box per post, for the most-discussed linked tool;
 *  - the affiliate disclosure line is added whenever the post has partner links.
 *
 * Pure string work (no database), so it is unit-testable and the artisan
 * command can show a dry-run diff before anything is saved.
 */
class AffiliateLinker
{
    public const MAX_LINKS = 4;
    public const MIN_MENTIONS = 2;
    public const CTA_MARKER = '> **Worth a look:**';

    /**
     * @param  array<string,array>  $programs  config('affiliate.links') entries to consider (already filtered)
     * @return array{content:string, added:string[], cta:bool, disclosure:bool, changed:bool}
     */
    public function apply(string $title, string $content, array $programs, string $baseUrl, int $maxLinks = self::MAX_LINKS): array
    {
        $original = $content;
        $added = [];

        $existing = $this->existingSlugs($content);
        $budget = max(0, $maxLinks - count($existing));

        // Rank candidate tools by how much the post talks about them.
        $candidates = [];
        foreach ($programs as $slug => $cfg) {
            if (in_array($slug, $existing, true) || empty($cfg['patterns'])) {
                continue;
            }
            $mentions = $this->countMentions($content, $cfg['patterns']);
            $inTitle = $this->matches($title, $cfg['patterns']);
            if ($mentions >= self::MIN_MENTIONS || ($inTitle && $mentions >= 1)) {
                $candidates[$slug] = $mentions + ($inTitle ? 100 : 0);
            }
        }
        arsort($candidates);

        foreach (array_keys($candidates) as $slug) {
            if ($budget <= 0) {
                break;
            }
            $cfg = $programs[$slug];
            $url = rtrim($baseUrl, '/') . '/go/' . $slug;
            $linked = $this->linkFirstProseMention($content, $cfg['patterns'], $cfg['anchor'], $url);
            if ($linked !== null) {
                $content = $linked;
                $added[] = $slug;
                $budget--;
            }
        }

        $all = array_values(array_unique(array_merge($existing, $added)));
        $cta = false;
        $disclosure = false;

        if ($all !== []) {
            if (! str_contains($content, self::CTA_MARKER)) {
                $top = $this->topSlug($all, $candidates, $programs, $content);
                $content = $this->insertCta($content, $programs[$top]['anchor'], rtrim($baseUrl, '/') . '/go/' . $top);
                $cta = true;
            }
            if (! preg_match('/affiliate-disclosure/i', $content)) {
                $content = $this->insertDisclosure($content, rtrim($baseUrl, '/'));
                $disclosure = true;
            }
        }

        return [
            'content' => $content,
            'added' => $added,
            'cta' => $cta,
            'disclosure' => $disclosure,
            'changed' => $content !== $original,
        ];
    }

    /** @return string[] slugs already linked via /go/ */
    public function existingSlugs(string $content): array
    {
        preg_match_all('#\]\((?:https?://[^/)\s]+)?/go/([a-z0-9-]+)#i', $this->stripCode($content), $m);

        return array_values(array_unique(array_map('strtolower', $m[1])));
    }

    private function stripCode(string $content): string
    {
        return preg_replace('/```.*?```/s', ' ', $content) ?? $content;
    }

    private function matches(string $text, array $patterns): bool
    {
        foreach ($patterns as $p) {
            if (preg_match('/\b' . preg_quote($p, '/') . '\b/i', $text)) {
                return true;
            }
        }

        return false;
    }

    /** Mentions in prose (code fences and inline code excluded). */
    private function countMentions(string $content, array $patterns): int
    {
        $prose = preg_replace('/`[^`\n]*`/', ' ', $this->stripCode($content)) ?? '';
        $n = 0;
        foreach ($patterns as $p) {
            $n += preg_match_all('/\b' . preg_quote($p, '/') . '\b/i', $prose);
        }

        return $n;
    }

    /**
     * Link the first prose mention. Returns null if no safe spot exists.
     */
    private function linkFirstProseMention(string $content, array $patterns, string $anchor, string $url): ?string
    {
        $lines = explode("\n", $content);
        $inFence = false;
        $alt = implode('|', array_map(fn ($p) => preg_quote($p, '/'), $patterns));
        $regex = '/\b(' . $alt . ')\b/i';

        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*(```|~~~)/', $line)) {
                $inFence = ! $inFence;
                continue;
            }
            if ($inFence || $this->isStructuralLine($line)) {
                continue;
            }

            // Hide spans we must not touch behind placeholders.
            $stash = [];
            $masked = preg_replace_callback(
                '/`[^`\n]*`|!?\[[^\]]*\]\([^)]*\)|<[^>]+>|https?:\/\/\S+/',
                function ($m) use (&$stash) {
                    $stash[] = $m[0];

                    return "\x01" . (count($stash) - 1) . "\x02";
                },
                $line
            );

            if (! preg_match($regex, $masked, $hit, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            // Keep the author's capitalisation of the matched word as link text.
            $text = $hit[1][0];
            $masked = substr_replace($masked, "[{$text}]({$url})", $hit[1][1], strlen($text));
            $lines[$i] = preg_replace_callback('/\x01(\d+)\x02/', fn ($m) => $stash[(int) $m[1]], $masked);

            return implode("\n", $lines);
        }

        return null;
    }

    /** Headings, table rows, blockquote CTAs, list-of-links TOC lines, rules. */
    private function isStructuralLine(string $line): bool
    {
        return (bool) preg_match('/^\s*(#{1,6}\s|\||>|---|\*\*\*|___|\d+\.\s*\[[^\]]+\]\(#)/', $line);
    }

    private function topSlug(array $all, array $candidates, array $programs, string $content): string
    {
        $best = null;
        $bestScore = -1;
        foreach ($all as $slug) {
            if (! isset($programs[$slug])) {
                continue;
            }
            $score = $candidates[$slug] ?? $this->countMentions($content, $programs[$slug]['patterns'] ?? []);
            if ($score > $bestScore) {
                $best = $slug;
                $bestScore = $score;
            }
        }

        // An already-linked tool that is no longer in the active program list.
        return $best ?? $all[0];
    }

    /** CTA box goes before the closing section (next steps / conclusion), else at the end. */
    private function insertCta(string $content, string $anchor, string $url): string
    {
        $box = self::CTA_MARKER . " {$anchor} is the tool this post covers in the most detail. If you want to try it, "
            . "[start with {$anchor}]({$url}) (affiliate link).";

        $lines = explode("\n", rtrim($content));
        $inFence = false;
        $closing = null;
        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*(```|~~~)/', $line)) {
                $inFence = ! $inFence;
                continue;
            }
            if (! $inFence && preg_match('/^##\s+.*\b(next steps?|conclusion|wrap(?:ping)?[- ]up|summary|final thoughts?|takeaways?|what to do next)\b/i', $line)) {
                $closing = $i;
            }
        }

        if ($closing !== null) {
            array_splice($lines, $closing, 0, [$box, '']);

            return implode("\n", $lines) . "\n";
        }

        return implode("\n", $lines) . "\n\n" . $box . "\n";
    }

    /** Disclosure goes after the first real paragraph. */
    private function insertDisclosure(string $content, string $baseUrl): string
    {
        $line = "*Some links in this post are affiliate links; see [our disclosure]({$baseUrl}/affiliate-disclosure).*";
        $lines = explode("\n", $content);
        $inFence = false;
        $seenText = false;
        foreach ($lines as $i => $l) {
            if (preg_match('/^\s*(```|~~~)/', $l)) {
                $inFence = ! $inFence;
            }
            if ($inFence) {
                continue;
            }
            $isText = trim($l) !== '' && ! preg_match('/^\s*(#|>|\||---|!\[|<)/', $l);
            if ($isText) {
                $seenText = true;
            } elseif ($seenText && trim($l) === '') {
                array_splice($lines, $i, 0, ['', $line]);

                return implode("\n", $lines);
            }
        }

        return rtrim($content) . "\n\n" . $line . "\n";
    }
}
