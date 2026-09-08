<?php

namespace Tests\Unit\Content;

use App\Models\Post;
use App\Services\Content\PublishGate;
use Tests\Support\MakesGateContent;
use Tests\TestCase;

class PublishGateTest extends TestCase
{
    use MakesGateContent;

    private function makePost(string $content, string $moderation = 'approved'): Post
    {
        $post = new Post();
        $post->content = $content;
        $post->moderation_status = $moderation;

        return $post;
    }

    public function test_toza_uzun_matn_barcha_darvozalardan_otadi(): void
    {
        $gate = new PublishGate();

        $this->assertSame([], $gate->failures($this->makePost($this->cleanContent())));
        $this->assertTrue($gate->passes($this->makePost($this->cleanContent())));
    }

    public function test_qisqa_matn_yiqiladi(): void
    {
        $gate = new PublishGate();

        $this->assertContains('too_short', $gate->failures($this->makePost('Juda qisqa matn.')));
    }

    public function test_kesilgan_matn_yiqiladi(): void
    {
        $gate = new PublishGate();
        $content = $this->cleanContent() . ' Bu jumla tugamay qoldi va nuqta yo';

        $this->assertContains('truncated', $gate->failures($this->makePost($content)));
    }

    public function test_yopilmagan_kod_bloki_yiqiladi(): void
    {
        $gate = new PublishGate();
        $content = $this->cleanContent() . " \n```php\n echo 1;\n";

        $this->assertContains('unbalanced_fences', $gate->failures($this->makePost($content)));
    }

    public function test_moderatsiya_kutilayotgan_post_yiqiladi(): void
    {
        $gate = new PublishGate();

        $this->assertContains(
            'moderation_pending',
            $gate->failures($this->makePost($this->cleanContent(), 'pending'))
        );
    }

    public function test_takrorlangan_jumlalar_rad_etiladi(): void
    {
        $gate = new PublishGate();
        $repeat = 'Bu jumla ataylab bir necha marta takrorlanadi va altmish belgidan uzunroq.';
        $content = $this->cleanContent() . ' ' . str_repeat($repeat . ' ', 5);

        $this->assertSame(4, $gate->redundantSentenceCount($content));
        $this->assertContains('duplicated_content', $gate->failures($this->makePost($content)));
    }

    public function test_takrorlanish_bilan_shishirilgan_uzunlik_otmaydi(): void
    {
        $gate = new PublishGate();
        // 300 so'zlik noyob matn, 6 marta takrorlangan: xom hisob 1500 dan oshadi,
        // lekin noyob hajm hamon juda kichik.
        $short = $this->cleanContent(300);
        $content = trim(str_repeat($short . ' ', 6));

        $this->assertGreaterThan(PublishGate::MIN_WORDS, str_word_count(strip_tags($content)));
        $this->assertLessThan(PublishGate::MIN_WORDS, $gate->uniqueWordCount($content));
        $this->assertContains('too_short', $gate->failures($this->makePost($content)));
    }

    public function test_toza_matnda_takrorlanish_yoq(): void
    {
        $gate = new PublishGate();

        $this->assertSame(0, $gate->redundantSentenceCount($this->cleanContent()));
    }

    /**
     * Tinish belgisisiz (masalan, kod bloki) takrorlanish ham aniqlanishi kerak.
     * `sentences()` endi qator chegaralaridan ham bo'ladi, shuning uchun ".", "!",
     * "?" bo'lmagan takrorlangan kod qatori ham dublikat sifatida hisoblanadi.
     */
    public function test_tinish_belgisiz_kod_bloki_takrorlanishi_aniqlanadi(): void
    {
        $gate = new PublishGate();

        $codeLine = '    $value = \'' . str_repeat('a', 60) . '\';';
        $codeBlock = "```php\n" . $codeLine . "\necho \$value;\n```";
        $content = $this->cleanContent() . "\n\n" . str_repeat($codeBlock . "\n\n", 6);

        $this->assertGreaterThan(2, $gate->redundantSentenceCount($content));
        $this->assertContains('duplicated_content', $gate->failures($this->makePost($content)));
    }

    /** Qator chegarasidan bo'lish oddiy (yangi qatorsiz) matnga ta'sir qilmasligi kerak. */
    public function test_qator_bolish_oddiy_matnga_tasir_qilmaydi(): void
    {
        $gate = new PublishGate();

        $this->assertSame(0, $gate->redundantSentenceCount($this->cleanContent()));
        $this->assertSame([], $gate->failures($this->makePost($this->cleanContent())));
    }

    /**
     * ENG MUHIM YASHIL YO'L: kod bilan to'la haqiqiy tutorial o'tishi SHART.
     *
     * `sentences()` qator chegaralaridan bo'lgani uchun har bir uzun kod qatori
     * alohida birlik. Yig'indi metrikasi bilan uchta misolda takrorlangan
     * import qatorlari maqolani `duplicated_content` bilan rad etardi — ya'ni
     * yangi darvozalar saytning asosiy kontentini abadiy bloklardi.
     */
    public function test_kod_bilan_tola_haqiqiy_maqola_barcha_darvozalardan_otadi(): void
    {
        $gate = new PublishGate();
        $content = $this->codeHeavyContent();

        $this->assertSame(
            [],
            $gate->failures($this->makePost($content)),
            'Kod bilan to\'la qonuniy tutorial darvozalardan o\'tishi kerak.'
        );
    }

