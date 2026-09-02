<?php

namespace Tests\Feature\Content;

use App\Console\Commands\GenerateAiPost;
use App\Services\Content\PublishGate;
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
        $this->assertSame('## Birinchi bo\'lim', $sections[1]['heading']);
        $this->assertSame('## Ikkinchi bo\'lim', $sections[2]['heading']);
    }

    public function test_sarlavhasiz_matn_bitta_bolim_boladi(): void
    {
        $sections = $this->invoke('splitSections', ["Sarlavhasiz oddiy matn."]);

        $this->assertCount(1, $sections);
        $this->assertSame('', $sections[0]['heading']);
    }
}
