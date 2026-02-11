<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\AITutorialGenerationService;
use App\Services\ContentEnhancementService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class GenerateWeeklyTutorialCommand extends Command
{
    protected $signature = 'ai-learning:generate-weekly {--day=} {--dry-run}';
    protected $description = 'Generate weekly AI learning tutorial based on schedule';

    public function handle(AITutorialGenerationService $tutorialService): int
    {
        $day = $this->option('day') ?: now()->format('l'); // Monday, Wednesday, etc.
        $schedule = config('ai-learning.weekly_schedule.' . strtolower($day));

        if (!$schedule) {
            $this->warn("No tutorial scheduled for {$day}");
            return self::SUCCESS;
        }

        // Check frequency
        if (!$this->shouldGenerateToday($schedule['frequency'])) {
            $this->info("Skipping {$day} - frequency is {$schedule['frequency']}");
            return self::SUCCESS;
        }

        // Select topic from appropriate difficulty level
        $topic = $this->selectTopic($schedule['type']);

        if (!$topic) {
            $this->error("No topics available for {$schedule['type']} level");
            return self::FAILURE;
        }

        $this->info("═══════════════════════════════════════════════════════");
        $this->info("Generating {$schedule['type']} tutorial: {$topic}");
        $this->info("═══════════════════════════════════════════════════════");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info("[DRY RUN] Would generate 8-part tutorial series");
            $this->info("Topics available for {$schedule['type']}: " . count(config("ai-learning.tutorial_topics.{$schedule['type']}")));
            return self::SUCCESS;
        }

        try {
            // Generate 8-part comprehensive tutorial
            $this->line("Generating content with Claude 3.5 Sonnet...");
            $parts = $tutorialService->generateComprehensiveTutorial($topic, 8);

            if (empty($parts)) {
                $this->error("Failed to generate tutorial");
                return self::FAILURE;
            }

            $this->line("Creating series in database...");

            // Create series
            $seriesSlug = Str::slug($topic);
            $categoryId = $this->getCategoryId('AI Tutorials');

            $enhancementService = app(ContentEnhancementService::class);

            foreach ($parts as $index => $content) {
                try {
                    // Enhance content with E-E-A-T signals
                    $enhancedContent = $content['content'];
                    $enhancementService->enhanceTutorialContent(
                        $enhancedContent,
                        $topic,
                        $index + 1,
                        8,
                        $schedule['type']
                    );

                    // Fetch featured image for the tutorial
                    $featuredImage = $this->fetchTutorialImage($topic);

                    $post = Post::create([
                        'author_id' => 1, // Platform account
                        'title' => $content['title'],
                        'slug' => Str::slug($content['title'] . ' ' . Str::random(6)),
                        'excerpt' => $content['excerpt'] ?? substr(strip_tags($enhancedContent), 0, 500),
                        'content' => $enhancedContent,
                        'featured_image' => $featuredImage,
                        'category_id' => $categoryId,
                        'series_title' => $topic,
                        'series_slug' => $seriesSlug,
                        'series_part' => $index + 1,
                        'series_total_parts' => 8,
                        'status' => 'published',
                        'published_at' => now(),
                        'is_premium' => $index >= 6, // Parts 7-8 are premium (70% free, 30% premium)
                        'premium_tier' => $index >= 6 ? 'basic' : null,
                    ]);

                    // Add expertise signals and structured data
                    $enhancementService->addExpertiseSignals($post, $schedule['type']);

                    // Generate and store structured data in seo_meta
                    $structuredData = $enhancementService->generateStructuredData($post, $schedule['type']);
                    $seometadata = $post->seo_meta ?? [];
                    $seometadata['structured_data'] = json_decode($structuredData, true);
                    $post->update(['seo_meta' => $seometadata]);

                    // Attach tags
                    $this->attachTags($post, $schedule['type'], $topic);

                    $this->info("  ✅ Published Part " . ($index + 1) . ": {$post->title}");
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed to create part " . ($index + 1) . ": " . $e->getMessage());
                    continue;
                }
            }

            $this->newLine();
            $this->info("═══════════════════════════════════════════════════════");
            $this->info("🎉 Complete 8-part tutorial published!");
            $this->info("═══════════════════════════════════════════════════════");
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating tutorial: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function selectTopic(string $level): ?string
    {
        $topics = config("ai-learning.tutorial_topics.{$level}");

        // Get topics used in the last 6 months
        $usedTopics = Post::where('created_at', '>', now()->subMonths(6))
            ->whereNotNull('series_slug')
            ->pluck('series_title')
            ->unique()
            ->toArray();

        // Find available topics
        $availableTopics = array_diff($topics, $usedTopics);

        if (empty($availableTopics)) {
            // If all topics exhausted, use all topics (reset cycle)
            $availableTopics = $topics;
        }

        return !empty($availableTopics) ? $availableTopics[array_rand($availableTopics)] : null;
    }

    protected function shouldGenerateToday(string $frequency): bool
    {
        return match($frequency) {
            'weekly' => true,
            'every_other_week' => now()->weekOfYear % 2 === 0,
            'monthly' => now()->day <= 7, // First week of month
            default => false,
        };
    }

    protected function getCategoryId(string $categoryName): int
    {
        return Category::firstOrCreate(
            ['name' => $categoryName],
            [
                'slug' => Str::slug($categoryName),
                'is_active' => true,
                'color' => '#3B82F6',
                'icon' => 'academic-cap',
            ]
        )->id;
    }

    protected function attachTags(Post $post, string $level, string $topic): void
    {
        $commonTags = ['ai', 'tutorial', 'learning'];
        $levelTag = [$level]; // 'beginner', 'intermediate', 'advanced'

        // Extract topic-specific tags from title
        $topicTags = $this->extractTopicTags($topic);

        $allTags = array_merge($commonTags, $levelTag, $topicTags);
        $allTags = array_unique($allTags); // Remove duplicates

        foreach ($allTags as $tagName) {
            if (empty($tagName)) {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['name' => $tagName],
                [
                    'slug' => Str::slug($tagName),
                    'is_active' => true,
                ]
            );
            $post->tags()->syncWithoutDetaching([$tag->id]);
        }
    }

    protected function extractTopicTags(string $title): array
    {
        $keywords = [
            'chatgpt', 'claude', 'midjourney', 'prompt', 'automation',
            'langchain', 'ai-agent', 'rag', 'fine-tuning', 'api',
            'gpt', 'llm', 'agent', 'image', 'text', 'speech',
        ];

        $tags = [];
        $lowerTitle = strtolower($title);

        foreach ($keywords as $keyword) {
            if (str_contains($lowerTitle, $keyword)) {
                $tags[] = $keyword;
            }
        }

        return $tags;
    }

    /**
     * Fetch a featured image from Unsplash API for the tutorial topic
     */
    protected function fetchTutorialImage(string $topic): ?string
    {
        try {
            $apiKey = config('services.unsplash.key') ?? env('UNSPLASH_ACCESS_KEY');

            if (!$apiKey) {
                return null;
            }

            $response = Http::get('https://api.unsplash.com/search/photos', [
                'query' => $topic,
                'per_page' => 1,
                'order_by' => 'relevant',
                'client_id' => $apiKey,
            ]);

            if ($response->successful() && $response->json('results.0.urls.regular')) {
                return $response->json('results.0.urls.regular');
            }

            return null;
        } catch (\Exception $e) {
            // Log error but don't fail the tutorial generation
            \Illuminate\Support\Facades\Log::warning('Failed to fetch image from Unsplash', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
