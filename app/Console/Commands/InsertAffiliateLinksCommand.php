<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InsertAffiliateLinksCommand extends Command
{
    protected $signature = 'revenue:insert-affiliate-links {--limit=5}';
    protected $description = 'Automatically insert tracked affiliate links into tutorials mentioning AI tools';

    /**
     * Active affiliate links from config/affiliate.php, excluding any tool whose
     * referral URL hasn't been set yet — so we never insert a non-earning link.
     */
    private function activeLinks(): array
    {
        return array_filter(
            config('affiliate.links', []),
            fn ($c) => !empty($c['url'])
        );
    }

    public function handle(): int
    {
        if (empty($this->activeLinks())) {
            $this->warn('No affiliate programs configured yet. Set the AFFILIATE_*_URL values in .env first.');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        // Recent published posts — the per-link dedup below keeps this idempotent,
        // so a post is only ever touched for tools it mentions but isn't linked to.
        $posts = Post::where('status', 'published')
            ->where('created_at', '>', now()->subDays(7))
            ->limit($limit)
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No posts to update with affiliate links');
            return self::SUCCESS;
        }

        $updatedCount = 0;

        foreach ($posts as $post) {
            if ($this->insertAffiliateLinks($post)) {
                $updatedCount++;
                $this->info("✅ Updated: {$post->title}");
            }
        }

        $this->info("📊 Affiliate links inserted into {$updatedCount} posts");
        return self::SUCCESS;
    }

    /**
     * Insert affiliate links into post content
     */
    private function insertAffiliateLinks(Post $post): bool
    {
        $originalContent = $post->content;
        $updatedContent = $originalContent;
        $linksAdded = 0;

        foreach ($this->activeLinks() as $tool => $config) {
            foreach ($config['patterns'] as $pattern) {
                // Create case-insensitive regex pattern
                $regex = '/\b' . preg_quote($pattern, '/') . '\b/i';

                // Check if pattern exists and not already linked
                if (preg_match($regex, $updatedContent)) {
                    // Only add link to first mention of each tool
                    if (!str_contains($updatedContent, $config['url'])) {
                        // Replace first occurrence only
                        $replacement = "[{$config['anchor']}]({$config['url']})";
                        $updatedContent = preg_replace($regex, $replacement, $updatedContent, 1);
                        $linksAdded++;
                        break; // Move to next tool
                    }
                }
            }
        }

        // Only save if changes were made
        if ($linksAdded > 0 && $updatedContent !== $originalContent) {
            $post->update(['content' => $updatedContent]);
            return true;
        }

        return false;
    }
}
