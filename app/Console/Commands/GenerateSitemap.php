<?php

namespace App\Console\Commands;

use App\Services\SeoService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate XML sitemap for the website';

    public function handle(SeoService $seoService): int
    {
        $this->info('Generating sitemap...');

        $seoService->generateSitemap();

        $this->info('Sitemap generated successfully!');

        return Command::SUCCESS;
    }
}