    /**
     * Metrika endi YIG'INDI emas, ENG YOMON bitta takror.
     *
     * Uchta HAR XIL import qatori ikki martadan uchraydi: yig'indi 3 ball
     * (rad etilardi), maksimum esa 2 - 1 = 1 ball (o'tadi).
     */
    public function test_har_xil_qatorlarning_ikki_martalik_takrori_yigilmaydi(): void
    {
        $gate = new PublishGate();

        $importlar = [
            'use Illuminate\Support\Facades\Cache; // birinchi umumiy import qatori bu yerda',
            'use Illuminate\Support\Facades\Queue; // ikkinchi umumiy import qatori bu yerda',
            'use Illuminate\Support\Facades\Redis; // uchinchi umumiy import qatori bu yerda',
        ];

        $content = $this->cleanContent();
        foreach ($importlar as $import) {
            $content .= "\n\n```php\n" . $import . "\n```\n\n```php\n" . $import . "\n```";
        }
        $content .= "\n\nYakuniy nasr jumlasi bilan maqola tugaydi.";

        $this->assertSame(1, $gate->redundantSentenceCount($content));
        $this->assertNotContains('duplicated_content', $gate->failures($this->makePost($content)));
    }

    /**
     * #456-draft: BITTA kod bloki olti marta qaytarilgan. Maksimum metrikasi
     * ham buni ushlashi shart — 6 - 1 = 5 > MAX_REDUNDANT_SENTENCES.
     */
    public function test_bitta_blokning_olti_martalik_takrori_hamon_rad_etiladi(): void
    {
        $gate = new PublishGate();
        $qator = 'use Illuminate\Support\Facades\Cache; // ayni bitta qator olti marta qaytarildi';

        $content = $this->cleanContent();
        for ($i = 0; $i < 6; $i++) {
            $content .= "\n\n```php\n" . $qator . "\n```";
        }
        $content .= "\n\nYakuniy nasr jumlasi bilan maqola tugaydi.";

        $this->assertSame(5, $gate->redundantSentenceCount($content));
        $this->assertContains('duplicated_content', $gate->failures($this->makePost($content)));
    }

    /**
     * contentFailures() faqat matnga bog'liq darvozalarni qaytaradi:
     * failures() = contentFailures() + moderation_pending, boshqa hech narsa.
     */
    public function test_contentFailures_moderatsiya_holatini_tekshirmaydi(): void
    {
        $gate = new PublishGate();
        $content = $this->cleanContent();

        $this->assertSame([], $gate->contentFailures($content));
        $this->assertNotContains('moderation_pending', $gate->contentFailures($content));

        $this->assertSame(
            ['moderation_pending'],
            $gate->failures($this->makePost($content, 'pending'))
        );
    }

    /**
     * Generator ilgari o'z nusxasidagi `/[.!?]\s*$/` bilan tekshirar edi, ya'ni
     * `…correctly."` bilan tugagan maqolani "kesilgan" deb belgilardi. Endi
     * ikkalasi ham shu metodni chaqiradi.
     */
    public function test_qoshtirnoq_bilan_tugagan_matn_kesilgan_deb_belgilanmaydi(): void
    {
        $gate = new PublishGate();
        $content = $this->cleanContent() . ' The configuration must be written "correctly."';

        $this->assertNotContains('truncated', $gate->contentFailures($content));
    }

    public function test_soxta_tajriba_davolari_rad_etiladi(): void
    {
        $gate = new PublishGate();

        $namunalar = [
            'As a senior engineer with over 10 years of experience, I learned a lot.',
            'Last quarter, our team discovered a serious bottleneck in the system.',
            'In my experience, connection pooling solves most of these problems.',
            'Our team migrated the whole platform to a new database engine.',
        ];

        foreach ($namunalar as $namuna) {
            $content = $this->cleanContent() . ' ' . $namuna;

            $this->assertNotEmpty(
                $gate->fabricatedExperience($content),
                "Ushlanmadi: {$namuna}"
            );
            $this->assertContains('fabricated_experience', $gate->failures($this->makePost($content)));
        }
    }

    public function test_toza_texnik_matn_atribut_darvozasidan_otadi(): void
    {
        $gate = new PublishGate();

        $this->assertSame([], $gate->fabricatedExperience($this->cleanContent()));
    }

