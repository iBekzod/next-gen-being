<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Services\ImageGenerationService;
use App\Services\ContentModerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeneratePostFromPrompt extends Command
{
    protected $signature = 'blogger:generate-from-prompt
                            {--prompt= : Natural language prompt describing the post to generate}
                            {--author= : Author user ID (blogger)}
                            {--category= : Category slug}
                            {--draft : Save as draft instead of publishing}
                            {--premium : Mark as premium content}
                            {--series= : Generate as tutorial series (number of parts)}
                            {--with-image : Generate and attach featured image}';

    protected $description = 'Generate blog post from natural language prompt for bloggers';

    private string $apiKey;
    private string $model;

    public function handle(): int
    {
        // Get Groq API key
        $this->apiKey = config('services.groq.api_key');
        $this->model = 'llama-3.3-70b-versatile';

        if (!$this->apiKey) {
            $this->error('Groq API key not configured. Set GROQ_API_KEY in .env');
            return self::FAILURE;
        }

        // Get prompt
        $prompt = $this->option('prompt');
        if (!$prompt) {
            $prompt = $this->ask('What topic would you like to write about?');
        }

        if (!$prompt) {
            $this->error('Prompt is required');
            return self::FAILURE;
        }

        // Get author
        $authorId = $this->option('author');
        if (!$authorId) {
            $this->error('Author ID is required (use --author=ID)');
            return self::FAILURE;
        }

        $author = User::find($authorId);
        if (!$author) {
            $this->error("User with ID {$authorId} not found");
            return self::FAILURE;
        }

        // Check if generating series
        $seriesParts = (int) $this->option('series');
        if ($seriesParts > 0) {
            return $this->generateSeries($prompt, $author, $seriesParts);
        }

        // Generate single post
        $this->info("🤖 Generating post from prompt...");
        $this->info("📝 Prompt: {$prompt}");
        $this->info("👤 Author: {$author->name}");
        $this->newLine();

        try {
            $post = $this->generateSinglePost($prompt, $author);

            $this->info("✅ Post created successfully!");
            $this->info("   Title: {$post->title}");
            $this->info("   Slug: {$post->slug}");
            $this->info("   Status: {$post->status}");
            $this->info("   URL: " . route('posts.show', $post->slug));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to generate post: " . $e->getMessage());
            Log::error('Blogger post generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
                'author_id' => $authorId,
            ]);
            return self::FAILURE;
        }
    }

    private function generateSinglePost(string $prompt, User $author): Post
    {
        // Step 1: Generate post content
        $this->info("⏳ Generating content with AI...");
        $content = $this->generateContent($prompt);

        // Step 2: Get category
        $categorySlug = $this->option('category');
        $category = $categorySlug
            ? Category::where('slug', $categorySlug)->first()
            : Category::inRandomOrder()->first();

        if (!$category) {
            throw new \Exception('No categories available. Create at least one category first.');
        }

        // Step 3: Generate image if requested
        $featuredImage = null;
        $imageAttribution = null;
        if ($this->option('with-image')) {
            $this->info("🖼️  Generating featured image...");
            $imageService = app(ImageGenerationService::class);
            $imageResult = $imageService->generateFeaturedImage($content['title'], $category->name);
            if ($imageResult) {
                // Extract filename from URL (e.g., storage/posts/featured/abc123.jpg)
                $url = $imageResult['url'];
                $featuredImage = str_replace(asset(''), '', $url); // Get relative path
                $imageAttribution = $imageResult['attribution'] ?? null;
            }
        }

        // Step 4: Check content moderation
        $moderationService = app(ContentModerationService::class);
        $moderationResult = $moderationService->quickCheck(
            $content['title'],
            $content['content']
        );

        $status = $this->option('draft') ? 'draft' : 'published';
        $isSafe = !($moderationResult['has_issues'] ?? false);
        $moderationStatus = $isSafe ? 'approved' : 'pending';

        if (!$isSafe) {
            $this->warn("⚠️  Content flagged by moderation: " . implode(', ', $moderationResult['flags'] ?? []));
            $this->warn("    Setting status to 'draft' for review.");
            $status = 'draft';
        }

        // Step 5: Create post
        $post = Post::create([
            'title' => $content['title'],
            'slug' => Str::slug($content['title']),
            'content' => $content['content'],
            'excerpt' => $content['excerpt'],
            'author_id' => $author->id,
            'category_id' => $category->id,
            'featured_image' => $featuredImage,
            'image_attribution' => $imageAttribution ? json_encode($imageAttribution) : null,
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
            'is_premium' => $this->option('premium') ? true : false,
            'premium_tier' => $this->option('premium') ? 'basic' : null,
            'moderation_status' => $moderationStatus,
            'moderation_notes' => $isSafe ? null : json_encode($moderationResult['flags'] ?? []),
        ]);

        // Step 6: Add tags
        if (!empty($content['tags'])) {
            $tagIds = [];
            foreach ($content['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        return $post;
    }

    private function generateContent(string $prompt): array
    {
        $systemPrompt = "You are an expert technical writer and blogger. Generate a high-quality, engaging blog post based on the user's prompt.

REQUIREMENTS:
- Write in a clear, engaging style
- Use proper markdown formatting
- Include code examples if technical
- Add relevant headers (##, ###)
- Keep it practical and actionable
- Length: 800-1500 words
- SEO-friendly but natural

CRITICAL: Return ONLY a valid JSON object. No markdown formatting, no code blocks, no extra text. Just the raw JSON.

Format:
{
  \"title\": \"Engaging, specific title\",
  \"excerpt\": \"Brief 2-3 sentence summary\",
  \"content\": \"Full blog post content in markdown format with proper headers and code blocks\",
  \"tags\": [\"tag1\", \"tag2\", \"tag3\"]
}";

        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 8000,
            ]);

        if (!$response->successful()) {
            throw new \Exception('AI API request failed: ' . $response->body());
        }

        $content = $response->json()['choices'][0]['message']['content'];

        // Parse JSON from response (try multiple methods)
        $data = json_decode($content, true);

        // If direct JSON decode fails, try to extract JSON from markdown code blocks
        if (!$data) {
            // Try to extract from ```json code block (with DOTALL flag for multiline)
            if (preg_match('/```json\s*(\{.*\})\s*```/s', $content, $matches)) {
                $data = json_decode($matches[1], true);
            }
            // Try to extract any JSON object (greedy match with DOTALL)
            elseif (preg_match('/(\{(?:[^{}]|(?R))*\})/s', $content, $matches)) {
                $data = json_decode($matches[1], true);
            }
            // Last resort: try to find the outermost braces
            elseif (preg_match('/\{.*\}/s', $content, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (!$data || !isset($data['title']) || !isset($data['content'])) {
            // Log the raw response for debugging
            Log::error('AI response parsing failed', [
                'raw_content' => substr($content, 0, 500),
                'json_error' => json_last_error_msg(),
            ]);
            throw new \Exception('Invalid response format from AI. Check logs for details.');
        }

        return $data;
    }

    private function generateSeries(string $prompt, User $author, int $parts): int
    {
        $this->info("📚 Generating tutorial series with {$parts} parts...");
        $this->newLine();

        $seriesId = Str::random(10);

        for ($i = 1; $i <= $parts; $i++) {
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📝 Generating Part {$i} of {$parts}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            $partPrompt = "Part {$i} of {$parts}: {$prompt}. Focus on building upon previous parts if applicable.";

            try {
                $post = $this->generateSinglePost($partPrompt, $author);

                // Add series metadata
                $post->update([
                    'series_id' => $seriesId,
                    'series_part' => $i,
                    'series_total_parts' => $parts,
                ]);

                $this->info("✅ Part {$i} created successfully!");
                $this->newLine();

                // Delay between parts
                if ($i < $parts) {
                    $this->info("⏳ Waiting 5 seconds before generating next part...");
                    sleep(5);
                    $this->newLine();
                }
            } catch (\Exception $e) {
                $this->error("Failed to generate part {$i}: " . $e->getMessage());
                return self::FAILURE;
            }
        }

        $this->info("🎉 Series generation complete!");
        $this->info("   {$parts} posts created");
        $this->info("   Series ID: {$seriesId}");

        return self::SUCCESS;
    }
}
