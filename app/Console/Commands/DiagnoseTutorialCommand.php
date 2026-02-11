<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class DiagnoseTutorialCommand extends Command
{
    protected $signature = 'tutorials:diagnose {--series-slug=} {--part=}';
    protected $description = 'Diagnose tutorial content generation issues';

    public function handle(): int
    {
        $this->info("\n🔍 Tutorial Diagnostic Tool\n");
        $this->info(str_repeat("=", 60));

        $seriesSlug = $this->option('series-slug');
        $part = $this->option('part');

        if (!$seriesSlug) {
            // Show latest series
            $latestPost = Post::whereNotNull('series_slug')
                ->orderByDesc('published_at')
                ->first();

            if (!$latestPost) {
                $this->error("No tutorial series found in database");
                return self::FAILURE;
            }

            $seriesSlug = $latestPost->series_slug;
            $this->info("Latest series: {$seriesSlug}\n");
        }

        // Get all parts in the series
        $posts = Post::where('series_slug', $seriesSlug)
            ->when($part, fn($q) => $q->where('series_part', $part))
            ->orderBy('series_part')
            ->get();

        if ($posts->isEmpty()) {
            $this->error("No posts found for series: {$seriesSlug}");
            return self::FAILURE;
        }

        $this->info("Series: {$seriesSlug} ({$posts->count()} parts)\n");
        $this->info(str_repeat("-", 60));

        foreach ($posts as $post) {
            $this->analyzePart($post);
        }

        return self::SUCCESS;
    }

    private function analyzePart(Post $post): void
    {
        $wordCount = str_word_count(strip_tags($post->content));
        $charCount = strlen($post->content);
        $headerCount = preg_match_all('/^#+\s+.+$/m', $post->content);
        $codeBlockCount = preg_match_all('/```[a-z]*\n/i', $post->content);

        $this->line("\n📄 Part {$post->series_part}: {$post->title}");
        $this->line("   ID: {$post->id}");
        $this->line("   Status: " . strtoupper($post->status));
        $this->line("   Premium: " . ($post->is_premium ? "Yes ({$post->premium_tier})" : "No"));

        $this->line("\n📊 Content Metrics:");
        $this->line("   Characters: " . number_format($charCount));
        $this->line("   Words: " . number_format($wordCount));
        $this->line("   Read Time: {$post->read_time} minutes");
        $this->line("   Headers: {$headerCount}");
        $this->line("   Code Blocks: {$codeBlockCount}");

        // Quality checks
        $this->line("\n✓ Quality Checks:");
        $checks = [
            'Has content' => $wordCount > 0,
            'Minimum length (1000 words)' => $wordCount >= 1000,
            'Good length (2500+ words)' => $wordCount >= 2500,
            'Has headers' => $headerCount >= 3,
            'Has code examples' => $codeBlockCount >= 1,
            'Not premium restricted' => !$post->is_premium || $post->premium_tier,
        ];

        foreach ($checks as $name => $passed) {
            $symbol = $passed ? '✅' : '❌';
            $this->line("   {$symbol} {$name}");
        }

        // Show content preview
        $this->line("\n📝 Content Preview (first 300 chars):");
        $preview = substr(strip_tags($post->content), 0, 300);
        $this->line("   " . wordwrap($preview, 70, "\n   "));

        // Show content ending
        $ending = substr(strip_tags($post->content), -300);
        $this->line("\n📝 Content Ending (last 300 chars):");
        $this->line("   " . wordwrap($ending, 70, "\n   "));

        // Check for markdown rendering
        $this->line("\n🔧 Markdown Analysis:");
        preg_match_all('/^(#+)\s+(.+)$/m', $post->content, $matches);
        if (!empty($matches[0])) {
            $this->line("   Found " . count($matches[0]) . " headings:");
            foreach (array_slice($matches[0], 0, 5) as $heading) {
                $this->line("   • " . substr($heading, 0, 60));
            }
            if (count($matches[0]) > 5) {
                $this->line("   • ... and " . (count($matches[0]) - 5) . " more");
            }
        }

        // If content seems short, provide recommendations
        if ($wordCount < 2500) {
            $this->warn("\n⚠️  Content Appears Incomplete:");
            $this->warn("   Word count ({$wordCount}) is below target (2500+)");
            $this->warn("   Recommendations:");
            $this->warn("   1. Check if AI generation was interrupted");
            $this->warn("   2. Check content enhancement logs");
            $this->warn("   3. Verify featured image fetch didn't fail");
            $this->warn("   4. Check if paywall is showing preview instead of full content");
        }

        $this->line("");
    }
}
