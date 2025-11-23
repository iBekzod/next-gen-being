<?php

namespace App\Console\Commands;

use App\Services\AITutorialGenerationService;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ScheduledTutorialGeneration extends Command
{
    protected $signature = 'tutorials:scheduled {--skip-cache : Skip cache check and force generation}';

    protected $description = 'Generate tutorials on a scheduled basis (called by kernel scheduler)';

    /**
     * Tutorial topics to generate on rotation
     */
    private $tutorialTopics = [
        [
            'topic' => 'Building a Production-Grade E-Commerce Platform with Laravel 12, Stripe, and Kubernetes',
            'parts' => 8,
            'description' => 'Complete guide to building scalable e-commerce with modern stack',
        ],
        [
            'topic' => 'Advanced REST API Development with Laravel 12, OpenAPI, and GraphQL Integration',
            'parts' => 8,
            'description' => 'Production-ready API patterns and best practices',
        ],
        [
            'topic' => 'Building Real-Time Applications with Laravel, WebSockets, and Vue 3',
            'parts' => 5,
            'description' => 'Real-time features, notifications, and live updates',
        ],
        [
            'topic' => 'Microservices Architecture with Laravel and Kubernetes: Complete Implementation',
            'parts' => 8,
            'description' => 'Scaling Laravel applications with microservices',
        ],
        [
            'topic' => 'Advanced Testing Strategy: Unit, Integration, E2E Tests with Laravel',
            'parts' => 5,
            'description' => 'Comprehensive testing patterns and automation',
        ],
        [
            'topic' => 'Security Hardening: OWASP Top 10 Prevention in Laravel Applications',
            'parts' => 5,
            'description' => 'Security best practices and vulnerability prevention',
        ],
        [
            'topic' => 'Performance Optimization: Database, Caching, and Infrastructure Tuning',
            'parts' => 5,
            'description' => 'Achieving 99.99% uptime and sub-100ms response times',
        ],
        [
            'topic' => 'Building a SaaS Platform with Laravel: Multi-Tenancy, Billing, and Scaling',
            'parts' => 8,
            'description' => 'Complete SaaS implementation with subscription management',
        ],
        [
            'topic' => 'AI Integration in Laravel: Building Intelligent Applications with Claude and OpenAI',
            'parts' => 5,
            'description' => 'Leveraging AI APIs for smart features',
        ],
        [
            'topic' => 'DevOps Mastery: Complete CI/CD Pipeline, Monitoring, and Disaster Recovery',
            'parts' => 8,
            'description' => 'Production deployment and operational excellence',
        ],
    ];

    public function handle()
    {
        $cacheKey = 'tutorials:last_generation';
        $skipCache = $this->option('skip-cache');

        // Check if we've generated recently (prevent duplicate generations)
        if (!$skipCache && Cache::has($cacheKey)) {
            $this->warn('⏭️  Skipping: Tutorial generated recently. Use --skip-cache to force.');
            return 0;
        }

        try {
            // Select next topic from rotation
            $topic = $this->selectNextTopic();

            if (!$topic) {
                $this->warn('⚠️  No topics available for generation');
                return 1;
            }

            $this->info("🤖 Starting automated tutorial generation");
            $this->line("Topic: {$topic['topic']}");
            $this->line("Parts: {$topic['parts']}");
            $this->newLine();

            // Generate tutorial series
            $service = new AITutorialGenerationService();
            $posts = $service->generateComprehensiveSeries(
                topic: $topic['topic'],
                parts: $topic['parts'],
                publish: false // Always save as draft for review
            );

            $this->newLine();
            $this->info("✨ Tutorial series generated successfully!");
            $this->line("Created " . count($posts) . " posts");

            foreach ($posts as $post) {
                $this->line("  ✓ {$post->title}");
                $this->line("    Status: {$post->status}");
            }

            // Cache the generation time (prevent duplicate runs within 24 hours)
            Cache::put($cacheKey, now(), now()->addHours(24));

            // Log for monitoring
            Log::info('Scheduled tutorial generation completed', [
                'topic' => $topic['topic'],
                'posts_created' => count($posts),
                'parts' => $topic['parts'],
            ]);

            // Send notification
            $this->notifyAdmins($topic, $posts);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Generation failed: {$e->getMessage()}");

            Log::error('Scheduled tutorial generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }

    /**
     * Select the next topic based on round-robin rotation
     */
    private function selectNextTopic(): ?array
    {
        $rotationKey = 'tutorials:rotation_index';
        $currentIndex = Cache::get($rotationKey, 0);

        // Get next topic
        if ($currentIndex >= count($this->tutorialTopics)) {
            $currentIndex = 0; // Reset rotation
        }

        $topic = $this->tutorialTopics[$currentIndex] ?? null;

        // Store next index for next run
        Cache::put($rotationKey, $currentIndex + 1, now()->addDays(30));

        return $topic;
    }

    /**
     * Notify admins of generated tutorials
     */
    private function notifyAdmins(array $topic, array $posts): void
    {
        $adminEmails = \App\Models\User::where('role', 'admin')
            ->pluck('email')
            ->toArray();

        if (empty($adminEmails)) {
            return;
        }

        foreach ($adminEmails as $email) {
            // Optional: Send email notification
            // Mail::to($email)->send(new TutorialGeneratedNotification($topic, $posts));
        }
    }
}
