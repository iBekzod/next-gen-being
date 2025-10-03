<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'AI Workflows', 'description' => 'Step-by-step automations, prompts, and systems.'],
            ['name' => 'Operating Systems', 'description' => 'Planning, documentation, and async processes.'],
            ['name' => 'Growth & Distribution', 'description' => 'Acquisition loops, social proof, and partnerships.'],
        ])->map(function (array $data, int $order) {
            return Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, [
                    'sort_order' => $order + 1,
                    'is_active' => true,
                ])
            );
        });

        $tags = collect([
            'Notion',
            'Zapier',
            'ChatGPT',
            'Affiliates',
            'Creator Playbook',
        ])->map(function (string $name) {
            return Tag::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                ]
            );
        });

        $author = User::firstOrCreate(
            ['email' => 'founder@nextgenbeing.com'],
            [
                'name' => 'NextGenBeing Founder',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $posts = [
            [
                'title' => 'Designing Your AI-First Morning Workflow',
                'excerpt' => 'A three-phase operating cadence that compresses research, writing, and outreach into one AI-augmented morning block.',
                'content' => <<<'HTML'
<h2>Morning Focus Block</h2>
<p>Start with 20 minutes of AI-assisted research using ChatGPT &amp; Perplexity, funnel those takeaways into your Notion daily system, then draft outreach copy with an autoreviewer prompt.</p>
<h3>Toolkit</h3>
<ul>
  <li>Perplexity Collections for topic tracking</li>
  <li>Notion synced databases for tasks</li>
  <li>Zapier to push highlights to Slack/Obsidian</li>
</ul>
HTML,
                'category' => $categories[0],
                'tag_names' => ['ChatGPT', 'Notion', 'Creator Playbook'],
                'is_featured' => true,
            ],
            [
                'title' => 'Affiliate Distribution Playbook for Solo Media Brands',
                'excerpt' => 'A repeatable way to source, vet, and activate affiliate partners without losing your editorial voice.',
                'content' => <<<'HTML'
<h2>Why affiliates still work</h2>
<p>When you package insights tightly, affiliates become your discovery engine. We show how to build a transparent offer sheet, scope partner ICPs, and sequence follow-up.</p>
<h3>Activation Ladder</h3>
<ol>
  <li>Publish an offer hub with testimonials.</li>
  <li>Automate discovery lists via Clay + Apollo.</li>
  <li>Ship partner-ready creative inside a Notion portal.</li>
</ol>
HTML,
                'category' => $categories[2],
                'tag_names' => ['Affiliates', 'Creator Playbook'],
            ],
            [
                'title' => 'Async OS: The Documentation Stack Behind $10K MRR',
                'excerpt' => 'Templates and rituals we use to keep product, growth, and operations aligned while staying async.',
                'content' => <<<'HTML'
<p>This stack combines Notion, Linear, and Loom for a frictionless cadence. Includes SOP templates, decision logs, and a Loom cadence to keep contributors aligned.</p>
HTML,
                'category' => $categories[1],
                'tag_names' => ['Notion', 'AI Workflows'],
                'is_premium' => true,
            ],
        ];

        Post::withoutSyncingToSearch(function () use ($posts, $tags, $author) {
            foreach ($posts as $data) {
                $post = Post::updateOrCreate(
                    ['slug' => Str::slug($data['title'])],
                    [
                        'title' => $data['title'],
                        'excerpt' => $data['excerpt'],
                        'content' => $data['content'],
                        'status' => 'published',
                        'published_at' => now()->subDays(rand(3, 21)),
                        'is_featured' => $data['is_featured'] ?? false,
                        'is_premium' => $data['is_premium'] ?? false,
                        'author_id' => $author->id,
                        'category_id' => $data['category']->id,
                        'allow_comments' => true,
                    ]
                );

                $tagIds = collect($data['tag_names'] ?? [])
                    ->map(function (string $tagName) use ($tags) {
                        return optional($tags->firstWhere('name', $tagName))->id;
                    })
                    ->filter()
                    ->all();

                $post->tags()->sync($tagIds);
            }
        });
    }
}

