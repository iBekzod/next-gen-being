<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Rising Star',
                'description' => 'Welcome to the platform! New member.',
                'slug' => 'rising-star',
                'icon' => '⭐',
                'color' => 'blue',
                'order' => 1,
                'requirements' => null, // Automatic on first post
            ],
            [
                'name' => 'Prolific Writer',
                'description' => 'Published 10 blog posts.',
                'slug' => 'prolific-writer',
                'icon' => '✍️',
                'color' => 'green',
                'order' => 2,
                'requirements' => ['posts_published' => 10],
            ],
            [
                'name' => 'Engaging Author',
                'description' => 'Your posts have received 100+ likes.',
                'slug' => 'engaging-author',
                'icon' => '❤️',
                'color' => 'red',
                'order' => 3,
                'requirements' => ['posts_liked' => 100],
            ],
            [
                'name' => 'Popular Creator',
                'description' => 'Reached 100 followers.',
                'slug' => 'popular-creator',
                'icon' => '👥',
                'color' => 'purple',
                'order' => 4,
                'requirements' => ['followers_count' => 100],
            ],
            [
                'name' => 'Top Contributor',
                'description' => 'Achieved expert level with 1500+ reputation points.',
                'slug' => 'top-contributor',
                'icon' => '🏆',
                'color' => 'orange',
                'order' => 5,
                'requirements' => ['points' => 1500],
            ],
            [
                'name' => 'Influencer',
                'description' => 'Reached 500 followers.',
                'slug' => 'influencer',
                'icon' => '📢',
                'color' => 'indigo',
                'order' => 6,
                'requirements' => ['followers_count' => 500],
            ],
            [
                'name' => 'Thought Leader',
                'description' => 'Received 500+ comments on your posts.',
                'slug' => 'thought-leader',
                'icon' => '💡',
                'color' => 'yellow',
                'order' => 7,
                'requirements' => ['comments_received' => 500],
            ],
            [
                'name' => 'Community Pillar',
                'description' => 'Reached 1000 followers - you\'re a community pillar.',
                'slug' => 'community-pillar',
                'icon' => '🌟',
                'color' => 'rose',
                'order' => 8,
                'requirements' => ['followers_count' => 1000],
            ],
            [
                'name' => 'Legend',
                'description' => 'Achieved legendary status with 5000+ reputation points.',
                'slug' => 'legend',
                'icon' => '👑',
                'color' => 'red',
                'order' => 9,
                'requirements' => ['points' => 5000],
            ],
            [
                'name' => 'Helpful Commenter',
                'description' => 'Your comments received 200+ likes.',
                'slug' => 'helpful-commenter',
                'icon' => '💬',
                'color' => 'cyan',
                'order' => 10,
                'requirements' => ['engagement_score' => 200],
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }
    }
}
