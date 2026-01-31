<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\DigitalProduct;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VerifyAILearningSetupCommand extends Command
{
    protected $signature = 'ai-learning:verify-setup';
    protected $description = 'Verify AI Learning platform setup and configuration';

    public function handle(): int
    {
        $this->newLine();
        $this->info('🔍 Verifying AI Learning & Tutorials Platform Setup...');
        $this->newLine();

        $checks = [
            'database' => $this->checkDatabase(),
            'tables' => $this->checkTables(),
            'models' => $this->checkModels(),
            'config' => $this->checkConfiguration(),
            'storage' => $this->checkStorage(),
            'scheduler' => $this->checkScheduler(),
            'routes' => $this->checkRoutes(),
            'environment' => $this->checkEnvironment(),
        ];

        $this->newLine();
        $this->printSummary($checks);
        $this->newLine();

        return collect($checks)->contains(false) ? self::FAILURE : self::SUCCESS;
    }

    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
            return true;
        } catch (\Exception $e) {
            $this->error('❌ Database connection: FAILED - ' . $e->getMessage());
            return false;
        }
    }

    protected function checkTables(): bool
    {
        $tables = [
            'digital_products' => 'Digital Products table',
            'product_purchases' => 'Product Purchases table',
            'categories' => 'Categories table',
            'tags' => 'Tags table',
        ];

        $allExist = true;

        foreach ($tables as $table => $label) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $this->info("✅ $label: EXISTS");
            } else {
                $this->error("❌ $label: MISSING - Run: php artisan migrate");
                $allExist = false;
            }
        }

        return $allExist;
    }

    protected function checkModels(): bool
    {
        $checks = [
            'DigitalProduct' => DigitalProduct::class,
            'ProductPurchase' => \App\Models\ProductPurchase::class,
            'Post (Series support)' => Post::class,
        ];

        $allOk = true;

        foreach ($checks as $name => $model) {
            try {
                class_exists($model);
                $this->info("✅ Model $name: OK");
            } catch (\Exception $e) {
                $this->error("❌ Model $name: MISSING");
                $allOk = false;
            }
        }

        return $allOk;
    }

    protected function checkConfiguration(): bool
    {
        try {
            $config = config('ai-learning');

            if (!$config) {
                $this->error('❌ Configuration: config/ai-learning.php not found');
                return false;
            }

            $this->info('✅ Configuration: OK');

            // Check topics
            $topics = $config['tutorial_topics'] ?? [];
            $topicCount = collect($topics)->sum(fn($items) => is_array($items) ? count($items) : 0);
            $this->line("   📚 Tutorial topics configured: $topicCount");

            // Check schedule
            $schedule = $config['weekly_schedule'] ?? [];
            $this->line("   📅 Weekly schedule: " . count($schedule) . " days");

            return true;
        } catch (\Exception $e) {
            $this->error('❌ Configuration: ERROR - ' . $e->getMessage());
            return false;
        }
    }

    protected function checkStorage(): bool
    {
        try {
            // Check if private disk is configured
            $disks = config('filesystems.disks');

            if (!isset($disks['private'])) {
                $this->error('❌ Storage: Private disk not configured in config/filesystems.php');
                return false;
            }

            $this->info('✅ Storage: Private disk configured');

            // Check if directory is writable
            $path = storage_path('app/private');
            if (!is_writable($path)) {
                $this->warn("⚠️  Storage path not writable: $path");
                $this->line("   Run: chmod -R 775 storage/app/private");
            }

            return true;
        } catch (\Exception $e) {
            $this->error('❌ Storage: ERROR - ' . $e->getMessage());
            return false;
        }
    }

    protected function checkScheduler(): bool
    {
        try {
            // Check if kernel has scheduled commands
            $commands = [
                'ai-learning:generate-weekly',
                'ai-learning:generate-prompts',
            ];

            $kernelPath = app_path('Console/Kernel.php');
            $kernelContent = file_get_contents($kernelPath);

            $allFound = true;
            foreach ($commands as $cmd) {
                if (strpos($kernelContent, $cmd) !== false) {
                    $this->info("✅ Scheduler: $cmd registered");
                } else {
                    $this->error("❌ Scheduler: $cmd NOT registered");
                    $allFound = false;
                }
            }

            return $allFound;
        } catch (\Exception $e) {
            $this->error('❌ Scheduler: ERROR - ' . $e->getMessage());
            return false;
        }
    }

    protected function checkRoutes(): bool
    {
        try {
            $routePath = base_path('routes/web.php');
            $routeContent = file_get_contents($routePath);

            $requiredRoutes = [
                '/resources' => 'Marketplace listing',
                'digital-products' => 'Digital products routes',
                'product:slug' => 'Product slug binding',
            ];

            $allFound = true;
            foreach ($requiredRoutes as $route => $label) {
                if (strpos($routeContent, $route) !== false) {
                    $this->info("✅ Routes: $label configured");
                } else {
                    $this->error("❌ Routes: $label NOT configured");
                    $allFound = false;
                }
            }

            return $allFound;
        } catch (\Exception $e) {
            $this->error('❌ Routes: ERROR - ' . $e->getMessage());
            return false;
        }
    }

    protected function checkEnvironment(): bool
    {
        $checks = [
            'AI_LEARNING_ENABLED' => 'AI Learning enabled',
            'TUTORIAL_GENERATION_ENABLED' => 'Tutorial generation enabled',
            'PROMPT_LIBRARY_ENABLED' => 'Prompt library enabled',
        ];

        $allOk = true;

        foreach ($checks as $env => $label) {
            $value = env($env, false);
            if ($value) {
                $this->info("✅ Environment: $label");
            } else {
                $this->warn("⚠️  Environment: $label NOT enabled");
            }
        }

        // Check AI providers
        $hasAI = env('OPENAI_API_KEY') || env('GROQ_API_KEY');
        if ($hasAI) {
            $this->info('✅ Environment: AI provider configured');
        } else {
            $this->error('❌ Environment: No AI provider found (OPENAI_API_KEY or GROQ_API_KEY)');
            $allOk = false;
        }

        return $allOk;
    }

    protected function printSummary(array $checks): void
    {
        $passed = collect($checks)->filter()->count();
        $total = count($checks);
        $percentage = round(($passed / $total) * 100);

        $this->newLine();

        if ($percentage === 100) {
            $this->info('════════════════════════════════════════════');
            $this->info('✅ ALL CHECKS PASSED - System Ready!');
            $this->info('════════════════════════════════════════════');
            $this->newLine();
            $this->line('Next steps:');
            $this->line('1. Create sample products: <fg=cyan>php artisan ai-learning:create-samples --count=10</>');
            $this->line('2. Start the scheduler: <fg=cyan>php artisan schedule:work</>');
            $this->line('3. Visit marketplace: <fg=cyan>http://localhost:9070/resources</>');
        } else {
            $this->warn('════════════════════════════════════════════');
            $this->warn("⚠️  {$passed}/{$total} checks passed ({$percentage}%)");
            $this->warn('════════════════════════════════════════════');
            $this->newLine();
            $this->line('Issues found:');
            $this->line('1. Run migrations: <fg=cyan>php artisan migrate</>');
            $this->line('2. Seed data: <fg=cyan>php artisan db:seed --class=AILearningCategoriesAndTagsSeeder</>');
            $this->line('3. Fix permissions: <fg=cyan>chmod -R 775 storage</>');
        }

        $this->newLine();

        // Data summary if database is working
        if (DB::connection()->getPdo()) {
            $this->printDataSummary();
        }
    }

    protected function printDataSummary(): void
    {
        try {
            $this->line('📊 Current Data:');

            $categoryCount = Category::count();
            $this->line("   Categories: <fg=cyan>$categoryCount</>");

            $tagCount = Tag::count();
            $this->line("   Tags: <fg=cyan>$tagCount</>");

            if (DB::getSchemaBuilder()->hasTable('digital_products')) {
                $productCount = DigitalProduct::count();
                $publishedCount = DigitalProduct::where('status', 'published')->count();
                $this->line("   Digital Products: <fg=cyan>$productCount</> (Published: <fg=cyan>$publishedCount</>)");
            }

            if (DB::getSchemaBuilder()->hasTable('product_purchases')) {
                $purchaseCount = \App\Models\ProductPurchase::count();
                $completedCount = \App\Models\ProductPurchase::where('status', 'completed')->count();
                $this->line("   Purchases: <fg=cyan>$purchaseCount</> (Completed: <fg=cyan>$completedCount</>)");
            }

            if (DB::getSchemaBuilder()->hasTable('posts')) {
                $seriesCount = Post::whereNotNull('series_slug')->distinct('series_slug')->count('series_slug');
                $this->line("   Tutorial Series: <fg=cyan>$seriesCount</>");
            }
        } catch (\Exception $e) {
            // Silently fail - database might not be fully migrated yet
        }
    }
}
