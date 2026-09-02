<?php

namespace Tests\Unit\Content;

use App\Models\Post;
use App\Services\Content\PublishGate;
use Tests\TestCase;

class PublishGateTest extends TestCase
{
    private function makePost(string $content, string $moderation = 'approved'): Post
    {
        $post = new Post();
        $post->content = $content;
        $post->moderation_status = $moderation;

        return $post;
    }

    /** Har biri ~18 so'zdan iborat, 60 belgidan uzun, TAKRORLANMAYDIGAN jumlalar. */
    private function cleanContent(int $minWords = 1600): string
    {
        $sentences = [];
        $i = 0;
        do {
            $i++;
            $sentences[] = "Bu {$i}-raqamli noyob izohli jumla bo'lib, ishlab chiqarish tizimlarida "
                . "ma'lumotlar bazasi indekslash va so'rov rejalashtirish haqida gapiradi.";
        } while (str_word_count(implode(' ', $sentences)) < $minWords);

        return implode(' ', $sentences);
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
}
