<?php

namespace App\Jobs;

use App\Services\AITutorialGenerationService;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTutorialSeriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $topic;
    protected int $parts;
    protected bool $publish;
    protected int $userId;

    public $timeout = 300; // 5 minutes
    public $tries = 1;
    public $backoff = 60;

    public function __construct(
        string $topic,
        int $parts,
        bool $publish,
        int $userId,
    ) {
        $this->topic = $topic;
        $this->parts = $parts;
        $this->publish = $publish;
        $this->userId = $userId;
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        Log::info('Starting tutorial generation job', [
            'topic' => $this->topic,
            'parts' => $this->parts,
            'user_id' => $this->userId,
        ]);

        try {
            $service = new AITutorialGenerationService();

            // Generate series
            $posts = $service->generateComprehensiveSeries(
                topic: $this->topic,
                parts: $this->parts,
                publish: $this->publish,
            );

            // Notify user
            $user = User::find($this->userId);
            if ($user) {
                $this->notifyUser($user, $posts, success: true);
            }

            Log::info('Tutorial generation completed', [
                'topic' => $this->topic,
                'created_posts' => count($posts),
            ]);

        } catch (\Exception $e) {
            Log::error('Tutorial generation failed', [
                'topic' => $this->topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Notify user of failure
            $user = User::find($this->userId);
            if ($user) {
                $this->notifyUser($user, [], success: false, error: $e->getMessage());
            }

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Notify user of job completion
     */
    private function notifyUser(User $user, array $posts = [], bool $success = true, string $error = ''): void
    {
        if ($success) {
            $message = "Tutorial series '{$this->topic}' with {$this->parts} parts has been generated successfully! Created " . count($posts) . " posts.";
            $type = 'success';
        } else {
            $message = "Tutorial generation for '{$this->topic}' failed: {$error}";
            $type = 'error';
        }

        // Create notification
        $user->notifications()->create([
            'type' => 'tutorial_generation_' . $type,
            'title' => 'Tutorial Generation ' . ucfirst($type),
            'message' => $message,
            'data' => [
                'topic' => $this->topic,
                'posts_count' => count($posts),
                'post_ids' => array_column($posts, 'id') ?? [],
            ],
        ]);

        // Optional: Send email notification
        // Mail::to($user)->send(new TutorialGenerationComplete($user, $this->topic, $posts));
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Tutorial generation job permanently failed', [
            'topic' => $this->topic,
            'error' => $exception->getMessage(),
        ]);
    }
}
