<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class BackupScheduleTest extends TestCase
{
    private function event(string $command): Event
    {
        foreach (app(Schedule::class)->events() as $event) {
            if (str_ends_with((string) $event->command, "artisan' {$command}")
                || str_ends_with((string) $event->command, "artisan {$command}")) {
                return $event;
            }
        }

        $this->fail("{$command} is not scheduled");
    }

    public function test_backup_clean_runs_daily_before_the_monitor(): void
    {
        $clean = $this->event('backup:clean');
        $monitor = $this->event('backup:monitor');

        $this->assertSame('30 3 * * *', $clean->expression);
        $this->assertSame('0 4 * * *', $monitor->expression);
        $this->assertSame($monitor->timezone, $clean->timezone);
    }
}
