<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'color',
        'category',
        'points',
        'conditions',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    // Relationships
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot('achieved_at')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Static achievement definitions
    public static function createDefaultAchievements()
    {
        $achievements = [
            [
                'slug' => 'first-step',
                'name' => 'First Step',
                'description' => 'Complete your first tutorial part',
                'icon' => '👣',
                'color' => 'blue',
                'category' => 'learning',
                'points' => 10,
                'conditions' => ['completed_parts' => 1],
            ],
            [
                'slug' => 'series-starter',
                'name' => 'Series Starter',
                'description' => 'Start a complete tutorial series',
                'icon' => '🚀',
                'color' => 'purple',
                'category' => 'learning',
                'points' => 25,
                'conditions' => ['completed_parts' => 5],
            ],
            [
                'slug' => 'series-master',
                'name' => 'Series Master',
                'description' => 'Complete an entire tutorial series',
                'icon' => '👑',
                'color' => 'amber',
                'category' => 'learning',
                'points' => 100,
                'conditions' => ['completed_series' => 1],
            ],
            [
                'slug' => 'quick-learner',
                'name' => 'Quick Learner',
                'description' => 'Complete 3 parts in one day',
                'icon' => '⚡',
                'color' => 'yellow',
                'category' => 'engagement',
                'points' => 50,
                'conditions' => ['parts_same_day' => 3],
            ],
            [
                'slug' => 'dedicated-student',
                'name' => 'Dedicated Student',
                'description' => 'Read for 5+ hours total',
                'icon' => '📚',
                'color' => 'emerald',
                'category' => 'engagement',
                'points' => 75,
                'conditions' => ['total_hours' => 5],
            ],
            [
                'slug' => 'polymath',
                'name' => 'Polymath',
                'description' => 'Complete 5 different tutorial series',
                'icon' => '🎓',
                'color' => 'indigo',
                'category' => 'milestone',
                'points' => 200,
                'conditions' => ['completed_series' => 5],
            ],
            [
                'slug' => 'knowledge-seeker',
                'name' => 'Knowledge Seeker',
                'description' => 'Complete 10 tutorial parts',
                'icon' => '🔍',
                'color' => 'cyan',
                'category' => 'learning',
                'points' => 50,
                'conditions' => ['completed_parts' => 10],
            ],
        ];

        foreach ($achievements as $achievement) {
            static::firstOrCreate(['slug' => $achievement['slug']], $achievement);
        }
    }
}
