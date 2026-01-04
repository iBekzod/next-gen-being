<?php

namespace App\Console\Commands;

use App\Jobs\FindDuplicatesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FindDuplicatesCommand extends Command
{
    protected $signature = 'content:deduplicate {--hours=24 : Hours to look back} {--async : Run async in queue}';
    protected $description = 'Find and group duplicate/similar content';

    public function handle()
    {
        $this->info('🔀 Finding duplicate content...');
        Log::info('FindDuplicatesCommand started');

        try {
            $hours = (int) $this->option('hours');
            $async = $this->option('async');

            if ($async) {
                FindDuplicatesJob::dispatch($hours)
                    ->onQueue('default');
                $this->info("✓ Deduplication queued (will look back {$hours} hours)");
                Log::info('Deduplication job queued', ['hours' => $hours]);
            } else {
                $dedup = new \App\Services\ContentDeduplicationService();

                $this->line("Looking back {$hours} hours for unprocessed content...");
                $aggregationsCreated = $dedup->findAllDuplicates($hours);
                $this->line("✓ Created {$aggregationsCreated} aggregations");

                $this->line("Merging related aggregations...");
                $merged = $dedup->mergeRelatedAggregations();
                $this->line("✓ Merged {$merged} aggregation pairs");

                $stats = $dedup->getAggregationStats();
                $this->info("\n📊 Statistics:");
                $this->line("  Total aggregations: {$stats['total_aggregations']}");
                $this->line("  High confidence (85%+): {$stats['high_confidence']}");
                $this->line("  Medium confidence (75-85%): {$stats['medium_confidence']}");
                $this->line("  Avg confidence: {$stats['avg_confidence']}");

                Log::info('FindDuplicatesCommand completed', $stats);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('FindDuplicatesCommand failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
