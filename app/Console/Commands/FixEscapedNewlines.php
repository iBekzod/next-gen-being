<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class FixEscapedNewlines extends Command
{
    protected $signature = 'posts:fix-newlines {--dry-run : Preview changes without applying them}';
    protected $description = 'Fix double-escaped newlines in post content (\\n → actual newlines)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be saved');
            $this->newLine();
        }

        // Find posts with escaped newlines in content
        $posts = Post::whereRaw("content LIKE '%\\\\n%'")
            ->orWhereRaw("excerpt LIKE '%\\\\n%'")
            ->get();

        if ($posts->isEmpty()) {
            $this->info('✅ No posts found with escaped newlines!');
            return self::SUCCESS;
        }

        $this->info("Found {$posts->count()} posts with escaped newlines");
        $this->newLine();

        $fixedCount = 0;

        foreach ($posts as $post) {
            $hasChanges = false;
            $originalContent = $post->content;
            $originalExcerpt = $post->excerpt;

            // Fix content
            if (str_contains($post->content, '\\n')) {
                $post->content = $this->fixEscapedNewlines($post->content);
                $hasChanges = true;
            }

            // Fix excerpt
            if (str_contains($post->excerpt, '\\n')) {
                $post->excerpt = $this->fixEscapedNewlines($post->excerpt);
                $hasChanges = true;
            }

            if ($hasChanges) {
                if ($dryRun) {
                    $this->warn("Would fix: {$post->title}");
                    $this->line("  Preview (first 100 chars):");
                    $this->line("  Before: " . substr($originalContent, 0, 100));
                    $this->line("  After:  " . substr($post->content, 0, 100));
                    $this->newLine();
                } else {
                    $post->save();
                    $this->info("✅ Fixed: {$post->title}");
                    $fixedCount++;
                }
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("📋 Would fix {$posts->count()} posts");
            $this->info("💡 Run without --dry-run to apply changes");
        } else {
            $this->info("✅ Fixed {$fixedCount} posts!");
        }

        return self::SUCCESS;
    }

    private function fixEscapedNewlines(string $text): string
    {
        // Replace escaped newlines with actual newlines
        // Handle various escape patterns
        $fixed = $text;

        // Pattern 1: \\n (double backslash + n)
        $fixed = str_replace('\\n', "\n", $fixed);

        // Pattern 2: \\r\\n (Windows style)
        $fixed = str_replace('\\r\\n', "\n", $fixed);

        // Pattern 3: \\r (Mac style)
        $fixed = str_replace('\\r', "\n", $fixed);

        // Pattern 4: \\t (tabs)
        $fixed = str_replace('\\t', "\t", $fixed);

        // Pattern 5: \" (escaped quotes)
        $fixed = str_replace('\\"', '"', $fixed);

        // Pattern 6: \\\\ (escaped backslashes)
        $fixed = str_replace('\\\\', '\\', $fixed);

        return $fixed;
    }
}
