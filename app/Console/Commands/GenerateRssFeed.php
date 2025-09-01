<?php

namespace App\Console\Commands;

use App\Services\SeoService;
use Illuminate\Console\Command;

class GenerateRssFeed extends Command
{
    protected $signature = 'rss:generate';
    protected $description = 'Generate RSS feed for the website';

    public function handle(SeoService $seoService): int
    {
        $this->info('Generating RSS feed...');

        $seoService->generateRssFeed();

        $this->info('RSS feed generated successfully!');

        return Command::SUCCESS;
    }
}
