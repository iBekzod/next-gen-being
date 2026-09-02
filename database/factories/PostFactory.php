<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(1, 999999),
            'excerpt' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'status' => 'draft',
            // Ustun default qiymati 'pending'. Uni ochiq 'approved' qilamiz,
            // aks holda har bir test posti moderatsiya darvozasida yiqiladi.
            'moderation_status' => 'approved',
            'series_title' => null,
            'published_at' => null,
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
