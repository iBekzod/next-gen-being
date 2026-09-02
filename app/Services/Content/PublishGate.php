<?php

namespace App\Services\Content;

use App\Models\Post;

/**
 * Nashr darvozalarining yagona manbasi.
 *
 * Ilgari bu mantiq ContentDripPublish ichida private edi, shuning uchun
 * monitoring uni qayta ishlata olmasdi. Endi nashr qiluvchi ham,
 * content:health-check ham xuddi shu predikatni chaqiradi — ikkalasi
 * bir-biridan uzoqlashib keta olmaydi.
 */
class PublishGate
{
    public const POST_INTERVAL_DAYS = 5;
    public const TUTORIAL_INTERVAL_DAYS = 7;
    public const MIN_WORDS = 1500;

    /** Bundan ko'p ortiqcha nusxa = generatsiya buzilgan (spec §1.2a). */
    public const MAX_REDUNDANT_SENTENCES = 2;

    /** Faqat shu uzunlikdan katta jumlalar takrorlanish uchun hisobga olinadi. */
    private const LONG_SENTENCE_CHARS = 60;

    /**
     * Post yiqilgan darvozalar ro'yxati. Bo'sh massiv = nashrga tayyor.
     *
     * @return string[]
     */
    public function failures(Post $post): array
    {
        $content = (string) $post->content;
        $failures = [];

        if ($this->uniqueWordCount($content) < self::MIN_WORDS) {
            $failures[] = 'too_short';
        }

        if (! preg_match('/[.!?]["\')\]]?\s*$/', trim($content))) {
            $failures[] = 'truncated';
        }

        if (substr_count($content, '```') % 2 !== 0) {
            $failures[] = 'unbalanced_fences';
        }

        if ($post->moderation_status === 'pending') {
            $failures[] = 'moderation_pending';
        }

        if ($this->redundantSentenceCount($content) > self::MAX_REDUNDANT_SENTENCES) {
            $failures[] = 'duplicated_content';
        }

        if ($this->fabricatedExperience($content) !== []) {
            $failures[] = 'fabricated_experience';
        }

        return $failures;
    }

    public function passes(Post $post): bool
    {
        return $this->failures($post) === [];
    }

    /** Matnni normallashtirilgan jumlalarga bo'ladi. @return string[] */
    public function sentences(string $content): array
    {
        $text = strip_tags($content);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Faqat probel/tab yig'iladi — qatorlarni ajratish uchun \n saqlanadi,
        // shunda tinish belgisiz takrorlangan qatorlar (masalan, kod bloklari)
        // ham alohida birlik sifatida hisoblanadi.
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $parts = preg_split('/(?<=[.!?])\s+|\n+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_map('trim', $parts ?: []);

        return array_values(array_filter($parts, fn (string $s): bool => $s !== ''));
    }

    /** Ortiqcha nusxalar soni: takrorlangan har bir uzun jumla uchun (n - 1). */
    public function redundantSentenceCount(string $content): int
    {
        $long = array_filter(
            $this->sentences($content),
            fn (string $s): bool => mb_strlen($s) > self::LONG_SENTENCE_CHARS
        );

        $redundant = 0;
        foreach (array_count_values($long) as $occurrences) {
            if ($occurrences > 1) {
                $redundant += $occurrences - 1;
            }
        }

        return $redundant;
    }

    /**
     * Birinchi shaxsdagi kasbiy tajriba da'volari.
     *
     * Generatsiya qilingan post o'zida bo'lmagan karerani da'vo qilmasligi kerak:
     * bu Google HCU nishoni va imzo ostidagi halollik masalasi (spec §1.2a).
     *
     * @return string[] topilgan iboralar
     */
    public function fabricatedExperience(string $content): array
    {
        $text = (string) preg_replace('/\s+/', ' ', strip_tags($content));

        $patterns = [
            '/\bas (?:an?|the) (?:senior|seasoned|experienced|lead|principal|staff)\b/i',
            '/\b(?:with|have|has|had) (?:over |more than )?\d+\+? years? of experience\b|\b\d+\+? years? in the industry\b/i',
            '/\bin my (?:experience|career)\b/i',
            '/\bwhen i first started\b/i',
            '/\b(?:our|my) team(?:\'s)? (?:(?:has|have|had|was|were|\'ve) )?(?:discovered|discovering|learned|learning|built|building|shipped|shipping|migrated|migrating|migration|ran|running)\b/i',
            '/\blast (?:quarter|month|year),? (?:we|our|i)\b/i',
            '/\b(?:i|we)(?:\'ve| have| had)? (?:spent|been) (?:\d+|a|one|two|three|four|five|six|seven|eight|nine|ten)\+? (?:years?|months?)\b/i',
        ];

        $hits = [];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                $hits[] = $matches[0];
            }
        }

        return $hits;
    }

    /**
     * Dublikatlar olib tashlangandan keyingi so'z soni.
     *
     * Uzunlikni takrorlash orqali sun'iy oshirib bo'lmasligi uchun
     * MIN_WORDS aynan shu qiymatga nisbatan qo'llanadi.
     */
    public function uniqueWordCount(string $content): int
    {
        $unique = [];
        foreach ($this->sentences($content) as $sentence) {
            $unique[$sentence] = true;
        }

        return str_word_count(implode(' ', array_keys($unique)));
    }
}
