<?php

namespace Tests\Feature\Content;

use App\Models\CollectedContent;
use App\Models\ContentSource;
use App\Services\Content\TopicQueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicQueueServiceTest extends TestCase
{
    use RefreshDatabase;

    private function source(string $name, int $trust = 90): ContentSource
    {
        return ContentSource::create([
            'name' => $name,
            'url' => 'https://' . strtolower(str_replace(' ', '', $name)) . '.example',
            'category' => 'news',
            'trust_level' => $trust,
            'scraping_enabled' => true,
        ]);
    }

    private function item(ContentSource $s, string $title, ?int $primaryId = null, int $ageHours = 2): CollectedContent
    {
        return CollectedContent::create([
            'content_source_id' => $s->id,
            'external_url' => 'https://x.example/' . uniqid('', true),
            'title' => $title,
            'excerpt' => 'Excerpt for ' . $title,
            'full_content' => str_repeat('Body text about the subject. ', 20),
            'content_type' => 'article',
            'published_at' => now()->subHours($ageHours),
            'is_duplicate' => $primaryId !== null,
            'duplicate_of' => $primaryId,
        ]);
    }

    public function test_kop_manbali_klaster_bitta_manbalidan_yuqori_turadi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');
        $c = $this->source('Gamma');

        // Uch mustaqil manba bir mavzu haqida — bu trend.
        $primary = $this->item($a, 'Postgres 18 changes connection pooling');
        $this->item($b, 'Postgres 18 connection pooling rework', $primary->id);
        $this->item($c, 'What Postgres 18 does to pooling', $primary->id);

        // Bitta manba, yolg'iz.
        $this->item($a, 'A niche library nobody else covered');

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertNotEmpty($out);
        $this->assertSame('Postgres 18 changes connection pooling', $out->first()['title']);
        $this->assertSame(3, $out->first()['cluster_size']);
    }

    public function test_bitta_manba_ozini_trendga_aylantira_olmaydi(): void
    {
        $a = $this->source('Alpha');

        // Ayni manba bir mavzuni besh marta yozsa ham — bu trend emas.
        $primary = $this->item($a, 'Alpha keeps writing about the same thing');
        for ($i = 0; $i < 4; $i++) {
            $this->item($a, 'Same thing again ' . $i, $primary->id);
        }

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertTrue(
            $out->isEmpty(),
            'yagona manba klasteri nomzod bo\'lib qoldi'
        );
    }

    public function test_yangiroq_klaster_yuqoriroq_ballanadi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');

        $old = $this->item($a, 'Older subject', null, 24 * 6);
        $this->item($b, 'Older subject echo', $old->id, 24 * 6);

        $new = $this->item($a, 'Fresher subject', null, 2);
        $this->item($b, 'Fresher subject echo', $new->id, 2);

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertSame('Fresher subject', $out->first()['title']);
    }

    public function test_oyna_tashqarisidagi_kontent_hisobga_olinmaydi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');

        $old = $this->item($a, 'Ancient news', null, 24 * 30);
        $this->item($b, 'Ancient news echo', $old->id, 24 * 30);

        $out = app(TopicQueueService::class)->topCandidates(5, 7);

        $this->assertTrue($out->isEmpty(), 'oyna tashqarisidagi klaster qaytdi');
    }

    public function test_manbalar_havolalari_qaytariladi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');

        $primary = $this->item($a, 'Subject with sources');
        $this->item($b, 'Subject with sources echo', $primary->id);

        $first = app(TopicQueueService::class)->topCandidates(5, 7)->first();

        $this->assertCount(2, $first['sources']);
        foreach ($first['sources'] as $src) {
            $this->assertArrayHasKey('url', $src);
            $this->assertArrayHasKey('title', $src);
            $this->assertStringStartsWith('http', $src['url']);
        }
    }

    public function test_bosh_oyna_bosh_massiv_qaytaradi(): void
    {
        $this->assertTrue(app(TopicQueueService::class)->topCandidates(5, 7)->isEmpty());
    }

    public function test_generator_navbatdan_mavzu_oladi(): void
    {
        $a = $this->source('Alpha');
        $b = $this->source('Beta');
        $p = $this->item($a, 'Redis 8 changes eviction defaults');
        $this->item($b, 'Redis 8 eviction rework', $p->id);

        $command = new \App\Console\Commands\GenerateAiPost();
        $ref = new \ReflectionClass($command);
        $m = $ref->getMethod('topicFromQueue');
        $m->setAccessible(true);

        $topic = $m->invoke($command);

        $this->assertIsArray($topic);
        $this->assertSame('Redis 8 changes eviction defaults', $topic['title']);
        $this->assertArrayHasKey('sources', $topic);
        $this->assertNotEmpty($topic['sources']);
    }

    public function test_navbat_bosh_bolsa_null_qaytadi(): void
    {
        $command = new \App\Console\Commands\GenerateAiPost();
        $ref = new \ReflectionClass($command);
        $m = $ref->getMethod('topicFromQueue');
        $m->setAccessible(true);

        $this->assertNull($m->invoke($command));
    }

    /**
     * Regression: "Docker Autoscaling Best Practices Guide" nomzodi va
     * nashr etilgan "Kubernetes Autoscaling Best Practices" o'lchangan
     * similar_text() nisbati 0.7692 — bir xil tech-blog shabloni, lekin
     * mutlaqo boshqa texnologiyalar. Eski 0.7 bo'sag'asida bu rad etilardi;
     * 0.88 da nomzod saqlanib qolishi kerak.
     */
    public function test_boshqa_mavzu_bir_xil_shablon_bolsada_saqlanib_qoladi(): void
    {
        \App\Models\Post::factory()->create([
            'status' => 'published',
            'title' => 'Kubernetes Autoscaling Best Practices',
            'published_at' => now()->subDay(),
        ]);

        $a = $this->source('Alpha');
        $b = $this->source('Beta');
        $p = $this->item($a, 'Docker Autoscaling Best Practices Guide');
        $this->item($b, 'Docker Autoscaling Best Practices Guide (redux)', $p->id);

        $command = new \App\Console\Commands\GenerateAiPost();
        $ref = new \ReflectionClass($command);
        $m = $ref->getMethod('topicFromQueue');
        $m->setAccessible(true);

        $topic = $m->invoke($command);

        $this->assertIsArray($topic, 'genuinely distinct topic was wrongly skipped as too similar');
        $this->assertSame('Docker Autoscaling Best Practices Guide', $topic['title']);
    }

    /**
     * Near-identical sarlavha hali ham o'tkazib yuboriladi: "Redis 8 changes
     * eviction defaults" (ko'plik) va nashr etilgan "Redis 8 changes eviction
     * default" (birlik) o'rtasidagi o'lchangan nisbat 0.9697 — 0.88
     * bo'sag'asidan yuqori.
     */
    public function test_haqiqiy_takror_hali_ham_otkazib_yuboriladi(): void
    {
        \App\Models\Post::factory()->create([
            'status' => 'published',
            'title' => 'Redis 8 changes eviction default',
            'published_at' => now()->subDay(),
        ]);

        $a = $this->source('Alpha');
        $b = $this->source('Beta');
        $p = $this->item($a, 'Redis 8 changes eviction defaults');
        $this->item($b, 'Redis 8 eviction rework', $p->id);

        $command = new \App\Console\Commands\GenerateAiPost();
        $ref = new \ReflectionClass($command);
        $m = $ref->getMethod('topicFromQueue');
        $m->setAccessible(true);

        $this->assertNull($m->invoke($command), 'near-identical recent title was not filtered out');
    }
}
