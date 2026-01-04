<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $postId;

    public $timeout = 60; // 1 minute
    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function handle(): void
    {
        Log::info("Starting SendReviewNotificationJob", [
            'post_id' => $this->postId,
        ]);

        try {
            $post = Post::find($this->postId);

            if (!$post) {
                Log::warning("Post not found", ['post_id' => $this->postId]);
                return;
            }

            // Get admins (users with admin role)
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'super-admin']);
            })->get();

            if ($admins->isEmpty()) {
                Log::warning("No admins found for notification");
                return;
            }

            $count = 0;

            foreach ($admins as $admin) {
                try {
                    // Create notification in database
                    $admin->notifications()->create([
                        'type' => 'App\\Notifications\\PostPendingReview',
                        'notifiable_type' => User::class,
                        'notifiable_id' => $admin->id,
                        'data' => [
                            'post_id' => $post->id,
                            'post_title' => $post->title,
                            'confidence_score' => $post->paraphrase_confidence_score,
                            'source_count' => count($post->source_ids ?? []),
                            'url' => route('admin.posts.edit', $post->id),
                        ],
                    ]);

                    $count++;

                    // Optionally send email
                    // Mail::to($admin->email)->queue(new \App\Mail\PostPendingReviewMail($post));

                } catch (\Exception $e) {
                    Log::error("Failed to notify admin", [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info("Review notifications sent", [
                'post_id' => $this->postId,
                'admins_notified' => $count,
            ]);

        } catch (\Exception $e) {
            Log::error("SendReviewNotificationJob failed", [
                'post_id' => $this->postId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendReviewNotificationJob permanently failed", [
            'post_id' => $this->postId,
            'error' => $exception->getMessage(),
        ]);
    }
}
