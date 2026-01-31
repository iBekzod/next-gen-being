<?php

namespace App\Console\Commands;

use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateSampleDigitalProductsCommand extends Command
{
    protected $signature = 'ai-learning:create-samples {--count=10 : Number of sample products to create}';
    protected $description = 'Create sample digital products for testing and demonstration';

    public function handle(): int
    {
        $count = (int)$this->option('count');
        $user = User::first();

        if (!$user) {
            $this->error('❌ No users found. Please create a user first.');
            return self::FAILURE;
        }

        $samples = [
            [
                'title' => 'ChatGPT Prompt Engineering Guide',
                'type' => 'prompt',
                'price' => 4.99,
                'category' => 'prompt-engineering',
                'description' => 'A comprehensive guide with 50+ proven prompt templates for ChatGPT. Includes beginner-friendly prompts and advanced techniques for developers and content creators.',
                'short_description' => 'A comprehensive guide with 50+ proven prompt templates for ChatGPT.',
                'tags' => ['chatgpt', 'prompt-engineering', 'templates'],
                'features' => ['50+ Ready-to-use prompts', 'Categorized by use case', 'Best practices guide', 'Examples included'],
                'includes' => ['PDF guide', '50 prompt templates', 'Quick reference card'],
            ],
            [
                'title' => 'Claude AI Advanced Usage Tutorial',
                'type' => 'tutorial',
                'price' => 19.99,
                'category' => 'claude',
                'description' => 'Master Claude AI with this 8-part tutorial series. Learn prompt optimization, system instructions, API integration, and advanced use cases.',
                'short_description' => 'Master Claude AI with advanced techniques and real-world applications.',
                'tags' => ['claude', 'tutorial', 'advanced', 'api-integration'],
                'features' => ['8-part video tutorial', 'Code examples', 'API integration guide', 'Best practices'],
                'includes' => ['Video tutorials', 'Code samples', 'API documentation', 'Cheat sheet'],
            ],
            [
                'title' => 'Midjourney Creative Prompts Collection',
                'type' => 'prompt',
                'price' => 9.99,
                'is_free' => false,
                'category' => 'midjourney',
                'description' => 'Collection of 200+ creative prompts specifically designed for Midjourney. Covers photography, art, design, and conceptual imagery.',
                'short_description' => '200+ creative prompts designed for stunning Midjourney images.',
                'tags' => ['midjourney', 'prompts', 'image-generation', 'creative'],
                'features' => ['200+ prompts', 'Categorized styles', 'Examples included', 'Regular updates'],
                'includes' => ['Prompt library (TXT)', 'Style guide', 'Examples gallery'],
            ],
            [
                'title' => 'AI Automation Workflow Templates',
                'type' => 'template',
                'price' => 14.99,
                'category' => 'ai-automation',
                'description' => 'Ready-to-use automation templates for Make.com and Zapier. Automate content generation, email marketing, social media, and more.',
                'short_description' => 'Ready-to-use automation templates for Make.com and Zapier.',
                'tags' => ['ai-automation', 'templates', 'workflow', 'integration'],
                'features' => ['10+ templates', 'Make.com workflows', 'Zapier integration', 'Setup guides'],
                'includes' => ['10 workflow templates', 'Setup guide', 'Troubleshooting tips'],
            ],
            [
                'title' => 'Content Creation with AI - Complete Course',
                'type' => 'course',
                'price' => 49.99,
                'category' => 'content-creation',
                'description' => 'Complete course on using AI to create high-quality content. Covers blog posts, social media, emails, videos, and more. 15 modules with practical exercises.',
                'short_description' => 'Learn to create professional content using AI tools.',
                'tags' => ['course', 'content-creation', 'ai', 'business'],
                'features' => ['15 modules', 'Video lessons', 'Practical exercises', 'Lifetime access'],
                'includes' => ['Course videos', 'Worksheets', 'Templates', 'Resource list'],
            ],
            [
                'title' => 'AI for Marketing Professionals',
                'type' => 'cheatsheet',
                'price' => 2.99,
                'is_free' => false,
                'category' => 'ai-for-business',
                'description' => 'Quick reference guide for marketing professionals on using AI tools. Covers copywriting, SEO, email marketing, social media optimization.',
                'short_description' => 'Quick reference guide for AI marketing tools and techniques.',
                'tags' => ['marketing', 'ai', 'business', 'cheatsheet'],
                'features' => ['One-page reference', 'Tool comparisons', 'Use cases', 'Quick tips'],
                'includes' => ['PDF cheatsheet', 'Tool links'],
            ],
            [
                'title' => 'Python AI Project Starter Kit',
                'type' => 'code_example',
                'price' => 24.99,
                'category' => 'coding',
                'description' => 'Complete starter kit for building AI applications with Python. Includes boilerplate code, examples, and best practices for integrating with ChatGPT, Claude, and other APIs.',
                'short_description' => 'Complete starter kit for AI applications with Python.',
                'tags' => ['coding', 'python', 'api-integration', 'ai'],
                'features' => ['Complete codebase', 'API examples', 'Best practices', 'Documentation'],
                'includes' => ['GitHub repository', 'Code samples', 'Setup guide', 'API docs'],
            ],
            [
                'title' => 'Voice AI & ElevenLabs Integration Guide',
                'type' => 'tutorial',
                'price' => 12.99,
                'category' => 'voice-ai',
                'description' => 'Learn to integrate ElevenLabs voice AI into your projects. Covers API setup, voice cloning, multilingual voices, and production deployment.',
                'short_description' => 'Master voice AI integration with ElevenLabs.',
                'tags' => ['voice-ai', 'tutorial', 'api-integration', 'elevenlabs'],
                'features' => ['Step-by-step guide', 'Code examples', 'Voice samples', 'API reference'],
                'includes' => ['Tutorial guide', 'Code snippets', 'API documentation'],
            ],
            [
                'title' => 'Free: AI Beginner Roadmap',
                'type' => 'tutorial',
                'price' => 0,
                'is_free' => true,
                'category' => 'ai-tutorials',
                'description' => 'Free guide to getting started with AI tools. Perfect for beginners. Covers what AI is, popular tools, and first steps to using them effectively.',
                'short_description' => 'Free beginner guide to getting started with AI tools.',
                'tags' => ['beginner', 'free', 'ai', 'tutorial'],
                'features' => ['Beginner-friendly', 'No prerequisites', 'Tool comparisons', 'Action steps'],
                'includes' => ['Complete guide', 'Resource links'],
            ],
            [
                'title' => 'AI Tools Comparison Matrix 2024',
                'type' => 'cheatsheet',
                'price' => 3.99,
                'category' => 'ai-tools-platforms',
                'description' => 'Comprehensive comparison of 30+ AI tools. Features, pricing, use cases, and ratings. Updated quarterly for latest tools and pricing.',
                'short_description' => 'Comprehensive comparison of 30+ popular AI tools.',
                'tags' => ['comparison', 'tools', 'ai', 'reference'],
                'features' => ['30+ tools', 'Feature comparison', 'Pricing matrix', 'Ratings & reviews'],
                'includes' => ['Comparison spreadsheet', 'Updated quarterly'],
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach (array_slice($samples, 0, $count) as $sample) {
            $exists = DigitalProduct::where('slug', Str::slug($sample['title']))->exists();

            if ($exists) {
                $this->line("⏭️  Skipping: {$sample['title']} (already exists)");
                $skipped++;
                continue;
            }

            $product = DigitalProduct::create([
                'creator_id' => $user->id,
                'title' => $sample['title'],
                'slug' => Str::slug($sample['title']),
                'description' => $sample['description'],
                'short_description' => $sample['short_description'],
                'type' => $sample['type'],
                'price' => $sample['price'] ?? 0,
                'is_free' => $sample['is_free'] ?? ($sample['price'] == 0),
                'category' => $sample['category'],
                'tags' => $sample['tags'],
                'features' => $sample['features'],
                'includes' => $sample['includes'],
                'content' => substr($sample['description'], 0, 200),
                'status' => 'published',
                'published_at' => now()->subDays(rand(1, 30)),
                'downloads_count' => rand(0, 100),
                'purchases_count' => rand(0, 50),
                'rating' => round(rand(30, 50) / 10, 1),
                'reviews_count' => rand(0, 20),
            ]);

            $this->info("✅ Created: {$product->title} (\${$product->price})");
            $created++;
        }

        $this->newLine();
        $this->info("🎉 Sample creation complete!");
        $this->line("   Created: <fg=green>$created</> products");
        $this->line("   Skipped: <fg=yellow>$skipped</> existing products");
        $this->newLine();
        $this->line("📍 View samples at: <fg=cyan>http://localhost:9070/resources</>");
        $this->line("📍 Manage in admin: <fg=cyan>http://localhost:9070/admin/digital-products</>");

        return self::SUCCESS;
    }
}
