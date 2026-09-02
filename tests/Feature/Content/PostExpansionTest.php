<?php

namespace Tests\Feature\Content;

use App\Console\Commands\GenerateAiPost;
use App\Services\Content\PublishGate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Tests\TestCase;

class PostExpansionTest extends TestCase
{
    /**
     * Generatsiya mo'ljali nashr chegarasidan qat'iy yuqori bo'lishi shart —
     * aks holda zaxira yo'qoladi va bitta takrorlangan qator ham chegarada
     * turgan draftni darvozadan tashqarida qoldiradi.
     */
    public function test_kengaytirish_moljali_nashr_chegarasidan_yuqori(): void
    {
        $this->assertGreaterThan(PublishGate::MIN_WORDS, PublishGate::EXPANSION_TARGET_WORDS);
        $this->assertSame(
            (int) ceil(PublishGate::MIN_WORDS * 1.15),
            PublishGate::EXPANSION_TARGET_WORDS,
            'EXPANSION_TARGET_WORDS = ceil(MIN_WORDS * 1.15) bo\'lishi kerak.'
        );
    }

    /**
     * O'lik zonaning yopilganini xulq-atvor darajasida isbotlaydi.
     *
     * Ikkala predikat ham bir xil (noyob) so'z sanog'ini oladi, lekin turli
     * chegaralarga taqqoslaydi: needsExpansion() — EXPANSION_TARGET_WORDS,
     * meetsPublishThreshold() — MIN_WORDS. Mo'ljal chegaradan yuqori bo'lgani
     * uchun "kengaytirilmaydi, lekin rad etiladi" holati mumkin emas: har
     * qanday rad etiladigan so'z soni ( < MIN_WORDS ) avval kengaytiriladi.
     */
    #[DataProvider('wordCountProvider')]
    public function test_kengaytirish_va_nashr_predikatlari_bir_xil_chegaraga_bogliq(
        int $wordCount,
        bool $expectedNeedsExpansion,
        bool $expectedMeetsThreshold
    ): void {
        $command = new GenerateAiPost();
        $reflection = new ReflectionClass($command);

        $needsExpansion = $reflection->getMethod('needsExpansion');
        $needsExpansion->setAccessible(true);

        $meetsThreshold = $reflection->getMethod('meetsPublishThreshold');
        $meetsThreshold->setAccessible(true);

        $this->assertSame(
            $expectedNeedsExpansion,
            $needsExpansion->invokeArgs($command, [$wordCount]),
            "needsExpansion({$wordCount}) kutilgan qiymatni qaytarmadi."
        );

        $this->assertSame(
            $expectedMeetsThreshold,
            $meetsThreshold->invokeArgs($command, [$wordCount]),
            "meetsPublishThreshold({$wordCount}) kutilgan qiymatni qaytarmadi."
        );
    }

    public static function wordCountProvider(): array
    {
        return [
            // Rad etiladigan har bir qiymat AVVAL kengaytiriladi.
            'juda qisqa (1200)' => [1200, true, false],
            'nashr chegarasidan bitta kam (1499)' => [1499, true, false],

            // Zaxira oralig'i: nashr qilsa bo'ladi, lekin marja yo'q — shuning
            // uchun kengaytiriladi. Kengaytirish MUVAFFAQIYATSIZ bo'lsa ham
            // draft saqlanadi (meetsPublishThreshold hamon true).
            'aynan nashr chegarasida (1500) - zaxira yoq, kengaytiriladi' => [1500, true, true],
            'eski olik zona ichida (1700) - kengaytiriladi va saqlanadi' => [1700, true, true],
            'moljaldan bitta kam (1724)' => [1724, true, true],

            // Mo'ljalga yetgan matn kengaytirilmaydi.
            'aynan moljalda (1725)' => [1725, false, true],
            'moljaldan ancha yuqori (2500)' => [2500, false, true],
        ];
    }

    /**
     * Zaxira oralig'idagi draft kengaytirish muvaffaqiyatsiz bo'lganda ham
     * TASHLANMAYDI: generatePostContent() ichida expandPostContent() istisnosi
     * ushlanadi, Pass 1 kontenti saqlanadi va keyingi
     * meetsPublishThreshold($wordCount) tekshiruvi hamon true qaytaradi.
     */
    public function test_zaxira_oraligidagi_draft_kengaytirish_yiqilsa_ham_saqlanadi(): void
    {
        $command = new GenerateAiPost();
        $reflection = new ReflectionClass($command);

        $needsExpansion = $reflection->getMethod('needsExpansion');
        $needsExpansion->setAccessible(true);
        $meetsThreshold = $reflection->getMethod('meetsPublishThreshold');
        $meetsThreshold->setAccessible(true);

        foreach ([PublishGate::MIN_WORDS, PublishGate::EXPANSION_TARGET_WORDS - 1] as $wordCount) {
            $this->assertTrue(
                $needsExpansion->invokeArgs($command, [$wordCount]),
                "{$wordCount} so'z kengaytirilishi kerak."
            );
            $this->assertTrue(
                $meetsThreshold->invokeArgs($command, [$wordCount]),
                "{$wordCount} so'z kengaytirish yiqilsa ham saqlanishi kerak."
            );
        }
    }

