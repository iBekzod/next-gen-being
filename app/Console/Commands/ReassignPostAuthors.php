<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;

class ReassignPostAuthors extends Command
{
    protected $signature = 'posts:reassign-authors
                            {--dry-run : Show what would change without making changes}';

    protected $description = 'Reassign posts with generic authors (Admin, AI Generator) to real tech bloggers';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        // Get all real tech bloggers (seeded bloggers with @nextgenbeing.com)
        $bloggers = User::whereHas('roles', fn($q) => $q->where('slug', 'blogger'))
            ->orWhere('email', 'like', '%@nextgenbeing.com')
            ->where('is_active', true)
            ->get();

        if ($bloggers->isEmpty()) {
            $this->error('No bloggers found. Run: php artisan db:seed --class=UserSeeder');
            return self::FAILURE;
        }

        $this->info("Found {$bloggers->count()} tech bloggers available.");

        // Find posts with generic/bot authors
        $genericNames = ['Admin', 'AI Tutorial Generator', 'Content Curator', 'AI Generator', 'NextGenBeing Founder'];

        $genericAuthorIds = User::where(function ($q) use ($genericNames) {
            foreach ($genericNames as $name) {
                $q->orWhere('name', $name);
            }
        })->orWhere('email', 'like', '%ai-tutorials@%')
          ->pluck('id');

        $posts = Post::whereIn('author_id', $genericAuthorIds)
            ->where('status', 'published')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No posts with generic authors found. All good!');
            return self::SUCCESS;
        }

        $this->info("Found {$posts->count()} published posts with generic authors to reassign.");
        $this->newLine();

        $table = [];
        $bloggerCount = $bloggers->count();

        foreach ($posts as $index => $post) {
            // Assign deterministically based on post id for consistency
            $blogger = $bloggers[$post->id % $bloggerCount];

            $oldAuthor = $post->author->name ?? 'Unknown';
            $table[] = [
                $post->id,
                substr($post->title, 0, 55) . (strlen($post->title) > 55 ? '...' : ''),
                $oldAuthor,
                $blogger->name,
            ];

            if (!$isDryRun) {
                $post->author_id = $blogger->id;
                $post->save();
            }
        }

        $this->table(['ID', 'Title', 'Old Author', 'New Author'], $table);

        if ($isDryRun) {
            $this->newLine();
            $this->warn('DRY RUN — no changes made. Run without --dry-run to apply.');
        } else {
            $this->newLine();
            $this->info("✅ Successfully reassigned {$posts->count()} posts to real tech bloggers.");
        }

        // Also handle draft posts
        $draftPosts = Post::whereIn('author_id', $genericAuthorIds)
            ->where('status', 'draft')
            ->count();

        if ($draftPosts > 0) {
            $this->newLine();
            $this->line("ℹ️  Also found {$draftPosts} draft posts with generic authors.");
            if (!$isDryRun) {
                Post::whereIn('author_id', $genericAuthorIds)
                    ->where('status', 'draft')
                    ->get()
                    ->each(function ($post) use ($bloggers, $bloggerCount) {
                        $post->author_id = $bloggers[$post->id % $bloggerCount]->id;
                        $post->save();
                    });
                $this->info("✅ Reassigned {$draftPosts} draft posts too.");
            }
        }

        return self::SUCCESS;
    }
}
