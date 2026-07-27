<?php

namespace Database\Factories;

use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MarketplaceListingFactory extends Factory
{
    protected $model = MarketplaceListing::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'seller_id'    => User::factory(),
            'title'        => ucfirst($title),
            'slug'         => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'tagline'      => $this->faker->sentence(6),
            'description'  => $this->faker->paragraph(),
            'category'     => $this->faker->randomElement(['saas', 'landing', 'template']),
            'tags'         => ['laravel', 'alpine'],
            'demo_type'    => 'none',
            'status'       => 'published',
            'published_at' => now(),
        ];
    }
}
