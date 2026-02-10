<?php

namespace App\Console\Commands;

use App\Services\ContentQualityService;
use Illuminate\Console\Command;

class EvaluateContentQualityCommand extends Command
{
    protected $signature = 'content:evaluate-quality {--dry-run} {--post-id=}';
    protected $description = 'Evaluate content quality and unpublish low-quality posts';

    public function handle(ContentQualityService $qualityService): int
    {
        if ($this->option('post-id')) {
            // Evaluate single post
            return $this->evaluateSinglePost($qualityService);
        }

        // Evaluate all posts
        $dryRun = $this->option('dry-run');

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('📊 AI Content Quality Manager');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No posts will be unpublished');
            $this->newLine();
        }

        $results = $qualityService->evaluateAllPosts($dryRun);

        // Display results
        $this->displayResults($results, $dryRun);

        return self::SUCCESS;
    }

    /**
     * Evaluate a single post
     */
    private function evaluateSinglePost(ContentQualityService $qualityService): int
    {
        $postId = $this->option('post-id');
        $post = \App\Models\Post::find($postId);

        if (!$post) {
            $this->error("Post #{$postId} not found");
            return self::FAILURE;
        }

        $report = $qualityService->getQualityReport($post);

        $this->info("Post Quality Report");
        $this->info('═══════════════════════════════════════════════════════');
        $this->line("Title: {$report['title']}");
        $this->line("Status: {$report['status']}");
        $this->line("Word Count: {$report['word_count']} words");
        $this->line("Headers: {$report['headers']}");
        $this->line("Code Blocks: {$report['code_blocks']}");
        $this->line("Series: " . ($report['has_series'] ? 'YES' : 'NO'));
        $this->line("Tags: {$report['has_tags']} tags");
        $this->newLine();

        // Color-code the score
        $score = $report['quality_score'];
        if ($score >= 70) {
            $this->info("Quality Score: ✅ {$score}/100 (EXCELLENT)");
        } elseif ($score >= 50) {
            $this->warn("Quality Score: ⚠️  {$score}/100 (FAIR)");
        } else {
            $this->error("Quality Score: ❌ {$score}/100 (POOR)");
        }

        $this->newLine();
        $this->info("Recommendation: {$report['recommendation']}");

        return self::SUCCESS;
    }

    /**
     * Display results table
     */
    private function displayResults(array $results, bool $dryRun): void
    {
        // Summary
        $this->info("Summary:");
        $this->info("├─ Total Evaluated: {$results['total_evaluated']}");
        $this->info("├─ High Quality: ✅ {$results['high_quality']}");
        $this->info("├─ Poor Quality: ❌ {$results['poor_quality']}");
        $this->info("└─ Unpublished: 🗑️  " . count($results['unpublished']));
        $this->newLine();

        // Unpublished posts
        if (!empty($results['unpublished'])) {
            $this->warn("Unpublished Posts (Low Quality):");
            foreach ($results['unpublished'] as $post) {
                $dryRunLabel = isset($post['dry_run']) ? ' [DRY RUN]' : '';
                $this->line("  ❌ [{$post['id']}] {$post['title']} (Score: {$post['score']}/100){$dryRunLabel}");
            }
            $this->newLine();
        }

        // Kept posts (random sample)
        if (!empty($results['kept'])) {
            $sample = array_slice($results['kept'], 0, 5);
            $this->info("Sample of Kept Posts (High Quality):");
            foreach ($sample as $post) {
                $this->line("  ✅ [{$post['id']}] {$post['title']} (Score: {$post['score']}/100)");
            }
            if (count($results['kept']) > 5) {
                $this->line("  ... and " . (count($results['kept']) - 5) . " more high-quality posts");
            }
            $this->newLine();
        }

        // Score distribution
        $this->displayScoreDistribution($results['scores']);

        // Final message
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        if ($dryRun) {
            $this->info("✅ Dry run complete. Run without --dry-run to unpublish.");
        } else {
            $this->info("✅ Quality evaluation complete! {$this->numToWord(count($results['unpublished']))} posts unpublished.");
        }
        $this->info('═══════════════════════════════════════════════════════');
    }

    /**
     * Display quality score distribution
     */
    private function displayScoreDistribution(array $scores): void
    {
        $excellent = count(array_filter($scores, fn($s) => $s['score'] >= 70));
        $good = count(array_filter($scores, fn($s) => $s['score'] >= 50 && $s['score'] < 70));
        $poor = count(array_filter($scores, fn($s) => $s['score'] < 50));

        $this->info("Quality Distribution:");
        $this->info("├─ 🟢 Excellent (70-100): {$excellent} posts");
        $this->info("├─ 🟡 Good (50-69): {$good} posts");
        $this->info("└─ 🔴 Poor (0-49): {$poor} posts");
    }

    /**
     * Convert number to word (1 = one, 2 = two, etc)
     */
    private function numToWord(int $num): string
    {
        $words = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        return $num < 10 ? $words[$num] : $num;
    }
}
