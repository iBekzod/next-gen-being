<?php

namespace App\Console\Commands;

use App\Services\SeoService;
use Illuminate\Console\Command;

class PingSearchEngines extends Command
{
    protected $signature = 'seo:ping-search-engines';
    protected $description = 'Ping search engines about sitemap updates';

    public function handle(SeoService $seoService): int
    {
        $this->info('Pinging search engines...');

        $seoService->pingSearchEngines();

        $this->info('Search engines pinged successfully!');

        return Command::SUCCESS;
    }
}
