<?php

namespace App\Jobs;

use App\Models\ContentAggregation;
use App\Models\Post;
use App\Services\ReferenceTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractReferencesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $postId;
    protected int $aggregationId;

    public $timeout = 60; // 1 minute
    public $tries = 3;
    public $backoff = [30, 60, 120]; // 30 sec, 1 min, 2 min

    public function __construct(int $postId, int $aggregationId)
    {
        $this->postId = $postId;
        $this->aggregationId = $aggregationId;
    }

    public function handle(): void
    {
        Log::info("Starting ExtractReferencesJob", [
            'post_id' => $this->postId,
            'aggregation_id' => $this->aggregationId,
        ]);

        try {
            $post = Post::find($this->postId);
            $aggregation = ContentAggregation::find($this->aggregationId);

            if (!$post || !$aggregation) {
                Log::warning("Post or aggregation not found", [
                    'post_id' => $this->postId,
                    'aggregation_id' => $this->aggregationId,
                ]);
                return;
            }

            // Check if references already exist
            if ($post->sourceReferences()->exists()) {
                Log::info("References already exist for this post", ['post_id' => $this->postId]);
                return;
            }

            $referenceService = new ReferenceTrackingService();
            $count = $referenceService->extractReferencesFromAggregation($aggregation, $post);

            Log::info("Reference extraction completed successfully", [
                'post_id' => $this->postId,
                'references_created' => $count,
            ]);

        } catch (\Exception $e) {
            Log::error("ExtractReferencesJob failed", [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ExtractReferencesJob permanently failed", [
            'post_id' => $this->postId,
            'error' => $exception->getMessage(),
        ]);
    }
}
