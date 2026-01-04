<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeSingleSourceJob;
use App\Models\ContentSource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeAllSourcesCommand extends Command
{
    protected $signature = 'content:scrape-all {--limit=50 : Articles per source} {--async : Run async in queue}';
    protected $description = 'Scrape all enabled content sources';

    public function handle()
    {
        $this->info('🔍 Starting content scraping...');
        Log::info('ScrapeAllSourcesCommand started');

        try {
            $sources = ContentSource::active()
                ->orderByDesc('trust_level')
                ->get();

            if ($sources->isEmpty()) {
                $this->warn('No active sources found. Run: php artisan content:init-sources');
                return 1;
            }

            $this->info("Found {$sources->count()} active sources");

            $limit = (int) $this->option('limit');
            $async = $this->option('async');
            $count = 0;

            foreach ($sources as $source) {
                $this->line("  📰 {$source->name} ({$source->category})...");

                try {
                    if ($async) {
                        // Queue the job
                        ScrapeSingleSourceJob::dispatch($source->id, $limit)
                            ->onQueue('default');
                        $this->line("    ✓ Queued for scraping");
                    } else {
                        // Scrape synchronously
                        $scraper = new \App\Services\ContentScraperService();
                        $articlesFound = $scraper->scrapeSource($source, $limit);
                        $this->line("    ✓ Found {$articlesFound} articles");
                        $count += $articlesFound;
                    }

                } catch (\Exception $e) {
                    $this->error("    ✗ Failed: {$e->getMessage()}");
                    Log::error("Scraping failed for {$source->name}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($async) {
                $this->info("\n✓ All sources queued for scraping (async)");
            } else {
                $this->info("\n✓ Scraping completed! Found {$count} total articles");
            }

            Log::info("ScrapeAllSourcesCommand completed", ['articles_found' => $count]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('ScrapeAllSourcesCommand failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
