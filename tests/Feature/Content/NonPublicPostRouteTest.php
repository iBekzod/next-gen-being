<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task #145: draft and archived posts must not be readable by slug.
 * (Before this, only the sitemap and listings hid them; /posts/{slug} was 200.)
 */
class NonPublicPostRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_post_is_public(): void
    {
        $p = Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);

        $this->get('/posts/' . $p->slug)->assertOk();
    }

    public function test_draft_and_archived_posts_are_404_for_guests(): void
    {
        foreach (['draft', 'archived'] as $status) {
            $p = Post::factory()->create(['status' => $status]);

            $this->get('/posts/' . $p->slug)->assertNotFound();
        }
    }

    public function test_author_can_still_preview_own_draft(): void
    {
        $p = Post::factory()->create(['status' => 'draft']);

        $this->actingAs($p->author)->get('/posts/' . $p->slug)->assertOk();
    }
}
