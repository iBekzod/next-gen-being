<?php

namespace Database\Factories;

use App\Models\DigitalProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DigitalProductFactory extends Factory
{
    protected $model = DigitalProduct::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        return [
            'creator_id'               => User::factory(),
            'title'                    => ucfirst($title),
            'slug'                     => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'description'              => $this->faker->paragraph(),
            'type'                     => 'template',
            'price'                    => $this->faker->randomElement([5, 7, 49]),
            'is_free'                  => false,
            'status'                   => 'published',
            'published_at'             => now(),
            'revenue_share_percentage' => 70.00,
            'lemonsqueezy_variant_id'  => (string) $this->faker->numberBetween(1000000, 1999999),
        ];
    }

    /** A marketplace tier: 90/10 split, linked tier semantics. */
    public function tier(string $tier, float $price): static
    {
        return $this->state(fn () => [
            'tier'                     => $tier,
            'price'                    => $price,
            'revenue_share_percentage' => DigitalProduct::MARKETPLACE_REVENUE_SHARE,
        ]);
    }
}
