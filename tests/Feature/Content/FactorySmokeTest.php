<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactorySmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_fabrikasi_ishlaydi(): void
    {
        $post = Post::factory()->create();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
        $this->assertSame('approved', $post->moderation_status);
        $this->assertNotNull($post->category_id);
        $this->assertNotNull($post->author_id);
    }
}
