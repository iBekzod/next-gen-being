<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FixTruncatedPosts extends Command
{
    protected $signature = 'posts:fix-truncated
                            {--dry-run : Just list truncated posts without fixing}
                            {--limit=10 : Maximum posts to fix in one run}
                            {--id= : Fix a specific post by ID}';

    protected $description = 'Detect and complete posts whose content was truncated by the AI generator';

    public function handle(): int
    {
        if ($postId = $this->option('id')) {
            $posts = Post::where('id', $postId)->get();
        } else {
            $posts = Post::where('status', 'published')->orderByDesc('id')->get();
        }

        $truncated = $posts->filter(fn($p) => $this->isTruncated($p->content));

        $this->info("Scanned {$posts->count()} posts. Found {$truncated->count()} truncated.");

        if ($this->option('dry-run')) {
            foreach ($truncated as $post) {
                $tail = mb_substr(strip_tags($post->content), -120);
                $this->line("  #{$post->id}: {$post->title}");
                $this->line("     ends: ...{$tail}");
            }
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $fixed = 0;
        foreach ($truncated->take($limit) as $post) {
            $this->info("Fixing #{$post->id}: {$post->title}");
            try {
                $continuation = $this->generateContinuation($post);
                if (empty(trim($continuation))) {
                    $this->warn("  No continuation produced, skipping.");
                    continue;
                }
                $post->content = rtrim($post->content) . "\n\n" . trim($continuation);
                $post->save();
                $words = str_word_count(strip_tags($post->content));
                $contWords = str_word_count(strip_tags($continuation));
                $this->info("  Done. Added {$contWords} words. New total: {$words}");
                $fixed++;
                sleep(2);
            } catch (\Throwable $e) {
                $this->error("  Failed: {$e->getMessage()}");
                Log::error('FixTruncatedPosts failed', ['post_id' => $post->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Fixed {$fixed} posts.");
        return self::SUCCESS;
    }

    private function isTruncated(?string $content): bool
    {
        if (empty($content)) return false;

        // 1. Check if content ends inside an unclosed code block
        // Find the LAST ``` and see if there's a matching closer after it
        $lastFence = mb_strrpos($content, '```');
        if ($lastFence !== false) {
            $afterFence = trim(mb_substr($content, $lastFence + 3));
            // Just a language identifier (e.g. "php" or empty) → opener with no closer
            if ($afterFence === '' || preg_match('/^[a-z0-9_+-]{1,15}$/i', $afterFence)) {
                return true;
            }
            // No closing fence in the rest of content
            if (!str_contains($afterFence, '```')) {
                $linesAfter = explode("\n", $afterFence);
                if (count($linesAfter) <= 8) {
                    return true;
                }
            }
        }

        // 2. Check the very end for proper terminator
        $plain = trim(strip_tags($content));
        if ($plain === '') return false;

        $lastChar = mb_substr($plain, -1);
        $validEnders = ['.', '!', '?', '"', "'", ')', ']', '}', '`', '*', '_', ':'];
        if (!in_array($lastChar, $validEnders)) {
            return true;
        }

        // 3. Last line is a long list item without sentence terminator
        $lastNewline = mb_strrpos($plain, "\n");
        $lastLine = trim($lastNewline !== false ? mb_substr($plain, $lastNewline + 1) : $plain);
        if ((str_starts_with($lastLine, '-') || str_starts_with($lastLine, '*')) && mb_strlen($lastLine) > 30) {
            if (!in_array($lastChar, ['.', '!', '?'])) {
                return true;
            }
        }

        return false;
    }

    private function generateContinuation(Post $post): string
    {
        $apiKey = config('services.anthropic.key');
        if (!$apiKey) {
            throw new \Exception('ANTHROPIC_API_KEY not set');
        }

        $existing = $post->content;
        $title = $post->title;

        $system = "You are completing a technical blog article that was cut off mid-sentence or mid-code-block. Continue exactly from where it stopped. Match the existing voice, depth, and markdown style. If the article ended inside a code block, finish the code block first. Add a proper conclusion section with key takeaways at the end. Do NOT repeat any existing content. Do NOT add any introduction. Just continue and finish the article.";

        $user = "ARTICLE TITLE: {$title}\n\nEXISTING CONTENT (was cut off):\n---\n{$existing}\n---\n\nContinue this article from where it ended. Finish any incomplete sentence or code block, complete any unfinished section, then add a proper Conclusion section with key takeaways. Output ONLY the continuation in markdown - no JSON, no metadata, no delimiters.";

        $response = Http::timeout(300)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-5',
                'max_tokens' => 12000,
                'temperature' => 0.7,
                'system' => $system,
                'messages' => [['role' => 'user', 'content' => $user]],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Anthropic API error: ' . $response->body());
        }

        return $response->json()['content'][0]['text'] ?? '';
    }
}
