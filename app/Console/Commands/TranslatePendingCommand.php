<?php

namespace App\Console\Commands;

use App\Jobs\TranslatePostJob;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TranslatePendingCommand extends Command
{
    protected $signature = 'content:translate-pending {--limit=20 : Max posts to translate} {--languages=es,fr,de : Languages to translate to}';
    protected $description = 'Translate curated posts to multiple languages';

    public function handle()
    {
        $this->info('🌍 Translating pending posts...');
        Log::info('TranslatePendingCommand started');

        try {
            $limit = (int) $this->option('limit');
            $languagesStr = $this->option('languages');
            $languages = array_map('trim', explode(',', $languagesStr));

            // Get curated posts that are base posts (English, not translations)
            $pending = Post::where('is_curated', true)
                ->where('base_language', 'en')
                ->whereNull('base_post_id') // Only base posts
                ->where('status', 'published')
                ->latest()
                ->limit($limit)
                ->get();

            if ($pending->isEmpty()) {
                $this->info('No pending posts to translate');
                return 0;
            }

            $this->info("Found {$pending->count()} posts to translate to: " . implode(', ', $languages));

            $count = 0;
            foreach ($pending as $post) {
                try {
                    // Check which languages need translation
                    $needsTranslation = array_filter($languages, function ($lang) use ($post) {
                        return !$post->translatedVersions()
                            ->where('base_language', $lang)
                            ->exists();
                    });

                    if (empty($needsTranslation)) {
                        continue;
                    }

                    $this->line("  ✓ {$post->title} → " . implode(', ', $needsTranslation));

                    // Queue translation
                    TranslatePostJob::dispatch($post->id, $needsTranslation)
                        ->onQueue('default');

                    $count++;

                } catch (\Exception $e) {
                    $this->error("  ✗ Failed: {$e->getMessage()}");
                    Log::error("Translation job dispatch failed", [
                        'post_id' => $post->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->info("\n✓ Queued {$count} posts for translation");
            Log::info('TranslatePendingCommand completed', [
                'queued' => $count,
                'languages' => $languages,
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('TranslatePendingCommand failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
}
