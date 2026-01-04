<?php

namespace App\Jobs;

use App\Models\TutorialCollection;
use App\Services\ContentAggregatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AggregateeTutorialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $topic;
    protected int $maxSources;
    protected string $skillLevel;

    public $timeout = 300; // 5 minutes
    public $tries = 2;
    public $backoff = [120, 300]; // 2 min, 5 min

    public function __construct(
        string $topic,
        int $maxSources = 5,
        string $skillLevel = 'intermediate'
    ) {
        $this->topic = $topic;
        $this->maxSources = $maxSources;
        $this->skillLevel = $skillLevel;
    }

    public function handle(): void
    {
        Log::info("Starting AggregateeTutorialsJob", [
            'topic' => $this->topic,
            'max_sources' => $this->maxSources,
            'skill_level' => $this->skillLevel,
        ]);

        try {
            // Check if collection already exists
            $existing = TutorialCollection::where('topic', $this->topic)
                ->where('skill_level', $this->skillLevel)
                ->whereNotNull('compiled_content')
                ->first();

            if ($existing) {
                Log::info("Tutorial collection already exists for topic", [
                    'topic' => $this->topic,
                    'collection_id' => $existing->id,
                ]);
                return;
            }

            $aggregator = new ContentAggregatorService();
            $collection = $aggregator->aggregateTutorials(
                $this->topic,
                $this->maxSources,
                $this->skillLevel
            );

            if ($collection) {
                Log::info("Tutorial aggregation completed successfully", [
                    'topic' => $this->topic,
                    'collection_id' => $collection->id,
                    'sources_used' => $collection->getSourceCount(),
                    'steps_created' => $collection->getStepCount(),
                ]);
            } else {
                Log::warning("Tutorial aggregation returned null", [
                    'topic' => $this->topic,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("AggregateeTutorialsJob failed", [
                'topic' => $this->topic,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("AggregateeTutorialsJob permanently failed", [
            'topic' => $this->topic,
            'error' => $exception->getMessage(),
        ]);
    }
}
