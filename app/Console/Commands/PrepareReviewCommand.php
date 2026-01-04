<?php

namespace App\Console\Commands;

use App\Jobs\SendReviewNotificationJob;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PrepareReviewCommand extends Command
{
    protected $signature = 'content:prepare-review {--limit=50 : Max posts to notify}';
    protected $description = 'Notify admins of posts pending review';

    public function handle()
    {
        $this->info('📬 Preparing review notifications...');
        Log::info('PrepareReviewCommand started');

        try {
            $limit = (int) $this->option('limit');

            // Get curated posts in draft status that haven't been reviewed yet
            $pending = Post::where('is_curated', true)
                ->where('status', 'draft')
                ->where('moderation_status', '!=', 'pending') // Not already pending
                ->latest()
                ->limit($limit)
                ->get();

            if ($pending->isEmpty()) {
                $this->info('No posts pending review');
                return 0;
            }

            $this->info("Found {$pending->count()} posts for review");

            $count = 0;
            foreach ($pending as $post) {
                try {
                    $this->line("  ✓ {$post->title}");

                    // Queue notification
                    SendReviewNotificationJob::dispatch($post->id)
                        ->onQueue('default');

                    // Mark as pending review
                    $post->update([
                        'moderation_status' => 'pending',
                        'moderation_notes' => 'Awaiting admin review from automated curation',
                    ]);

                    $count++;

                } catch (\Exception $e) {
                    $this->error("  ✗ Failed: {$e->getMessage()}");
                    Log::error("Notification job dispatch failed", [
                        'post_id' => $post->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("\n✓ Notified admins about {$count} posts");
            Log::info('PrepareReviewCommand completed', ['notified' => $count]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('PrepareReviewCommand failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
