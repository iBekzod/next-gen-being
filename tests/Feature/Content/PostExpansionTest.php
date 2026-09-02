<?php

namespace Tests\Feature\Content;

use App\Services\Content\PublishGate;
use ReflectionClass;
use Tests\TestCase;

class PostExpansionTest extends TestCase
{
    public function test_generatsiya_chegarasi_nashr_chegarasi_bilan_bir_xil(): void
    {
        $source = file_get_contents(app_path('Console/Commands/GenerateAiPost.php'));

        // O'lik zona: kengaytirish va rad etish chegaralari farq qilmasligi kerak.
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
}
