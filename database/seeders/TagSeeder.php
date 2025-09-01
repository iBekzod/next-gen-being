<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Laravel', 'PHP', 'JavaScript', 'React', 'Vue.js', 'Node.js',
            'Python', 'Django', 'FastAPI', 'TypeScript', 'Next.js', 'Nuxt.js',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'Terraform',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Elasticsearch',
            'Machine Learning', 'Deep Learning', 'TensorFlow', 'PyTorch',
            'API', 'REST', 'GraphQL', 'Microservices', 'Serverless',
            'Testing', 'Unit Testing', 'Integration Testing', 'TDD', 'BDD',
            'Security', 'Authentication', 'Authorization', 'OAuth', 'JWT',
            'Performance', 'Optimization', 'Caching', 'CDN', 'Load Balancing',
            'Git', 'GitHub', 'GitLab', 'CI/CD', 'Jenkins', 'GitHub Actions',
            'Frontend', 'Backend', 'Full Stack', 'Mobile', 'iOS', 'Android',
            'React Native', 'Flutter', 'Swift', 'Kotlin', 'Java',
            'HTML', 'CSS', 'Sass', 'Tailwind CSS', 'Bootstrap',
            'Webpack', 'Vite', 'Rollup', 'Babel', 'ESLint', 'Prettier',
            'Database Design', 'SQL', 'NoSQL', 'Data Modeling',
            'Algorithms', 'Data Structures', 'System Design', 'Architecture',
        ];

        foreach ($tags as $tagName) {
            Tag::updateOrCreate(
                ['name' => $tagName],
                [
                    'slug' => \Str::slug($tagName),
                    'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                ]
            );
        }
    }
}
