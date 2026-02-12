<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\DeepResearchContentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDeepResearchPost extends Command
{
    protected $signature = 'content:generate-deep-research
                            {--count=1 : Number of deep research posts to generate}
                            {--topic= : Specific topic to write about}
                            {--author= : Author user ID (defaults to first admin)}
                            {--category= : Specific category}
                            {--publish : Publish immediately instead of draft}
                            {--no-tags : Don\'t auto-generate tags}';

    protected $description = 'Generate deep research-backed blog posts (15+ min reads) with practical examples';

    public function handle(): int
    {
        $this->info("\n");
        $this->info("╔════════════════════════════════════════════════════════════════════╗");
        $this->info("║      🔬 Deep Research Blog Post Generator                          ║");
        $this->info("║   Generates 15+ min reads with practical examples & real solutions  ║");
        $this->info("╚════════════════════════════════════════════════════════════════════╝");
        $this->newLine();

        $service = app(DeepResearchContentService::class);
        $count = (int) $this->option('count');
        $topic = $this->option('topic');

        if ($count < 1) {
            $this->error('Count must be at least 1');
            return self::FAILURE;
        }

        $generatedPosts = [];
        $failedCount = 0;

        for ($i = 1; $i <= $count; $i++) {
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📚 Generating Deep Research Post {$i} of {$count}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            try {
                // Generate content
                $this->info("🔍 Researching and synthesizing content...");
                $postData = $service->generateDeepResearchPost($topic);

                // Get or create category
                $category = $this->getOrCreateCategory($postData['category']);

                // Get author
                $author = $this->getAuthor();

                // Create post
                $this->info("💾 Creating post in database...");
                $post = Post::create([
                    'title' => $postData['title'],
                    'slug' => $postData['slug'],
                    'excerpt' => $postData['excerpt'],
                    'content' => $postData['content'],
                    'featured_image' => $postData['featured_image'],
                    'read_time' => $postData['read_time'],
                    'category_id' => $category->id,
                    'author_id' => $author->id,
                    'status' => $this->option('publish') ? 'published' : 'draft',
                    'published_at' => $this->option('publish') ? now() : null,
                ]);

                // Attach tags
                if (!$this->option('no-tags') && !empty($postData['tags'])) {
                    $this->info("🏷️  Attaching tags...");
                    $this->attachTags($post, $postData['tags']);
                }

                $generatedPosts[] = $post;

                $this->info("✅ Post created successfully!");
                $this->info("   Title: {$post->title}");
                $this->info("   Slug: {$post->slug}");
                $this->info("   Read Time: {$post->read_time} min");
                $this->info("   Words: {$postData['word_count']}");
                $this->newLine();

                // Delay between posts
                if ($i < $count) {
                    $this->info("⏳ Waiting 3 seconds before next post...");
                    sleep(3);
                    $this->newLine();
                }

            } catch (\Exception $e) {
                $failedCount++;
                $this->error("❌ Failed to generate post {$i}: " . $e->getMessage());
                Log::error("Deep research post generation failed", [
                    'attempt' => $i,
                    'topic' => $topic,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->newLine();
            }
        }

        // Summary
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Generation Summary");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Total requested: {$count}");
        $this->info("Successfully generated: " . count($generatedPosts));
        $this->info("Failed: {$failedCount}");

        if (count($generatedPosts) > 0) {
            $this->newLine();
            $this->info("✅ Generated Posts:");
            foreach ($generatedPosts as $index => $post) {
                $num = $index + 1;
                $status = $this->option('publish') ? '📰 Published' : '📝 Draft';
                $this->info("  {$num}. {$post->title} {$status}");
                $this->info("     → {$post->read_time} min read | {$post->category->name}");
            }
        }

        $this->newLine();
        $this->info("💡 Tip: Use 'php artisan content:generate-deep-research --topic=\"Your Topic\" --publish' to generate specific topics");
        $this->newLine();

        return $failedCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Get or create category
     */
    private function getOrCreateCategory(string $categoryName): Category
    {
        return Category::firstOrCreate(
            ['name' => $categoryName],
            ['slug' => \Illuminate\Support\Str::slug($categoryName)]
        );
    }

    /**
     * Get author (default to first admin)
     */
    private function getAuthor()
    {
        $authorId = $this->option('author');

        if ($authorId) {
            return \App\Models\User::findOrFail($authorId);
        }

        // Default to first admin user
        $admin = \App\Models\User::where('role', 'admin')
            ->orWhere('is_admin', true)
            ->first();

        if (!$admin) {
            // Fallback to any user
            $admin = \App\Models\User::first();
        }

        if (!$admin) {
            throw new \Exception('No users found in system. Please create a user first.');
        }

        return $admin;
    }

    /**
     * Attach tags to post
     */
    private function attachTags(Post $post, array $tagNames): void
    {
        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => \Illuminate\Support\Str::slug($tagName)]
            );
            $post->tags()->attach($tag);
        }
    }
}
