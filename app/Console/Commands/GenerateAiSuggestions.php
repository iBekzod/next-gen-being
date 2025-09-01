<?php

namespace App\Console\Commands;

use App\Services\AiContentService;
use Illuminate\Console\Command;

class GenerateAiSuggestions extends Command
{
    protected $signature = 'ai:generate-suggestions {--force : Force generation even if disabled}';
    protected $description = 'Generate AI content suggestions based on trending topics';

    public function handle(AiContentService $aiService): int
    {
        if (!$this->option('force') && !setting('enable_ai_suggestions', false)) {
            $this->info('AI suggestions are disabled. Use --force to generate anyway.');
            return Command::SUCCESS;
        }

        $this->info('Generating AI content suggestions...');

        try {
            $suggestions = $aiService->generateContentSuggestions();

            $this->info('Generated ' . count($suggestions) . ' AI content suggestions successfully!');

            foreach ($suggestions as $suggestion) {
                $this->line("- {$suggestion->title} (Score: {$suggestion->relevance_score})");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to generate AI suggestions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
