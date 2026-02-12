<?php

namespace App\Console\Commands;

use App\Jobs\GenerateDeepResearchPostJob;
use Illuminate\Console\Command;

class GenerateDeepResearchPostBackground extends Command
{
    protected $signature = 'content:generate-deep-research-bg
                            {--count=1 : Number of posts to queue}
                            {--topic= : Specific topic}
                            {--author=1 : Author user ID}
                            {--publish : Publish immediately}
                            {--category= : Specific category}
                            {--no-tags : Don\'t generate tags}
                            {--delay=0 : Seconds to delay before processing}';

    protected $description = 'Queue deep research posts for background generation (non-blocking)';

    public function handle(): int
    {
        $this->info("\n");
        $this->info("╔════════════════════════════════════════════════════════════════════╗");
        $this->info("║   📚 Deep Research Blog Post Background Queue                      ║");
        $this->info("║           (Non-blocking, queued for background processing)          ║");
        $this->info("╚════════════════════════════════════════════════════════════════════╝");
        $this->newLine();

        $count = (int) $this->option('count');
        $topic = $this->option('topic');
        $authorId = (int) $this->option('author');
        $publish = $this->option('publish');
        $category = $this->option('category');
        $generateTags = !$this->option('no-tags');
        $delay = (int) $this->option('delay');

        if ($count < 1) {
            $this->error('Count must be at least 1');
            return self::FAILURE;
        }

        $this->info("⏳ Queueing {$count} post(s) for background generation...\n");

        for ($i = 1; $i <= $count; $i++) {
            try {
                // Dispatch to queue
                if ($delay > 0) {
                    GenerateDeepResearchPostJob::dispatch(
                        $topic, $authorId, $publish, $category, $generateTags
                    )->delay(now()->addSeconds($delay))->onQueue('content');

                    $this->info("  {$i}. ✅ Queued (will start in {$delay}s)");
                } else {
                    GenerateDeepResearchPostJob::dispatch(
                        $topic, $authorId, $publish, $category, $generateTags
                    )->onQueue('content');

                    $this->info("  {$i}. ✅ Queued");
                }

            } catch (\Exception $e) {
                $this->error("  {$i}. ❌ Failed to queue: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("✅ {$count} job(s) queued successfully!");
        $this->newLine();

        $this->info("📋 Queue Status:");
        $this->info("   • Queue name: content");
        $this->info("   • Status: Waiting for worker");
        $this->newLine();

        $this->info("🚀 Start queue worker with one of these commands:");
        $this->line("   php artisan queue:work redis --queue=content");
        $this->line("   php artisan queue:work redis --queue=content,default,video");
        $this->line("   php artisan queue:listen redis --queue=content");
        $this->newLine();

        $this->info("📊 Monitor queue with:");
        $this->line("   php artisan queue:failed           (view failed jobs)");
        $this->line("   redis-cli LLEN queues:content      (check queue size)");
        $this->line("   tail -f storage/logs/laravel.log   (view logs)");
        $this->newLine();

        $this->info("💡 Tips:");
        $this->info("   • Each post takes 3-5 minutes to generate");
        $this->info("   • Posts are created as drafts by default (use --publish to auto-publish)");
        $this->info("   • Check admin panel at /admin/posts to see generated posts");
        $this->info("   • Failed jobs are retried up to 3 times with delays");
        $this->newLine();

        return self::SUCCESS;
    }
}
