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
     * Affiliate links configuration
     */
    private array $affiliateLinks = [
        'chatgpt' => [
            'patterns' => ['chatgpt', 'chatgpt pro', 'openai chatgpt'],
            'link' => 'https://openai.com/chatgpt?utm_source=nextgenbeing&utm_medium=blog&utm_campaign=affiliate',
            'anchor' => 'ChatGPT Plus',
        ],
        'midjourney' => [
            'patterns' => ['midjourney'],
            'link' => 'https://www.midjourney.com?utm_source=nextgenbeing&utm_medium=blog&utm_campaign=affiliate',
            'anchor' => 'Midjourney',
        ],
        'claude' => [
            'patterns' => ['claude', 'claude pro', 'anthropic claude'],
            'link' => 'https://claude.ai?utm_source=nextgenbeing&utm_medium=blog&utm_campaign=affiliate',
            'anchor' => 'Claude Pro',
        ],
        'perplexity' => [
            'patterns' => ['perplexity'],
            'link' => 'https://www.perplexity.ai?utm_source=nextgenbeing&utm_medium=blog&utm_campaign=affiliate',
            'anchor' => 'Perplexity AI',
        ],
        'zapier' => [
            'patterns' => ['zapier', 'zapier automation'],
            'link' => 'https://zapier.com?utm_source=nextgenbeing&utm_medium=blog&utm_campaign=affiliate',
            'anchor' => 'Zapier',
        ],
        'make' => [
            'patterns' => ['make.com', 'make automation', 'integromat'],
            'link' => 'https://make.com?utm_source=nextgenbeing&utm_medium=blog&utm_campaign=affiliate',
            'anchor' => 'Make.com',
        ],
        'airtable' => [
            'patterns' => ['airtable'],
            'link' => 'https://airtable.com?utm_source=nextgenbeing&utm_medium=blog&utm_campaign=affiliate',
            'anchor' => 'Airtable',
        ],
    ];

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        // Find recently published posts without affiliate links
        $posts = Post::where('status', 'published')
            ->where('created_at', '>', now()->subDays(7))
            ->whereRaw("content NOT LIKE '%utm_source=nextgenbeing%'")
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

        foreach ($this->affiliateLinks as $tool => $config) {
            foreach ($config['patterns'] as $pattern) {
                // Create case-insensitive regex pattern
                $regex = '/\b' . preg_quote($pattern, '/') . '\b/i';

                // Check if pattern exists and not already linked
                if (preg_match($regex, $updatedContent)) {
                    // Only add link to first mention of each tool
                    if (!preg_match('/' . preg_quote($config['link'], '/') . '/', $updatedContent)) {
                        // Replace first occurrence only
                        $replacement = "[{$config['anchor']}]({$config['link']})";
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
