<?php

namespace App\Console\Commands;

use App\Services\SourceWhitelistService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InitializeSourcesCommand extends Command
{
    protected $signature = 'content:init-sources {--validate : Validate sources after creation}';
    protected $description = 'Initialize default content sources';

    public function handle()
    {
        $this->info('📚 Initializing default sources...');
        Log::info('InitializeSourcesCommand started');

        try {
            $service = new SourceWhitelistService();

            // Initialize defaults
            $count = $service->initializeDefaultSources();

            $this->info("✓ Initialized {$count} sources");

            // Show sources
            $stats = $service->getStatistics();
            $this->info("\n📊 Source Statistics:");
            $this->line("  Total sources: {$stats['total_sources']}");
            $this->line("  Active sources: {$stats['active_sources']}");
            $this->line("  Avg trust level: {$stats['avg_trust_level']}%");
            $this->line("  Total articles collected: {$stats['total_articles_collected']}");

            if ($this->option('validate')) {
                $this->info("\n🔍 Validating sources...");
                $sources = \App\Models\ContentSource::active()->get();

                foreach ($sources as $source) {
                    $this->line("  Validating {$source->name}...");
                    try {
                        $result = $service->validateNewSource($source);
                        if ($result['valid']) {
                            $this->line("    ✓ Valid");
                        } else {
                            $this->warn("    ⚠ " . implode(', ', $result['issues']));
                        }
                    } catch (\Exception $e) {
                        $this->error("    ✗ {$e->getMessage()}");
                    }
                }
            }

            Log::info('InitializeSourcesCommand completed', ['sources_created' => $count]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('InitializeSourcesCommand failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
