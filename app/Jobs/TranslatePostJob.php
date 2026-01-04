<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslatePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $postId;
    protected array $targetLanguages;

    public $timeout = 180; // 3 minutes (multiple translations)
    public $tries = 2;
    public $backoff = [60, 300]; // 1 min, 5 min

    public function __construct(int $postId, array $targetLanguages = ['es', 'fr', 'de'])
    {
        $this->postId = $postId;
        $this->targetLanguages = $targetLanguages;
    }

    public function handle(): void
    {
        Log::info("Starting TranslatePostJob", [
            'post_id' => $this->postId,
            'target_languages' => $this->targetLanguages,
        ]);

        try {
            $post = Post::find($this->postId);

            if (!$post) {
                Log::warning("Post not found", ['post_id' => $this->postId]);
                return;
            }

            if (!$post->is_curated) {
                Log::info("Post is not curated, skipping translation", ['post_id' => $this->postId]);
                return;
            }

            $translator = new TranslationService();

            // Get languages that don't exist yet
            $languagesToCreate = array_filter($this->targetLanguages, function ($lang) use ($post) {
                return !$post->translatedVersions()
                    ->where('base_language', $lang)
                    ->exists();
            });

            if (empty($languagesToCreate)) {
                Log::info("All translations already exist", ['post_id' => $this->postId]);
                return;
            }

            $translations = $translator->translatePost($post, $languagesToCreate);

            Log::info("Translation completed successfully", [
                'post_id' => $this->postId,
                'translations_created' => count($translations),
                'languages' => $languagesToCreate,
            ]);

        } catch (\Exception $e) {
            Log::error("TranslatePostJob failed", [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("TranslatePostJob permanently failed", [
            'post_id' => $this->postId,
            'error' => $exception->getMessage(),
        ]);
    }
}
