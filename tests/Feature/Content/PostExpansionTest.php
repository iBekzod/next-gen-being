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
    public function test_generatsiya_chegarasi_nashr_chegarasi_bilan_bir_xil(): void
    {
        $source = file_get_contents(app_path('Console/Commands/GenerateAiPost.php'));

        // O'lik zona: kengaytirish va rad etish chegaralari farq qilmasligi kerak.
        // Bu ikkinchi darajali himoya: asosiy isbot quyidagi xulq-atvorga
        // asoslangan testlarda (needsExpansion / meetsPublishThreshold).
        $this->assertStringNotContainsString(
            'Minimum required: 2000 words',
            $source,
            'Generatsiya hamon 2000 so\'z talab qilyapti, PublishGate::MIN_WORDS esa 1500.'
        );

        $this->assertStringContainsString(
            'PublishGate::MIN_WORDS',
            $source,
            'GenerateAiPost PublishGate::MIN_WORDS dan foydalanishi kerak.'
        );
    }

    public function test_publish_gate_chegarasi_kutilgan_qiymatda(): void
    {
        $this->assertSame(1500, PublishGate::MIN_WORDS);
    }

    /**
     * O'lik zonaning yopilganini xulq-atvor darajasida isbotlaydi: har bir
     * so'z soni uchun needsExpansion() va meetsPublishThreshold() natijalari
     * bir-biriga zid bo'lmasligi kerak (ya'ni 1500-1999 oralig'i endi
     * "kengaytirilmaydi va ham rad etiladi" holatiga tushmaydi).
     *
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
            'juda qisqa (1200)' => [1200, true, false],
            'chegaradan bitta kam (1499)' => [1499, true, false],
            'aynan chegarada (1500)' => [1500, false, true],
            'olik zona ichida (1700) - eng muhim tekshiruv' => [1700, false, true],
            'chegaradan ancha yuqori (2500)' => [2500, false, true],
        ];
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