    private function invoke(string $method, array $args = []): mixed
    {
        $command = app(\App\Console\Commands\GenerateAiPost::class);
        $ref = new ReflectionClass($command);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($command, $args);
    }

    public function test_maqola_h2_sarlavhalari_boyicha_bolinadi(): void
    {
        $markdown = "Kirish matni.\n\n## Birinchi bo'lim\nBirinchi tana.\n\n## Ikkinchi bo'lim\nIkkinchi tana.";

        $sections = $this->invoke('splitSections', [$markdown]);

        $this->assertCount(3, $sections);
        $this->assertSame('', $sections[0]['heading']);
        $this->assertSame('Kirish matni.', $sections[0]['body']);
        $this->assertSame('## Birinchi bo\'lim', $sections[1]['heading']);
        $this->assertSame('Birinchi tana.', $sections[1]['body']);
        $this->assertSame('## Ikkinchi bo\'lim', $sections[2]['heading']);
        $this->assertSame('Ikkinchi tana.', $sections[2]['body']);
    }

    public function test_sarlavhasiz_matn_bitta_bolim_boladi(): void
    {
        $sections = $this->invoke('splitSections', ["Sarlavhasiz oddiy matn."]);

        $this->assertCount(1, $sections);
        $this->assertSame('', $sections[0]['heading']);
        $this->assertSame('Sarlavhasiz oddiy matn.', $sections[0]['body']);
    }

    public function test_bosh_tanali_sarlavha_qollab_quvvatlanadi(): void
    {
        $markdown = "## Faqat sarlavha\n\n## Keyingi sarlavha\nTana bor.";

        $sections = $this->invoke('splitSections', [$markdown]);

        $this->assertCount(2, $sections);
        $this->assertSame('## Faqat sarlavha', $sections[0]['heading']);
        $this->assertSame('', $sections[0]['body']);
        $this->assertSame('## Keyingi sarlavha', $sections[1]['heading']);
        $this->assertSame('Tana bor.', $sections[1]['body']);
    }

    public function test_crlf_qator_uzilishi_lf_bilan_bir_xil_natija_beradi(): void
    {
        $lfMarkdown = "Kirish matni.\n\n## Birinchi bo'lim\nBirinchi tana.\n\n## Ikkinchi bo'lim\nIkkinchi tana.";
        $crlfMarkdown = "Kirish matni.\r\n\r\n## Birinchi bo'lim\r\nBirinchi tana.\r\n\r\n## Ikkinchi bo'lim\r\nIkkinchi tana.";

        $lfSections = $this->invoke('splitSections', [$lfMarkdown]);
        $crlfSections = $this->invoke('splitSections', [$crlfMarkdown]);

        $this->assertCount(count($lfSections), $crlfSections);

        foreach ($lfSections as $i => $section) {
            $this->assertSame($section['heading'], $crlfSections[$i]['heading']);
            $this->assertSame($section['body'], $crlfSections[$i]['body']);
        }
    }

    public function test_uch_taqqa_sarlavha_yangi_bolim_boshlamaydi(): void
    {
        $markdown = "## Birinchi bo'lim\nTana matni.\n\n### Kichik sarlavha\nKichik bolim matni.\n\n## Ikkinchi bo'lim\nBoshqa tana.";

        $sections = $this->invoke('splitSections', [$markdown]);

        $this->assertCount(2, $sections);
        $this->assertSame('## Birinchi bo\'lim', $sections[0]['heading']);
        $this->assertStringContainsString('### Kichik sarlavha', $sections[0]['body']);
        $this->assertStringContainsString('Kichik bolim matni.', $sections[0]['body']);
        $this->assertSame('## Ikkinchi bo\'lim', $sections[1]['heading']);
        $this->assertSame('Boshqa tana.', $sections[1]['body']);
    }

