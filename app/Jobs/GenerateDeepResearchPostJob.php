<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\DeepResearchContentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateDeepResearchPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 600; // 10 minutes

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1 min, 5 min, 15 min
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $topic = null,
        public int $authorId = 1,
        public bool $publish = false,
        public ?string $category = null,
        public bool $generateTags = true,
    ) {
        $this->onQueue('content');
    }

    /**
     * Execute the job.
     */
    public function handle(DeepResearchContentService $contentService): void
    {
        Log::info('Starting deep research post generation job', [
            'topic' => $this->topic,
            'author_id' => $this->authorId,
            'publish' => $this->publish,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Step 1: Generate content
            Log::info('Generating deep research content...');
            $postData = $contentService->generateDeepResearchPost($this->topic);

            // Step 2: Get or create category
            $categoryName = $this->category ?? $postData['category'];
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['slug' => \Illuminate\Support\Str::slug($categoryName)]
            );

            // Step 3: Get author
            $author = \App\Models\User::findOrFail($this->authorId);

            // Step 4: Create post
            Log::info('Creating post in database...');
            $post = Post::create([
                'title' => $postData['title'],
                'slug' => $postData['slug'],
                'excerpt' => $postData['excerpt'],
                'content' => $postData['content'],
                'featured_image' => $postData['featured_image'],
                'read_time' => $postData['read_time'],
                'category_id' => $category->id,
                'author_id' => $author->id,
                'status' => $this->publish ? 'published' : 'draft',
                'published_at' => $this->publish ? now() : null,
            ]);

            // Step 5: Attach tags
            if ($this->generateTags && !empty($postData['tags'])) {
                Log::info('Attaching tags...', ['count' => count($postData['tags'])]);
                foreach ($postData['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => \Illuminate\Support\Str::slug($tagName)]
                    );
                    $post->tags()->attach($tag);
                }
            }

            Log::info('Deep research post generated successfully', [
                'post_id' => $post->id,
                'title' => $post->title,
                'word_count' => $postData['word_count'],
                'read_time' => $post->read_time,
                'status' => $post->status,
            ]);

            // Optional: Notify user
            $this->notifyCompletion($post);

        } catch (Exception $e) {
            Log::error('Deep research post generation failed', [
                'topic' => $this->topic,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Deep research post generation job failed permanently', [
            'topic' => $this->topic,
            'author_id' => $this->authorId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);

        // Optional: Send notification to admin
        // NotifyAdminOfFailedPost::dispatch($this->topic, $exception->getMessage());
    }

    /**
     * Notify about completion
     */
    private function notifyCompletion(Post $post): void
    {
        // You can implement notifications here:
        // - Email to admin
        // - Slack notification
        // - Database notification
        // - WebSocket broadcast

        Log::info('Post generation completed and ready for review', [
            'post_id' => $post->id,
            'post_url' => "/admin/posts/{$post->id}",
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'content-generation',
            'deep-research',
            'blog-post',
            'topic:' . ($this->topic ?? 'random'),
        ];
    }
}
