<?php

namespace App\Console\Commands;

use App\Jobs\ParaphraseAggregationJob;
use App\Jobs\ExtractReferencesJob;
use App\Jobs\SendReviewNotificationJob;
use App\Models\ContentAggregation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ParaphrasePendingCommand extends Command
{
    protected $signature = 'content:paraphrase-pending {--limit=10 : Max aggregations to process} {--language=en : Language to paraphrase}';
    protected $description = 'Paraphrase pending aggregations into draft posts';

    public function handle()
    {
        $this->info('📝 Processing pending aggregations...');
        Log::info('ParaphrasePendingCommand started');

        try {
            $limit = (int) $this->option('limit');
            $language = $this->option('language');

            // Get pending aggregations (not yet curated)
            $pending = ContentAggregation::notYetCurated()
                ->latest()
                ->limit($limit)
                ->get();

            if ($pending->isEmpty()) {
                $this->info('No pending aggregations to process');
                return 0;
            }

            $this->info("Found {$pending->count()} aggregations to process");

            $count = 0;
            foreach ($pending as $aggregation) {
                try {
                    $this->line("  ✓ {$aggregation->topic}");

                    // Queue paraphrasing
                    ParaphraseAggregationJob::dispatch($aggregation->id, $language)
                        ->onQueue('default');

                    $count++;

                } catch (\Exception $e) {
                    $this->error("  ✗ Failed: {$e->getMessage()}");
                    Log::error("Paraphrasing job dispatch failed", [
                        'aggregation_id' => $aggregation->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("\n✓ Queued {$count} aggregations for paraphrasing");
            Log::info('ParaphrasePendingCommand completed', ['queued' => $count]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('ParaphrasePendingCommand failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
