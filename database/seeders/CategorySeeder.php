<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Frontend and backend web development tutorials and tips',
                'color' => '#3b82f6',
                'icon' => 'code',
            ],
            [
                'name' => 'Artificial Intelligence',
                'slug' => 'artificial-intelligence',
                'description' => 'AI, machine learning, and deep learning content',
                'color' => '#8b5cf6',
                'icon' => 'cpu',
            ],
            [
                'name' => 'Mobile Development',
                'slug' => 'mobile-development',
                'description' => 'iOS, Android, and cross-platform mobile development',
                'color' => '#10b981',
                'icon' => 'device-mobile',
            ],
            [
                'name' => 'DevOps',
                'slug' => 'devops',
                'description' => 'DevOps practices, CI/CD, and infrastructure management',
                'color' => '#f59e0b',
                'icon' => 'server',
            ],
            [
                'name' => 'Data Science',
                'slug' => 'data-science',
                'description' => 'Data analysis, visualization, and data engineering',
                'color' => '#ef4444',
                'icon' => 'chart-bar',
            ],
            [
                'name' => 'Career & Industry',
                'slug' => 'career-industry',
                'description' => 'Career advice, industry trends, and professional development',
                'color' => '#6366f1',
                'icon' => 'briefcase',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }
    }
}
