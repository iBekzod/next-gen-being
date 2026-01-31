<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class AILearningCategoriesAndTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create AI Learning Categories
        $categories = [
            [
                'name' => 'AI Tutorials',
                'slug' => 'ai-tutorials',
                'description' => 'Complete step-by-step tutorials on AI tools and techniques',
                'color' => '#3B82F6',
                'icon' => 'academic-cap',
                'sort_order' => 1,
            ],
            [
                'name' => 'Prompt Engineering',
                'slug' => 'prompt-engineering',
                'description' => 'Learn to write effective prompts for AI models',
                'color' => '#8B5CF6',
                'icon' => 'sparkles',
                'sort_order' => 2,
            ],
            [
                'name' => 'ChatGPT',
                'slug' => 'chatgpt',
                'description' => 'ChatGPT tips, tricks, and advanced techniques',
                'color' => '#10B981',
                'icon' => 'chat',
                'sort_order' => 3,
            ],
            [
                'name' => 'Claude',
                'slug' => 'claude',
                'description' => 'Master Anthropic Claude AI',
                'color' => '#F59E0B',
                'icon' => 'beaker',
                'sort_order' => 4,
            ],
            [
                'name' => 'Midjourney',
                'slug' => 'midjourney',
                'description' => 'AI image generation with Midjourney',
                'color' => '#EC4899',
                'icon' => 'photograph',
                'sort_order' => 5,
            ],
            [
                'name' => 'AI Automation',
                'slug' => 'ai-automation',
                'description' => 'Automate workflows with AI tools and integrations',
                'color' => '#6366F1',
                'icon' => 'cog',
                'sort_order' => 6,
            ],
            [
                'name' => 'AI Tools & Platforms',
                'slug' => 'ai-tools-platforms',
                'description' => 'Reviews and guides for various AI platforms',
                'color' => '#14B8A6',
                'icon' => 'wrench',
                'sort_order' => 7,
            ],
            [
                'name' => 'AI for Business',
                'slug' => 'ai-for-business',
                'description' => 'Use AI to grow your business and increase productivity',
                'color' => '#06B6D4',
                'icon' => 'briefcase',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('✅ Created AI Learning categories');

        // Create common tags for AI content
        $tags = [
            'ai' => 'General AI',
            'tutorial' => 'Tutorial',
            'chatgpt' => 'ChatGPT',
            'claude' => 'Claude AI',
            'midjourney' => 'Midjourney',
            'prompt-engineering' => 'Prompt Engineering',
            'ai-automation' => 'AI Automation',
            'productivity' => 'Productivity',
            'beginner' => 'Beginner Level',
            'intermediate' => 'Intermediate Level',
            'advanced' => 'Advanced Level',
            'prompt-template' => 'Prompt Template',
            'tips-tricks' => 'Tips & Tricks',
            'how-to' => 'How To',
            'comparison' => 'Comparison',
            'tool-review' => 'Tool Review',
            'case-study' => 'Case Study',
            'workflow' => 'Workflow',
            'coding' => 'Coding',
            'content-creation' => 'Content Creation',
            'business' => 'Business',
            'marketing' => 'Marketing',
            'design' => 'Design',
            'writing' => 'Writing',
            'research' => 'Research',
            'data-analysis' => 'Data Analysis',
            'image-generation' => 'Image Generation',
            'voice-ai' => 'Voice AI',
            'video-ai' => 'Video AI',
            'web-development' => 'Web Development',
            'mobile-development' => 'Mobile Development',
            'best-practices' => 'Best Practices',
            'common-mistakes' => 'Common Mistakes',
            'troubleshooting' => 'Troubleshooting',
            'api-integration' => 'API Integration',
            'machine-learning' => 'Machine Learning',
            'deep-learning' => 'Deep Learning',
            'nlp' => 'Natural Language Processing',
        ];

        foreach ($tags as $slug => $name) {
            Tag::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Created ' . count($tags) . ' AI learning tags');
        $this->command->info('🎉 AI Learning database seeded successfully!');
    }
}
