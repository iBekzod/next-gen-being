<?php

namespace App\Console\Commands;

use App\Services\AITutorialGenerationService;
use Illuminate\Console\Command;

class GenerateComprehensiveTutorial extends Command
{
    protected $signature = 'tutorial:generate
                          {topic : Tutorial topic/title}
                          {--parts=8 : Number of tutorial parts (3, 5, or 8)}
                          {--publish : Publish immediately}';

    protected $description = 'Generate comprehensive multi-part tutorial series using AI';

    public function handle()
    {
        $topic = $this->argument('topic');
        $parts = (int) $this->option('parts');
        $publish = $this->option('publish');

        // Validate parts
        if (!\in_array($parts, [3, 5, 8])) {
            $this->error('Parts must be 3, 5, or 8');
            return 1;
        }

        $this->info("🚀 Generating comprehensive {$parts}-part tutorial series");
        $this->info("Topic: {$topic}");
        $this->info("Status: " . ($publish ? 'Publishing' : 'Draft'));
        $this->newLine();

        try {
            $service = new AITutorialGenerationService();

            // Generate series
            $posts = $service->generateComprehensiveSeries(
                topic: $topic,
                parts: $parts,
                publish: $publish
            );

            $this->newLine();
            $this->info("✨ Tutorial series generated successfully!");
            $this->line("Created " . \count($posts) . " posts");

            foreach ($posts as $post) {
                $this->line("  ✓ {$post->title}");
                $this->line("    URL: {$post->slug}");
            }

        } catch (\Exception $e) {
            $this->error("Generation failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
