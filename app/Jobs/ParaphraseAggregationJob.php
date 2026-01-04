<?php

namespace App\Jobs;

use App\Models\ContentAggregation;
use App\Models\User;
use App\Services\ParaphrasingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParaphraseAggregationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $aggregationId;
    protected string $language;

    public $timeout = 120; // 2 minutes (Claude might take time)
    public $tries = 3;
    public $backoff = [60, 180, 300]; // 1 min, 3 min, 5 min
    public $maxExceptions = 1;

    public function __construct(int $aggregationId, string $language = 'en')
    {
        $this->aggregationId = $aggregationId;
        $this->language = $language;
    }

    public function handle(): void
    {
        Log::info("Starting ParaphraseAggregationJob", [
            'aggregation_id' => $this->aggregationId,
            'language' => $this->language,
        ]);

        try {
            $aggregation = ContentAggregation::find($this->aggregationId);

            if (!$aggregation) {
                Log::warning("Aggregation not found", ['aggregation_id' => $this->aggregationId]);
                return;
            }

            // Check if already processed
            if ($aggregation->hasBeenCurated()) {
                Log::info("Aggregation already curated", ['aggregation_id' => $this->aggregationId]);
                return;
            }

            // Get or create curator user
            $author = User::where('email', 'curator@system.local')->first()
                ?? User::firstOrCreate(
                    ['email' => 'curator@system.local'],
                    [
                        'name' => 'Content Curator',
                        'password' => bcrypt(\Illuminate\Support\Str::random(32)),
                        'email_verified_at' => now(),
                    ]
                );

            $paraphrase = new ParaphrasingService();
            $post = $paraphrase->paraphraseAggregation(
                $aggregation,
                $this->language,
                $author
            );

            if ($post) {
                Log::info("Paraphrasing completed successfully", [
                    'aggregation_id' => $this->aggregationId,
                    'post_id' => $post->id,
                    'confidence_score' => $post->paraphrase_confidence_score,
                ]);
            } else {
                Log::warning("Paraphrasing returned null", [
                    'aggregation_id' => $this->aggregationId,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("ParaphraseAggregationJob failed", [
                'aggregation_id' => $this->aggregationId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ParaphraseAggregationJob permanently failed", [
            'aggregation_id' => $this->aggregationId,
            'error' => $exception->getMessage(),
        ]);
    }
}
