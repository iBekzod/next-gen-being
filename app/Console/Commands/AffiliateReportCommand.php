<?php

namespace App\Console\Commands;

use App\Services\Revenue\AffiliateStats;
use Illuminate\Console\Command;

class AffiliateReportCommand extends Command
{
    protected $signature = 'revenue:affiliate-report {--days=7}';
    protected $description = 'Print /go/ click totals per slug and the top posts by clicks';

    public function handle(AffiliateStats $stats): int
    {
        $this->line($stats->format($stats->summary(max(1, (int) $this->option('days')))));

        return self::SUCCESS;
    }
}
