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

    /**
     * Generatsiya mo'ljali — nashr chegarasidan 15% yuqori (1725).
     *
     * Nashr chegarasi NOYOB so'zlarga nisbatan qo'llanadi, shuning uchun
     * chegarada turgan draftni bitta takrorlangan qator ham chegaradan pastga
     * tushirib yuboradi. Eski kodda 500 so'zlik zaxira bor edi (2000 yozib,
     * 1500 da nashr qilinardi); ikkalasini 1500 ga birlashtirish uni yo'q qildi.
     * Endi zaxira ataylab qaytarildi: kengaytirish shu mo'ljalgacha ishlaydi,
     * rad etish esa hamon MIN_WORDS bo'yicha.
     *
     * Qiymat = ceil(MIN_WORDS * 1.15). PHP konstanta ifodalarida ceil() ham,
     * (int) kastlash ham mumkin emas, shuning uchun yuqoriga yaxlitlash butun
     * sonli arifmetika bilan yozilgan (natija 100 ga bo'linadi, ya'ni int).
     * PublishGateTest buni ceil() ga nisbatan tekshiradi.
     */
    public const EXPANSION_TARGET_WORDS =
        (self::MIN_WORDS * 115 + 99 - (self::MIN_WORDS * 115 + 99) % 100) / 100;

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
        $failures = $this->contentFailures((string) $post->content);

        if ($post->moderation_status === 'pending') {
            $failures[] = 'moderation_pending';
        }

        return $failures;
    }

    /**
     * Faqat MATNGA bog'liq darvozalar — post holatidan mustaqil.
     *
     * Generator (GenerateAiPost) ham shu metodni chaqiradi: ilgari u
     * darvozalarni o'z ichida qayta yozgan edi va ikkala nusxa bir-biridan
     * uzoqlashib ketgandi (masalan, `…correctly."` bilan tugagan maqola
     * nashrchidan o'tardi, lekin generator uni "kesilgan" deb belgilardi).
     *
     * Bu yerda `moderation_pending` ATAYLAB yo'q: generator moderatsiya
     * holatini o'zi belgilaydi, shuning uchun uni bu yerda tekshirish
     * aylanma bog'liqlik bo'lardi.
     *
     * @return string[]
     */
    public function contentFailures(string $content): array
    {
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

    /**
     * Markdown-safe plain text.
     *
     * strip_tags() cannot be used here: the content is markdown, so a bare `<`
     * in a code example ("if ($x<self::TTL)") reads as an unterminated tag and
     * deletes the rest of the article before it is measured. Requiring a letter
     * after `<` removes real HTML tags while leaving comparison operators alone.
     *
     * The tag body is bounded to `[^<>\n]{0,200}` — no newline, no nested `<`,
     * capped length — so the match can only span something that actually looks
     * like a single HTML tag. Without that bound, `[^>]*` happily crosses
     * paragraph breaks: a generics-like `List<Item` followed, pages later, by
     * an unrelated `>` (a markdown blockquote marker, say) would delete every
     * word in between. That is the exact same failure class as the original
     * bug, just with a smaller blast radius, so it gets the same fix: require
     * the `>` that closes the tag to show up close by, on the same line.
     */
    private function plainText(string $content): string
    {
        return (string) preg_replace('/<\/?[a-zA-Z][^<>\n]{0,200}>/', '', $content);
    }

    /** Matnni normallashtirilgan jumlalarga bo'ladi. @return string[] */
    public function sentences(string $content): array
    {
        $text = $this->plainText($content);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Faqat probel/tab yig'iladi — qatorlarni ajratish uchun \n saqlanadi,
        // shunda tinish belgisiz takrorlangan qatorlar (masalan, kod bloklari)
        // ham alohida birlik sifatida hisoblanadi.
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $parts = preg_split('/(?<=[.!?])\s+|\n+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_map('trim', $parts ?: []);

        return array_values(array_filter($parts, fn (string $s): bool => $s !== ''));
    }

    /**
     * ENG YOMON bitta takrorlanish: max(uchrashlar) - 1, hech biri
     * takrorlanmasa 0.
     *
     * Ilgari bu barcha takrorlangan jumlalar bo'yicha YIG'INDI edi. `sentences()`
     * qator chegaralaridan ham bo'lgani uchun har bir uzun KOD QATORI alohida
     * birlik bo'ladi — uchta misolda bir xil `use Illuminate\...` importini
     * ko'rsatgan haqiqiy tutorial yig'indida 2+ ball to'plab, `duplicated_content`
     * bilan rad etilardi. Kod bilan to'la tutoriallar bu saytning asosiy
     * kontenti, ya'ni yig'indi metrikasi butun quvurni to'xtatib qo'yishi mumkin
     * edi.
     *
     * Maksimum esa aynan mo'ljaldagi buzilishni ushlaydi: #456-draft bitta kod
     * blokini 6 marta qaytargan (6 - 1 = 5 > MAX_REDUNDANT_SENTENCES), uch xil
     * import qatori ikki martadan uchragan holat esa 2 - 1 = 1 ball oladi va
     * o'tadi.
     */
    public function redundantSentenceCount(string $content): int
    {
        $long = array_filter(
            $this->sentences($content),
            fn (string $s): bool => mb_strlen($s) > self::LONG_SENTENCE_CHARS
        );

        $counts = array_count_values($long);

        return $counts === [] ? 0 : max(max($counts) - 1, 0);
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
        $text = (string) preg_replace('/\s+/', ' ', $this->plainText($content));

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
