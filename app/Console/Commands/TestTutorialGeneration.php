<?php

namespace App\Console\Commands;

use App\Services\AITutorialGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestTutorialGeneration extends Command
{
    protected $signature = 'tutorials:test {topic?} {--parts=3}';
    protected $description = 'Test tutorial generation with a quick example';

    public function handle(): int
    {
        $this->info("\n🧪 Testing Tutorial Generation System\n");
        $this->info(str_repeat("=", 60) . "\n");

        $topic = $this->argument('topic') ?? 'Building a Simple REST API with Laravel 12 and Docker';
        $parts = (int) $this->option('parts');

        if (!in_array($parts, [3, 5, 8])) {
            $this->error("Parts must be 3, 5, or 8");
            return Command::FAILURE;
        }

        $this->info("Configuration:");
        $this->line("  Topic: {$topic}");
        $this->line("  Parts: {$parts}");
        $this->line("  Status: DRAFT (for review)\n");

        $this->info("Starting generation...\n");

        try {
            $service = new AITutorialGenerationService();

            // Track time
            $startTime = microtime(true);

            $posts = $service->generateComprehensiveSeries(
                topic: $topic,
                parts: $parts,
                publish: false
            );

            $duration = microtime(true) - $startTime;

            $this->info("\n" . str_repeat("=", 60));
            $this->info("✅ Generation Successful!\n");

            $this->line("Generated {$parts} parts in " . number_format($duration, 2) . " seconds\n");

            $this->info("Posts Created:");
            foreach ($posts as $index => $post) {
                $this->line("  [" . ($index + 1) . "] {$post->title}");
                $this->line("       ID: {$post->id} | Slug: {$post->slug}");
                $this->line("       Words: " . str_word_count($post->content) . " | Status: {$post->status}");
            }

            $this->info("\nNext Steps:");
            $this->line("  1. Review content: http://localhost/admin/posts");
            $this->line("  2. Filter by series: '{$posts[0]->series_title}'");
            $this->line("  3. Edit if needed, then publish");
            $this->line("  4. Check status: curl http://localhost/api/v1/tutorials/status\n");

            $this->info(str_repeat("=", 60) . "\n");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("\n❌ Generation Failed!\n");

            $this->line("Error: " . $e->getMessage());

            $this->info("\nTroubleshooting:");
            $this->line("  1. Verify API key: grep ANTHROPIC_API_KEY .env");
            $this->line("  2. Check logs: tail -f storage/logs/laravel.log");
            $this->line("  3. Test setup: php artisan tutorials:verify");

            Log::error('Test generation failed', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
