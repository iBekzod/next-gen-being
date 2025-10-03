<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FeedDemoSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();

        if (!$author) {
            $author = User::factory()->create([
                'name' => 'Demo Creator',
                'email' => 'demo@nextgenbeing.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $category = Category::first() ?? Category::create([
            'name' => 'Updates',
            'slug' => 'updates',
            'description' => 'Seeded demo collection',
            'is_active' => true,
        ]);

        $tag = Tag::first() ?? Tag::create([
            'name' => 'Demo',
            'slug' => 'demo',
            'is_active' => true,
        ]);

        $titles = [
            'AI Case Study: Turning Internal Memos into a Public Newsletter',
            'The 5-day Affiliate Sprint You Can Run Solo',
            'Systemizing Customer Research with Claude Projects',
        ];

        Post::withoutSyncingToSearch(function () use ($titles, $author, $category, $tag) {
            foreach ($titles as $title) {
                Post::updateOrCreate(
                    ['slug' => Str::slug($title)],
                    [
                        'title' => $title,
                        'excerpt' => 'Quick-start blueprint published from FeedDemoSeeder.',
                        'content' => '<p>This is seeded demo content to validate RSS and sitemap output.</p>',
                        'status' => 'published',
                        'published_at' => now()->subHours(rand(6, 72)),
                        'author_id' => $author->id,
                        'category_id' => $category->id,
                        'allow_comments' => true,
                    ]
                )->tags()->syncWithoutDetaching([$tag->id]);
            }
        });
    }
}