    public function test_kod_blokidagi_taqqa_belgili_qator_sarlavha_boshlamaydi(): void
    {
        $markdown = "## Birinchi bo'lim\nTana matni.\n\n```bash\n## bu izoh, sarlavha emas\necho hi\n```\n\nDavomi matni.";

        $sections = $this->invoke('splitSections', [$markdown]);

        $this->assertCount(1, $sections);
        $this->assertSame('## Birinchi bo\'lim', $sections[0]['heading']);
        $this->assertStringContainsString('## bu izoh, sarlavha emas', $sections[0]['body']);
        $this->assertStringContainsString('Davomi matni.', $sections[0]['body']);
        $this->assertSame(2, substr_count($sections[0]['body'], '```'));
    }

    /**
     * Asosiy da'voni tekshiradi: expandPostContent() asl matnni HECH QACHON
     * qayta chiqarmaydi. Http::fake() orqali model chaqiruvini ushlab
     * qolamiz va aniq belgilangan javob qaytaramiz, so'ng yakuniy kontentda
     * har bir asl jumla ANIQ bir marta uchrashini tekshiramiz.
     */
    public function test_expandPostContent_asl_matnni_takrorlamaydi(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'ADDITION_MARKER']],
                ],
            ], 200),
        ]);

        $command = new GenerateAiPost();
        $this->setPrivateProviderProps($command);

        $postData = [
            'title' => 'Test post',
            'content' => "## Birinchi bo'lim\nBu birinchi jumla.\n\n## Ikkinchi bo'lim\nBu ikkinchi jumla.",
        ];

        $result = $this->invokeOn($command, 'expandPostContent', [$postData]);
        $content = $result['content'];

        $this->assertSame(1, substr_count($content, "Bu birinchi jumla."));
        $this->assertSame(1, substr_count($content, "Bu ikkinchi jumla."));
        $this->assertSame(2, substr_count($content, 'ADDITION_MARKER'));
    }

    public function test_expandPostContent_butunlay_oralgan_kod_bloki_ochiladi(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => "```php\necho 'salom';\n```"]],
                ],
            ], 200),
        ]);

        $command = new GenerateAiPost();
        $this->setPrivateProviderProps($command);

        $postData = [
            'title' => 'Test post',
            'content' => "## Bolim\nAsl matn.",
        ];

        $result = $this->invokeOn($command, 'expandPostContent', [$postData]);
        $content = $result['content'];

        $this->assertStringContainsString("echo 'salom';", $content);
        $this->assertSame(0, substr_count($content, '```'), 'Butunlay oralgan javobdagi ikkala tomon ham olib tashlanishi kerak.');
    }

    public function test_expandPostContent_haqiqiy_kod_misoli_teginilmaydi(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => "Mana misol:\n\n```php\necho 'salom';\n```\n\nDavomi matni."]],
                ],
            ], 200),
        ]);

        $command = new GenerateAiPost();
        $this->setPrivateProviderProps($command);

        $postData = [
            'title' => 'Test post',
            'content' => "## Bolim\nAsl matn.",
        ];

        $result = $this->invokeOn($command, 'expandPostContent', [$postData]);
        $content = $result['content'];

        $this->assertStringContainsString("```php\necho 'salom';\n```", $content);
        $this->assertSame(2, substr_count($content, '```'));
    }

    public function test_expandPostContent_kesilgan_kod_bloki_tashlab_yuboriladi(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Section addition discarded: unbalanced code fence', \Mockery::type('array'));

        Http::fake([
            '*' => Http::response([
                'choices' => [
                    ['message' => ['content' => "```php\necho 'salom';"]],
                ],
            ], 200),
        ]);

        $command = new GenerateAiPost();
        $this->setPrivateProviderProps($command);

        $postData = [
            'title' => 'Test post',
            'content' => "## Bolim\nAsl matn.",
        ];

        $result = $this->invokeOn($command, 'expandPostContent', [$postData]);
        $content = $result['content'];

        $this->assertStringNotContainsString("echo 'salom';", $content);
        $this->assertSame('## Bolim' . "\n" . 'Asl matn.', $content);
    }

    private function invokeOn(object $command, string $method, array $args = []): mixed
    {
        $ref = new ReflectionClass($command);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($command, $args);
    }

    private function setPrivateProviderProps(GenerateAiPost $command): void
    {
        $ref = new ReflectionClass($command);

        foreach ([
            'provider' => 'groq',
            'baseUrl' => 'https://api.groq.test/openai/v1',
            'apiKey' => 'test-key',
            'model' => 'test-model',
        ] as $name => $value) {
            $prop = $ref->getProperty($name);
            $prop->setAccessible(true);
            $prop->setValue($command, $value);
        }
    }
}
