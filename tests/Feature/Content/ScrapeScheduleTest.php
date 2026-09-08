<?php

namespace Tests\Feature\Content;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrapeScheduleTest extends TestCase
{
    use RefreshDatabase;

    /** @return string[] */
    private function scheduledCommands(): array
    {
        return array_map(
            fn ($e) => (string) $e->command,
            app(Schedule::class)->events()
        );
    }

    public function test_scraping_jadvalga_qoyilgan(): void
    {
        $found = array_filter(
            $this->scheduledCommands(),
            fn (string $c) => str_contains($c, 'content:scrape-all')
        );

        $this->assertNotEmpty($found, 'content:scrape-all jadvalda yo\'q');
    }

    public function test_mavzu_ranking_jadvalga_qoyilgan(): void
    {
        $found = array_filter(
            $this->scheduledCommands(),
            fn (string $c) => str_contains($c, 'content:rank-topics')
        );

        $this->assertNotEmpty($found, 'content:rank-topics jadvalda yo\'q');
    }

    public function test_deduplikatsiya_jadvalga_qoyilgan(): void
    {
        $found = array_filter(
            $this->scheduledCommands(),
            fn (string $c) => str_contains($c, 'content:deduplicate')
        );

        $this->assertNotEmpty($found, 'content:deduplicate jadvalda yo\'q');
    }

    public function test_deduplikatsiya_72_soatlik_oyna_bilan_qoyilgan(): void
    {
        $found = array_filter(
            $this->scheduledCommands(),
            fn (string $c) => str_contains($c, 'content:deduplicate') && str_contains($c, '--hours=72')
        );

        $this->assertNotEmpty($found, 'content:deduplicate --hours=72 bilan jadvalda yo\'q');
    }
}
