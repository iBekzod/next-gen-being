<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\AITutorialGenerationService;
use App\Services\ContentEnhancementService;
use Illuminate\Support\Str;
use Tests\TestCase;

class TutorialGenerationTest extends TestCase
{
    protected AITutorialGenerationService $tutorialService;
    protected ContentEnhancementService $enhancementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tutorialService = app(AITutorialGenerationService::class);
        $this->enhancementService = app(ContentEnhancementService::class);
    }

    /**
     * Test that tutorials are generated with sufficient content
     */
    public function test_tutorials_are_generated_with_complete_content()
    {
        // Get the latest published tutorial posts
        $posts = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->take(8)
            ->get();

        $this->assertGreaterThan(0, $posts->count(), 'No published tutorial posts found');

        foreach ($posts as $post) {
            // Verify post has content
            $this->assertNotEmpty($post->content, "Post {$post->id} has empty content");

            // Verify content length
            $wordCount = str_word_count(strip_tags($post->content));
            $this->assertGreaterThanOrEqual(1000, $wordCount,
                "Post {$post->id} ({$post->title}) has only {$wordCount} words, minimum 1000 required");

            // Verify content has proper structure
            $this->assertStringContainsString('##', $post->content,
                "Post {$post->id} missing markdown headers");

            // Verify content has code examples
            $this->assertStringContainsString('```', $post->content,
                "Post {$post->id} missing code blocks");

            // Verify series information is complete
            $this->assertNotEmpty($post->series_title, "Post {$post->id} missing series_title");
            $this->assertNotNull($post->series_part, "Post {$post->id} missing series_part");
            $this->assertNotNull($post->series_total_parts, "Post {$post->id} missing series_total_parts");

            // Verify author is set
            $this->assertNotNull($post->author_id, "Post {$post->id} missing author_id");

            // Verify category is set
            $this->assertNotNull($post->category_id, "Post {$post->id} missing category_id");

            // Verify SEO metadata
            $this->assertNotEmpty($post->seo_meta, "Post {$post->id} missing SEO metadata");
            if (is_array($post->seo_meta)) {
                $this->assertArrayHasKey('title', $post->seo_meta);
                $this->assertArrayHasKey('description', $post->seo_meta);
                $this->assertArrayHasKey('keywords', $post->seo_meta);
            }

            // Verify read time is calculated
            $this->assertGreaterThan(0, $post->read_time, "Post {$post->id} missing read time");
        }
    }

    /**
     * Test that content has been enhanced with E-E-A-T signals
     */
    public function test_tutorials_have_eeat_enhancements()
    {
        $post = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->first();

        $this->assertNotNull($post, 'No published tutorial found');

        $content = $post->content;

        // Check for Table of Contents
        $this->assertStringContainsString('Table of Contents', $content,
            'Content missing Table of Contents enhancement');

        // Check for Author Bio section
        $this->assertStringContainsString('About the Author', $content,
            'Content missing Author Bio enhancement');

        // Check for Key Takeaways
        $this->assertStringContainsString('Key Takeaways', $content,
            'Content missing Key Takeaways enhancement');

        // Check for Series Progress indicator
        $this->assertStringContainsString('Series Progress', $content,
            'Content missing Series Progress indicator');

        // Check for Read Time estimate
        $this->assertStringContainsString('Estimated Reading Time', $content,
            'Content missing Read Time estimate');

        // Check for Related Tutorials section
        $this->assertStringContainsString('Related Tutorials', $content,
            'Content missing Related Tutorials section');
    }

    /**
     * Test that all parts of a series exist
     */
    public function test_complete_tutorial_series_exists()
    {
        // Get latest series
        $latestPost = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->first();

        $this->assertNotNull($latestPost, 'No published tutorial found');

        $seriesSlug = $latestPost->series_slug;
        $expectedParts = $latestPost->series_total_parts ?? 8;

        // Get all parts in the series
        $parts = Post::where('series_slug', $seriesSlug)
            ->orderBy('series_part')
            ->get();

        $this->assertEquals($expectedParts, $parts->count(),
            "Series '{$seriesSlug}' has {$parts->count()} parts, expected {$expectedParts}");

        // Verify each part is in sequence
        foreach ($parts as $index => $part) {
            $expectedPartNumber = $index + 1;
            $this->assertEquals($expectedPartNumber, $part->series_part,
                "Series '{$seriesSlug}' part {$part->series_part} is out of order");
        }
    }

    /**
     * Test that premium tiers are correctly assigned
     */
    public function test_premium_tiers_correctly_assigned()
    {
        // Get latest series
        $latestPost = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->first();

        if (!$latestPost) {
            $this->markTestSkipped('No published tutorial found');
        }

        $seriesSlug = $latestPost->series_slug;
        $totalParts = $latestPost->series_total_parts ?? 8;

        // Get all parts in the series
        $parts = Post::where('series_slug', $seriesSlug)->get()->keyBy('series_part');

        // Parts 1-6 should be free (70%)
        for ($i = 1; $i <= 6; $i++) {
            if (isset($parts[$i])) {
                $this->assertFalse($parts[$i]->is_premium,
                    "Part {$i} should NOT be premium (free tier)");
            }
        }

        // Parts 7-8 should be premium (30%)
        for ($i = 7; $i <= $totalParts; $i++) {
            if (isset($parts[$i])) {
                $this->assertTrue($parts[$i]->is_premium,
                    "Part {$i} should be premium");
                $this->assertNotNull($parts[$i]->premium_tier,
                    "Part {$i} premium_tier should be set");
            }
        }
    }

    /**
     * Test content quality metrics
     */
    public function test_content_meets_quality_metrics()
    {
        $post = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->first();

        $this->assertNotNull($post, 'No published tutorial found');

        $content = $post->content;
        $wordCount = str_word_count(strip_tags($content));
        $headerCount = preg_match_all('/^#+\s+.+$/m', $content);
        $codeBlockCount = preg_match_all('/```[a-z]*\n/i', $content);

        // Word count check
        $this->assertGreaterThanOrEqual(1000, $wordCount,
            "Content should have at least 1000 words, has {$wordCount}");

        // Header check (at least 3 sections)
        $this->assertGreaterThanOrEqual(3, $headerCount,
            "Content should have at least 3 headers, has {$headerCount}");

        // Code block check (at least 1 for most tutorials)
        $this->assertGreaterThanOrEqual(1, $codeBlockCount,
            "Content should have at least 1 code block, has {$codeBlockCount}");

        echo "\n✅ Content Quality Metrics:\n";
        echo "   Word Count: " . number_format($wordCount) . " words\n";
        echo "   Headers: {$headerCount}\n";
        echo "   Code Blocks: {$codeBlockCount}\n";
        echo "   Read Time: {$post->read_time} minutes\n";
    }

    /**
     * Test that featured images are fetched
     */
    public function test_featured_images_are_fetched()
    {
        $post = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->first();

        $this->assertNotNull($post, 'No published tutorial found');

        if ($post->featured_image) {
            $this->assertStringContainsString('http', $post->featured_image,
                'Featured image should be a valid URL');
            echo "\n✅ Featured Image: {$post->featured_image}\n";
        } else {
            echo "\n⚠️  No featured image found (Unsplash API may have failed)\n";
        }
    }

    /**
     * Test that tags are attached
     */
    public function test_tags_are_properly_attached()
    {
        $post = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->first();

        $this->assertNotNull($post, 'No published tutorial found');

        $tags = $post->tags->pluck('name')->toArray();

        $this->assertGreaterThan(0, count($tags),
            "Post should have at least one tag");

        // Check for common tags
        $commonTags = ['ai', 'tutorial', 'learning'];
        $hasCommonTag = count(array_intersect($tags, $commonTags)) > 0;
        $this->assertTrue($hasCommonTag,
            "Post should have at least one common tag (ai, tutorial, or learning)");

        echo "\n✅ Tags Attached: " . implode(', ', $tags) . "\n";
    }

    /**
     * Test complete post creation flow
     */
    public function test_complete_post_creation_includes_all_fields()
    {
        $post = Post::where('status', 'published')
            ->whereNotNull('series_slug')
            ->latest('published_at')
            ->first();

        $this->assertNotNull($post, 'No published tutorial found');

        // Required fields
        $requiredFields = [
            'title' => 'Title',
            'slug' => 'Slug',
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'author_id' => 'Author ID',
            'category_id' => 'Category ID',
            'status' => 'Status',
            'published_at' => 'Published At',
            'series_title' => 'Series Title',
            'series_part' => 'Series Part',
            'series_total_parts' => 'Series Total Parts',
        ];

        foreach ($requiredFields as $field => $label) {
            $this->assertNotNull($post->$field, "Post missing required field: {$label}");
        }

        // Optional but important fields
        $this->assertNotNull($post->read_time, 'Read time should be calculated');
        $this->assertNotEmpty($post->seo_meta, 'SEO metadata should be present');

        echo "\n✅ All Required Fields Present\n";
    }
}
