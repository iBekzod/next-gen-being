<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsMarketplaceListing;
use Illuminate\Database\Seeder;

class HaloListingSeeder extends Seeder
{
    use SeedsMarketplaceListing;

    public function run(): void
    {
        $this->seedMarketplaceListing(
            [
                'slug'        => 'halo-ai-writer',
                'title'       => 'Halo — AI Writing Assistant',
                'tagline'     => 'A refined AI chat UI with streaming responses',
                'description' => "A production-grade AI writing assistant interface.\n\n".
                                 "Editorial two-tone layout, suggested-prompt starters, a real streaming ".
                                 "response animation, an auto-growing composer, and conversation history. ".
                                 "Bring your own model, the polished front end is done.",
                'category'    => 'ai',
                'tags'        => ['ai', 'chat', 'assistant', 'ui'],
                'rating'      => 4.9,
                'reviews_count' => 28,
                'sales_count' => 28,
            ],
            [
                ['tier' => 'prompt', 'title' => 'Halo — Prompt Plan',   'price' => 6,  'type' => 'prompt',       'desc' => 'Prompts to generate the full assistant UI with Claude Code.'],
                ['tier' => 'design', 'title' => 'Halo — Design',        'price' => 11, 'type' => 'template',     'desc' => 'The complete streaming chat interface — the exact demo above.'],
                ['tier' => 'code',   'title' => 'Halo — Full Project',  'price' => 59, 'type' => 'code_example', 'desc' => 'Chat UI wired to a streaming API endpoint. Clone & deploy.'],
                ['tier' => 'bundle', 'title' => 'Halo — Bundle',        'price' => 69, 'type' => 'code_example', 'desc' => 'Prompt + design + full project, discounted.'],
            ]
        );
    }
}
