<?php

namespace App\Console\Commands;

use App\Models\DigitalProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GeneratePromptLibraryCommand extends Command
{
    protected $signature = 'ai-learning:generate-prompts {--count=5}';
    protected $description = 'Generate AI prompt templates for prompt library';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $categories = ['chatgpt', 'claude', 'midjourney', 'general', 'business', 'creative', 'coding', 'writing'];

        $this->info("═══════════════════════════════════════════════════════");
        $this->info("🎯 Generating {$count} prompt templates...");
        $this->info("═══════════════════════════════════════════════════════");
        $this->newLine();

        $generated = 0;

        for ($i = 0; $i < $count; $i++) {
            $category = $categories[array_rand($categories)];

            try {
                $product = $this->generatePromptTemplate($category);

                if ($product) {
                    $this->info("  ✅ Generated: {$product->title}");
                    $generated++;
                } else {
                    $this->warn("  ⚠️  Failed to generate prompt for category: {$category}");
                }
            } catch (\Exception $e) {
                $this->warn("  ⚠️  Error: " . $e->getMessage());
            }

            // Small delay between API calls
            if ($i < $count - 1) {
                sleep(2);
            }
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════");
        $this->info("✅ Successfully generated {$generated} prompts!");
        $this->info("═══════════════════════════════════════════════════════");

        return $generated > 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function generatePromptTemplate(string $category): ?DigitalProduct
    {
        // Check if we already have a similar prompt recently
        $recentCount = DigitalProduct::where('type', 'prompt')
            ->where('category', $category)
            ->where('created_at', '>', now()->subDays(7))
            ->count();

        if ($recentCount >= 2) {
            $this->line("  ⏭️  Skipping {$category} - already generated 2 this week");
            return null;
        }

        try {
            // Create a structured prompt template with realistic content
            // For MVP, we'll create templates programmatically rather than calling AI each time
            $templates = $this->getPromptTemplates($category);

            if (empty($templates)) {
                return null;
            }

            $template = $templates[array_rand($templates)];

            // Create txt file with prompt template
            $filename = Str::slug($template['title']) . '-' . Str::random(8) . '.txt';
            $filePath = storage_path('app/private/prompts/' . $filename);

            $fileContent = "# {$template['title']}\n\n";
            $fileContent .= "## Description\n{$template['description']}\n\n";
            $fileContent .= "## Template\n```\n{$template['template']}\n```\n\n";
            $fileContent .= "## Example\n{$template['example']}\n\n";
            $fileContent .= "## Tips\n";

            foreach ($template['tips'] as $tip) {
                $fileContent .= "- {$tip}\n";
            }

            File::ensureDirectoryExists(storage_path('app/private/prompts'));
            File::put($filePath, $fileContent);

            // Create digital product
            return DigitalProduct::create([
                'creator_id' => 1, // Platform user
                'title' => $template['title'],
                'slug' => Str::slug($template['title']) . '-' . Str::random(8),
                'description' => $template['description'],
                'short_description' => $template['short_description'] ?? substr($template['description'], 0, 100),
                'type' => 'prompt',
                'price' => 4.99, // Standard prompt price
                'tier_required' => 'free',
                'is_free' => false,
                'file_path' => 'prompts/' . $filename,
                'category' => $category,
                'tags' => array_merge([$category, 'prompt-template'], $template['tags'] ?? []),
                'features' => [
                    'Ready-to-use prompt template',
                    'Includes examples and tips',
                    'TXT file format',
                    'Lifetime updates'
                ],
                'includes' => ['Prompt template', 'Usage examples', 'Tips & best practices'],
                'content' => substr($template['description'], 0, 200), // Preview
                'status' => 'published',
                'published_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->error("Error creating prompt: " . $e->getMessage());
            return null;
        }
    }

    protected function getPromptTemplates(string $category): array
    {
        $templates = [
            'chatgpt' => [
                [
                    'title' => 'ChatGPT Content Writer for Blog Posts',
                    'short_description' => 'Create engaging blog posts with ChatGPT',
                    'description' => 'Use this prompt to generate high-quality, SEO-optimized blog posts quickly. Perfect for content creators and marketing teams.',
                    'template' => 'Write a {{tone}} blog post about {{topic}} that targets {{audience}}. The post should be {{length}} words and include {{keyword}}. Include an introduction, 3 main sections with examples, and a conclusion.',
                    'example' => 'Write a professional blog post about AI productivity tools that targets small business owners. The post should be 2000 words and include "automation" and "efficiency". Include an introduction, 3 main sections with examples, and a conclusion.',
                    'tags' => ['content-writing', 'blog', 'seo'],
                    'tips' => [
                        'Be specific about your target audience',
                        'Include the main keyword naturally',
                        'Specify the tone (professional, casual, technical)',
                        'Ask for specific structure (sections, examples)',
                    ]
                ],
                [
                    'title' => 'ChatGPT Email Marketing Assistant',
                    'short_description' => 'Generate compelling email campaigns',
                    'description' => 'Create professional email templates and copy for marketing campaigns using ChatGPT.',
                    'template' => 'Create an email campaign for {{campaign_type}} targeting {{audience}}. The subject line should {{criteria}}. Include a hook, value proposition, and clear CTA. Tone: {{tone}}',
                    'example' => 'Create an email campaign for product launch targeting SaaS founders. The subject line should create urgency. Include a hook, value proposition, and clear CTA. Tone: Persuasive but friendly',
                    'tags' => ['email', 'marketing', 'sales'],
                    'tips' => [
                        'Specify the campaign type and goal',
                        'Define your target audience clearly',
                        'Include tone preferences',
                        'Ask for specific elements (CTA, personalization)',
                    ]
                ],
            ],
            'claude' => [
                [
                    'title' => 'Claude Code Analysis & Debugging Assistant',
                    'short_description' => 'Debug code and improve code quality',
                    'description' => 'Use Claude to analyze, debug, and improve your code. Perfect for developers wanting detailed explanations.',
                    'template' => 'Review this {{language}} code and: 1) Identify bugs, 2) Suggest optimizations, 3) Explain what it does, 4) Provide a corrected version.\n\nCode:\n```\n{{code}}\n```',
                    'example' => 'Review this Python code and: 1) Identify bugs, 2) Suggest optimizations, 3) Explain what it does, 4) Provide a corrected version.',
                    'tags' => ['coding', 'debugging', 'programming'],
                    'tips' => [
                        'Include the programming language',
                        'Paste your code between backticks',
                        'Ask for specific types of analysis',
                        'Request optimized versions',
                    ]
                ],
            ],
            'midjourney' => [
                [
                    'title' => 'Midjourney Detailed Image Prompt Builder',
                    'short_description' => 'Create detailed prompts for Midjourney',
                    'description' => 'Learn to write effective prompts for Midjourney to generate stunning AI artwork.',
                    'template' => '[CONCEPT] {{concept}}, {{art_style}}, {{medium}}, {{lighting}}, {{composition}}, [MOOD] {{mood}}, [QUALITY] {{quality}}, --aspect {{aspect}} --niji',
                    'example' => '[CONCEPT] Cyberpunk city street, digital art, neon lighting, cinematic composition, [MOOD] futuristic, intense, [QUALITY] highly detailed, 8k, --aspect 16:9 --niji',
                    'tags' => ['midjourney', 'ai-art', 'image-generation'],
                    'tips' => [
                        'Start with a clear concept',
                        'Specify art style (digital, oil, watercolor, etc.)',
                        'Include lighting preferences',
                        'Add mood and atmosphere words',
                        'Use aspect ratio and quality flags',
                    ]
                ],
            ],
            'general' => [
                [
                    'title' => 'General Research Synthesis Prompt',
                    'short_description' => 'Synthesize information from research',
                    'description' => 'Create a comprehensive summary of research on any topic.',
                    'template' => 'Summarize the following topic comprehensively: {{topic}}. Include: 1) Key concepts, 2) Current research, 3) Practical applications, 4) Future trends. Format as an executive summary.',
                    'example' => 'Summarize the following topic comprehensively: Machine Learning in Healthcare. Include: 1) Key concepts, 2) Current research, 3) Practical applications, 4) Future trends.',
                    'tags' => ['research', 'summary', 'analysis'],
                    'tips' => [
                        'Be specific about your topic',
                        'Request specific sections',
                        'Ask for formatting preferences',
                        'Specify detail level',
                    ]
                ],
            ],
            'business' => [
                [
                    'title' => 'Business Plan Outline Generator',
                    'short_description' => 'Create a structured business plan',
                    'description' => 'Generate a comprehensive business plan outline for your {{business_type}}.',
                    'template' => 'Create a business plan for a {{business_type}} targeting {{market}}. Include: Executive Summary, Market Analysis, Product/Service, Marketing Strategy, Financial Projections, Team & Operations.',
                    'example' => 'Create a business plan for a SaaS startup targeting small businesses. Include: Executive Summary, Market Analysis, Product/Service, Marketing Strategy, Financial Projections, Team & Operations.',
                    'tags' => ['business', 'planning', 'startup'],
                    'tips' => [
                        'Define your business type clearly',
                        'Specify your target market',
                        'Ask for specific sections',
                        'Include timeline if relevant',
                    ]
                ],
            ],
            'creative' => [
                [
                    'title' => 'Creative Story Writing Prompt',
                    'short_description' => 'Write creative stories and narratives',
                    'description' => 'Generate creative stories using AI.',
                    'template' => 'Write a {{genre}} story about {{premise}} in {{length}} words. Include {{elements}}. Style: {{style}}. Include dialogue and vivid descriptions.',
                    'example' => 'Write a sci-fi story about a time traveler discovering their past in 1500 words. Include mystery and romance elements. Style: Literary fiction with detailed world-building.',
                    'tags' => ['creative-writing', 'storytelling', 'fiction'],
                    'tips' => [
                        'Specify the genre and setting',
                        'Define key story elements',
                        'Mention writing style preferences',
                        'Include character requirements',
                    ]
                ],
            ],
            'coding' => [
                [
                    'title' => 'Code Documentation Generator',
                    'short_description' => 'Generate documentation for your code',
                    'description' => 'Create clear, professional documentation for any codebase.',
                    'template' => 'Generate {{doc_type}} documentation for this {{language}} code. Include function descriptions, parameters, return types, and usage examples.\n\nCode:\n```\n{{code}}\n```',
                    'example' => 'Generate comprehensive documentation for this Python function. Include descriptions, parameters, return types, and usage examples.',
                    'tags' => ['documentation', 'coding', 'development'],
                    'tips' => [
                        'Specify documentation type (README, API docs, etc.)',
                        'Include the language and framework',
                        'Request specific documentation style',
                        'Ask for examples',
                    ]
                ],
            ],
            'writing' => [
                [
                    'title' => 'Email Outreach & Cold Email Template',
                    'short_description' => 'Create cold emails that convert',
                    'description' => 'Generate personalized cold email templates for outreach.',
                    'template' => 'Create a cold email template for {{purpose}} targeting {{persona}}. Should be {{length}}, have a subject line that {{criteria}}, and include a clear CTA for {{action}}.',
                    'example' => 'Create a cold email template for B2B partnership targeting startup founders. Should be 100-150 words, have a subject line that stands out, and include a clear CTA for a 15-minute call.',
                    'tags' => ['email', 'outreach', 'sales'],
                    'tips' => [
                        'Define your purpose clearly',
                        'Specify target persona',
                        'Include length preferences',
                        'Ask for personalization elements',
                    ]
                ],
            ],
        ];

        return $templates[$category] ?? [];
    }
}
