<?php

namespace App\Services\AI;

use App\Models\LearningPath;
use App\Models\LearningPathItem;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;

class AILearningPlanGenerator
{
    /**
     * Generate a personalized learning plan for a user
     */
    public function generateLearningPlan(
        User $user,
        string $goal,
        string $skillLevel = 'beginner',
        int $estimatedHours = 10
    ): LearningPath {
        // Create the learning path
        $learningPath = LearningPath::create([
            'user_id' => $user->id,
            'name' => $this->generatePlanName($goal, $skillLevel),
            'description' => $this->generatePlanDescription($goal),
            'goal' => $goal,
            'skill_level' => $skillLevel,
            'estimated_duration_hours' => $estimatedHours,
            'status' => 'draft',
            'ai_generated' => true,
            'generated_at' => now(),
            'metadata' => [
                'generated_at' => now(),
                'generation_method' => 'ai',
                'user_skill_level' => $skillLevel,
            ],
        ]);

        // Get relevant posts for the learning path
        $posts = $this->selectRelevantPosts($goal, $skillLevel, $estimatedHours);

        // Create learning path items
        $this->createPathItems($learningPath, $posts);

        return $learningPath;
    }

    /**
     * Generate a learning plan name
     */
    protected function generatePlanName(string $goal, string $skillLevel): string
    {
        $levelLabel = match($skillLevel) {
            'beginner' => 'Beginner\'s',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            'expert' => 'Expert',
            default => 'Custom',
        };

        // Capitalize goal
        $goalLabel = ucfirst($goal);

        return "{$levelLabel} {$goalLabel} Learning Path";
    }

    /**
     * Generate a learning plan description
     */
    protected function generatePlanDescription(string $goal): string
    {
        $descriptions = [
            'web_development' => 'Master the fundamentals and advanced techniques of modern web development.',
            'backend' => 'Learn server-side development, APIs, and database management.',
            'frontend' => 'Build interactive and responsive user interfaces.',
            'database' => 'Understand data design, SQL, and database optimization.',
            'devops' => 'Learn deployment, containerization, and infrastructure.',
            'security' => 'Master cybersecurity principles and best practices.',
            'mobile' => 'Build native and cross-platform mobile applications.',
            'ai' => 'Explore artificial intelligence and machine learning.',
            'testing' => 'Become proficient in software testing strategies.',
            'soft_skills' => 'Develop communication and leadership abilities.',
        ];

        return $descriptions[strtolower(str_replace(' ', '_', $goal))]
            ?? "A comprehensive learning path to master {$goal}.";
    }