    /**
     * Fix round 1: patterns widened to catch realistic variants within the same
     * phrase families (article "the", have/has/had-introduced tenure claims,
     * auxiliary verbs and possessive "team's", and a first-person spent/been
     * tenure claim) without inventing new categories.
     */
    public function test_kengaytirilgan_soxta_tajriba_shakllari_rad_etiladi(): void
    {
        $gate = new PublishGate();

        $namunalar = [
            'As the lead engineer on this project, I made the final call.',
            'I have 10 years of experience building backend systems.',
            'She has 10 years in the industry and mentors new hires.',
            'Our team has discovered a clever workaround for this issue.',
            'Our team was building a new pipeline when the outage hit.',
            "My team's migration to Kubernetes took several weeks.",
            "I've spent five years building distributed systems.",
        ];

        foreach ($namunalar as $namuna) {
            $content = $this->cleanContent() . ' ' . $namuna;

            $this->assertNotEmpty(
                $gate->fabricatedExperience($content),
                "Ushlanmadi: {$namuna}"
            );
            $this->assertContains('fabricated_experience', $gate->failures($this->makePost($content)));
        }
    }

    /**
     * Eng muhim test: ikkinchi shaxs ("your team") va abstrakt texnik jumlalar
     * hech qachon fabricated_experience sifatida belgilanmasligi kerak — bu
     * darvoza nashrni bloklaydi, shuning uchun noto'g'ri rad juda qimmatga
     * tushadi.
     */
    public function test_ikkinchi_shaxs_va_abstrakt_matn_atribut_darvozasidan_otadi(): void
    {
        $gate = new PublishGate();

        $namunalar = [
            'When your team migrates to a new database, plan the cutover carefully.',
            'Your team should run load tests before every release.',
            'Teams that have shipped this pattern report fewer incidents.',
        ];

        foreach ($namunalar as $namuna) {
            $content = $this->cleanContent() . ' ' . $namuna;

            $this->assertSame(
                [],
                $gate->fabricatedExperience($content),
                "Noto'g'ri ushlandi: {$namuna}"
            );
            $this->assertNotContains('fabricated_experience', $gate->failures($this->makePost($content)));
        }
    }

    /**
     * PRODUCTION BUG: content is markdown, not HTML. `strip_tags()` reads a bare
     * `<` immediately followed by a letter (no surrounding whitespace, e.g. a
     * compact comparison like `$elapsed<self::LOCK_TTL_SECONDS`) as the start of
     * an unterminated tag and deletes everything to the end of the string before
     * the gate measures anything — verified directly against PHP 8.4's
     * strip_tags(): `< self` (with a space) is left alone, but `<self` is not,
     * and when nothing in the remaining article contains a literal `>` the
     * deletion runs to end-of-string. A real production draft lost 64% of its
     * words this way and was rejected as `too_short` at 3555 real words.
     *
     * This fixture reproduces the shape: clean prose, then a fenced code block
     * containing a spaceless `<` comparison, then a large amount of additional
     * clean prose AFTER the bare `<` — exactly the part that strip_tags()
     * silently erases. Before the fix this fails with `too_short` even though
     * the article is comfortably over MIN_WORDS. After the fix it must pass
     * every gate.
     */
    public function test_kod_ichidagi_taqqoslash_belgisi_maqolani_qisqartirmaydi(): void
    {
        $gate = new PublishGate();

        $codeBlock = "```php\nif (\$elapsed<self::LOCK_TTL_SECONDS) {\n    return true;\n}\n```";
        $content = "Kirish jumlasi maqolani boshlaydi."
            . "\n\n" . $codeBlock
            . "\n\n" . $this->cleanContent();

        $this->assertSame(
            [],
            $gate->failures($this->makePost($content)),
            "Kod ichidagi '<' taqqoslash belgisi undan keyingi matnni yo'q qilmasligi kerak."
        );
    }

    /**
     * Haqiqiy HTML teglari hamon olib tashlanishi kerak — yechim faqat
     * strip_tags()'ning noto'g'ri xatti-harakatini (belgisiz `<`) tuzatadi,
     * lekin haqiqiy teglarni matn sifatida qoldirmaydi.
     */
    public function test_haqiqiy_html_teglari_hamon_olib_tashlanadi(): void
    {
        $gate = new PublishGate();
        $content = '<p>Salom</p><div class="foo"></div>';

        $this->assertSame(1, $gate->uniqueWordCount($content));
        $this->assertNotContains('p', $gate->sentences($content));
        $this->assertNotContains('div', $gate->sentences($content));
    }

    /**
     * Regressiya: ilgari bare `<` dan keyingi butun matn strip_tags() tomonidan
     * o'chirilgani uchun undan keyin kelgan "in my experience" kabi soxta
     * tajriba da'vosi darvozaga UMUMAN ko'rinmas edi. Endi ko'rinishi shart.
     */
    public function test_bare_belgidan_keyingi_soxta_tajriba_hamon_aniqlanadi(): void
    {
        $gate = new PublishGate();

        $codeBlock = "```php\nif (\$elapsed<self::LOCK_TTL_SECONDS) {\n    return true;\n}\n```";
        $content = $this->cleanContent()
            . "\n\n" . $codeBlock
            . "\n\n" . 'In my experience, connection pooling solves most of these problems.';

        $this->assertNotEmpty(
            $gate->fabricatedExperience($content),
            'Bare "<" dan keyingi soxta tajriba iborasi ushlanishi kerak edi.'
        );
        $this->assertContains('fabricated_experience', $gate->failures($this->makePost($content)));
    }
}
