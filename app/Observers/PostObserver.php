<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\Webhook\WebhookService;

class PostObserver
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $this->webhookService->trigger('post.published', [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
            'user_id' => $post->user_id,
            'created_at' => $post->created_at,
        ], $post->user);
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // Only trigger webhook if content has changed significantly
        $significant_changes = [
            'title',
            'content',
            'excerpt',
            'status',
            'featured_image',
        ];

        $changed = $post->isDirty($significant_changes);

        if ($changed) {
            $this->webhookService->trigger('post.updated', [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'user_id' => $post->user_id,
                'changed_fields' => $post->getChanges(),
                'updated_at' => $post->updated_at,
            ], $post->user);
        }
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $this->webhookService->trigger('post.deleted', [
            'id' => $post->id,
            'title' => $post->title,
            'user_id' => $post->user_id,
            'deleted_at' => now(),
        ], $post->user);
    }
}
