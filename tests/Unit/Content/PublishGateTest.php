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
}
