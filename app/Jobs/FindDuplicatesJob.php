<?php

namespace App\Jobs;

use App\Services\ContentDeduplicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FindDuplicatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $sinceHours;

    public $timeout = 600; // 10 minutes
    public $tries = 2;
    public $backoff = [300, 900]; // 5 min, 15 min

    public function __construct(int $sinceHours = 24)
    {
        $this->sinceHours = $sinceHours;
    }

    public function handle(): void
    {
        Log::info("Starting FindDuplicatesJob", [
            'since_hours' => $this->sinceHours,
        ]);

        try {
            $dedup = new ContentDeduplicationService();
            $aggregationsCreated = $dedup->findAllDuplicates($this->sinceHours);

            Log::info("Deduplication completed successfully", [
                'since_hours' => $this->sinceHours,
                'aggregations_created' => $aggregationsCreated,
            ]);

            // Merge related aggregations
            $merged = $dedup->mergeRelatedAggregations();
            Log::info("Aggregation merging completed", [
                'merged_count' => $merged,
            ]);

        } catch (\Exception $e) {
            Log::error("FindDuplicatesJob failed", [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("FindDuplicatesJob permanently failed", [
            'error' => $exception->getMessage(),
        ]);
    }
}
