<?php

namespace App\Jobs;

use App\Models\ContentSource;
use App\Services\ContentScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeSingleSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $sourceId;
    protected int $limit;
    protected int $attempt = 0;

    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $backoff = [60, 300, 600]; // 1 min, 5 min, 10 min

    public function __construct(int $sourceId, int $limit = 50)
    {
        $this->sourceId = $sourceId;
        $this->limit = $limit;
    }

    public function handle(): void
    {
        Log::info("Starting ScrapeSingleSourceJob", [
            'source_id' => $this->sourceId,
            'limit' => $this->limit,
        ]);

        try {
            $source = ContentSource::find($this->sourceId);

            if (!$source) {
                Log::warning("Source not found", ['source_id' => $this->sourceId]);
                return;
            }

            if (!$source->scraping_enabled) {
                Log::info("Source scraping disabled", ['source_id' => $this->sourceId]);
                return;
            }

            $scraper = new ContentScraperService();
            $articlesFound = $scraper->scrapeSource($source, $this->limit);

            Log::info("Scraping completed successfully", [
                'source_id' => $this->sourceId,
                'source_name' => $source->name,
                'articles_found' => $articlesFound,
            ]);

        } catch (\Exception $e) {
            Log::error("ScrapeSingleSourceJob failed", [
                'source_id' => $this->sourceId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ScrapeSingleSourceJob permanently failed", [
            'source_id' => $this->sourceId,
            'error' => $exception->getMessage(),
        ]);

        // Could send notification here
    }
}