    /**
     * Select relevant posts for the learning plan
     */
    protected function selectRelevantPosts(string $goal, string $skillLevel, int $estimatedHours): Collection
    {
        $query = Post::query();

        // Filter by difficulty
        $difficultyMap = [
            'beginner' => ['beginner'],
            'intermediate' => ['beginner', 'intermediate'],
            'advanced' => ['intermediate', 'advanced'],
            'expert' => ['advanced', 'expert'],
        ];

        $difficulties = $difficultyMap[$skillLevel] ?? ['beginner'];
        $query->whereIn('difficulty', $difficulties);

        // Filter by goal/category (basic implementation)
        $goalKeywords = explode('_', strtolower($goal));
        foreach ($goalKeywords as $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw("LOWER(title) LIKE ?", ["%{$keyword}%"])
                  ->orWhereRaw("LOWER(content) LIKE ?", ["%{$keyword}%"]);
            });
        }

        // Get posts and estimate time
        $totalMinutes = $estimatedHours * 60;
        $posts = $query->where('published', true)
            ->orderBy('created_at', 'asc')
            ->get()
            ->take(intval(ceil($totalMinutes / 10))); // Assume ~10 min per post

        // If not enough posts, get broader selection
        if ($posts->count() < 5) {
            $posts = Post::where('difficulty', $skillLevel)
                ->where('published', true)
                ->orderBy('created_at', 'asc')
                ->limit(intval(ceil($totalMinutes / 10)))
                ->get();
        }

        return $posts;
    }

    /**
     * Create learning path items
     */
    protected function createPathItems(LearningPath $learningPath, Collection $posts): void
    {
        $order = 1;
        $totalMinutes = 0;

        foreach ($posts as $post) {
            $estimatedMinutes = $post->read_time_minutes ?? 10;
            $totalMinutes += $estimatedMinutes;

            LearningPathItem::create([
                'learning_path_id' => $learningPath->id,
                'post_id' => $post->id,
                'order' => $order,
                'title' => $post->title,
                'description' => $post->excerpt ?? $post->meta_description,
                'reason_for_recommendation' => $this->generateReason($order, $post),
                'difficulty_level' => $post->difficulty,
                'estimated_duration_minutes' => $estimatedMinutes,
                'metadata' => [
                    'category' => $post->category?->name,
                    'series' => $post->series_slug,
                ],
            ]);

            $order++;
        }

        // Update estimated duration
        $learningPath->update([
            'estimated_duration_hours' => round($totalMinutes / 60, 1),
        ]);
    }

    /**
     * Generate reason for including post
     */
    protected function generateReason(int $order, Post $post): string
    {
        if ($order === 1) {
            return 'Start with this foundational topic.';
        } elseif ($order <= 3) {
            return 'Build your core knowledge with this essential topic.';
        } elseif ($order <= 5) {
            return 'Expand your understanding with intermediate concepts.';
        }

        return 'Master advanced topics and best practices.';
    }

    /**
     * Get recommended learning paths for a user
     */
    public function getRecommendedTopics(User $user): Collection
    {
        $topics = [
            [
                'name' => 'Web Development',
                'goal' => 'web_development',
                'description' => 'Learn HTML, CSS, JavaScript, and modern frameworks.',
                'icon' => '🌐',
                'estimated_hours' => 30,
            ],
            [
                'name' => 'Backend Development',
                'goal' => 'backend',
                'description' => 'Master server-side programming and APIs.',
                'icon' => '⚙️',
                'estimated_hours' => 25,
            ],
            [
                'name' => 'Database Management',
                'goal' => 'database',
                'description' => 'Learn SQL, database design, and optimization.',
                'icon' => '🗄️',
                'estimated_hours' => 20,
            ],
            [
                'name' => 'Frontend Mastery',
                'goal' => 'frontend',
                'description' => 'Build beautiful, responsive user interfaces.',
                'icon' => '🎨',
                'estimated_hours' => 28,
            ],
            [
                'name' => 'DevOps & Deployment',
                'goal' => 'devops',
                'description' => 'Master deployment, Docker, and CI/CD.',
                'icon' => '🚀',
                'estimated_hours' => 22,
            ],
            [
                'name' => 'Security Basics',
                'goal' => 'security',
                'description' => 'Learn security principles and best practices.',
                'icon' => '🔒',
                'estimated_hours' => 18,
            ],
        ];

        return collect($topics);
    }

    /**
     * Get learning plans for a user
     */
    public function getUserLearningPlans(User $user): Collection
    {
        return $user->learningPaths()->orderByDesc('created_at')->get();
    }

    /**
     * Get active learning plan for a user
     */
    public function getActiveLearningPlan(User $user): ?LearningPath
    {
        return $user->learningPaths()
            ->where('status', 'active')
            ->latest()
            ->first();
    }

    /**
     * Get next item in learning plan
     */
    public function getNextItem(LearningPath $learningPath): ?LearningPathItem
    {
        return $learningPath->getNextItem();
    }

    /**
     * Calculate plan completion percentage
     */
    public function getCompletionPercentage(LearningPath $learningPath): float
    {
        return $learningPath->getProgressPercentage();
    }

    /**
     * Get learning plan statistics
     */
    public function getPlanStatistics(LearningPath $learningPath): array
    {
        $items = $learningPath->items;
        $completedItems = $items->where('completed', true)->count();
        $totalItems = $items->count();

        return [
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
            'pending_items' => $totalItems - $completedItems,
            'completion_percentage' => $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0,
            'estimated_total_hours' => $learningPath->estimated_duration_hours,
            'estimated_remaining_hours' => round(
                ($learningPath->estimated_duration_hours / max($totalItems, 1)) * ($totalItems - $completedItems),
                1
            ),
        ];
    }
}
