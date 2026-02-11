<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VerifyTutorialSetup extends Command
{
    protected $signature = 'tutorials:verify';
    protected $description = 'Verify the tutorial generation system is properly configured';

    public function handle(): int
    {
        $this->info("\n🔍 Tutorial Generation System Verification\n");
        $this->info(str_repeat("=", 60));

        $checks = [
            'API Key Configuration' => $this->checkApiKey(),
            'Database Connection' => $this->checkDatabase(),
            'Queue Configuration' => $this->checkQueueConfiguration(),
            'Cache System' => $this->checkCache(),
            'Post Model' => $this->checkPostModel(),
            'API Connectivity' => $this->checkApiConnectivity(),
            'Anthropic API' => $this->checkAnthropicApi(),
        ];

        $passed = 0;
        $failed = 0;

        foreach ($checks as $name => $result) {
            if ($result) {
                $this->line("✅ {$name}");
                $passed++;
            } else {
                $this->line("❌ {$name}");
                $failed++;
            }
        }

        $this->info("\n" . str_repeat("=", 60));
        $this->info("Results: {$passed} passed, {$failed} failed\n");

        if ($failed === 0) {
            $this->info("✨ All systems ready! You can now:");
            $this->line("  1. Start queue worker: php artisan queue:work");
            $this->line("  2. Start scheduler: php artisan schedule:work");
            $this->line("  3. Generate manually: http://localhost/admin/tutorial-generator");
            $this->line("  4. Check status: curl http://localhost/api/v1/tutorials/status\n");
            return Command::SUCCESS;
        } else {
            $this->error("\n⚠️  Fix the issues above before starting generation\n");
            return Command::FAILURE;
        }
    }

    private function checkApiKey(): bool
    {
        $key = config('services.anthropic.key');

        if (empty($key)) {
            $this->line("   └─ Missing ANTHROPIC_API_KEY in .env");
            return false;
        }

        if (!str_starts_with($key, 'sk-ant-')) {
            $this->line("   └─ API key format invalid (should start with sk-ant-)");
            return false;
        }

        $this->line("   └─ API key configured ✓");
        return true;
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            // Check if posts table exists
            if (!DB::getSchemaBuilder()->hasTable('posts')) {
                $this->line("   └─ posts table not found");
                return false;
            }

            // Check required columns
            $columns = DB::getSchemaBuilder()->getColumnListing('posts');
            $required = ['series_title', 'series_slug', 'series_part', 'series_total_parts'];

            foreach ($required as $column) {
                if (!in_array($column, $columns)) {
                    $this->line("   └─ Missing column: {$column}");
                    return false;
                }
            }

            $this->line("   └─ Database connected and schema valid ✓");
            return true;
        } catch (\Exception $e) {
            $this->line("   └─ Database error: " . $e->getMessage());
            return false;
        }
    }

    private function checkQueueConfiguration(): bool
    {
        try {
            $queueDriver = config('queue.default');

            if ($queueDriver === 'sync') {
                $this->line("   └─ ⚠️  Using SYNC queue (blocking, not recommended for production)");
                $this->line("       Consider changing QUEUE_CONNECTION in .env");
            }

            $this->line("   └─ Queue driver: {$queueDriver} ✓");
            return true;
        } catch (\Exception $e) {
            $this->line("   └─ Queue error: " . $e->getMessage());
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            Cache::put('tutorials:test', 'working', 60);
            $value = Cache::get('tutorials:test');
            Cache::forget('tutorials:test');

            if ($value !== 'working') {
                $this->line("   └─ Cache write/read failed");
                return false;
            }

            $this->line("   └─ Cache system working ✓");
            return true;
        } catch (\Exception $e) {
            $this->line("   └─ Cache error: " . $e->getMessage());
            return false;
        }
    }

    private function checkPostModel(): bool
    {
        try {
            $postCount = \App\Models\Post::count();
            $draftCount = \App\Models\Post::where('status', 'draft')->count();
            $seriesCount = \App\Models\Post::whereNotNull('series_slug')->distinct('series_slug')->count();

            $this->line("   └─ Total posts: {$postCount}");
            $this->line("   └─ Draft posts: {$draftCount}");
            $this->line("   └─ Series count: {$seriesCount}");
            return true;
        } catch (\Exception $e) {
            $this->line("   └─ Model error: " . $e->getMessage());
            return false;
        }
    }

    private function checkApiConnectivity(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get('https://api.anthropic.com/v1/models', [
                    'x-api-key' => 'sk-test',
                    'anthropic-version' => '2023-06-01',
                ]);

            // We expect a 401 or 400 with invalid key, which means connectivity is fine
            if ($response->status() === 401 || $response->status() === 400) {
                $this->line("   └─ Can reach Anthropic API ✓");
                return true;
            }

            $this->line("   └─ Unexpected API response: " . $response->status());
            return false;
        } catch (\Exception $e) {
            $this->line("   └─ Cannot reach Anthropic API: " . $e->getMessage());
            return false;
        }
    }

    private function checkAnthropicApi(): bool
    {
        try {
            $key = config('services.anthropic.key');

            if (empty($key)) {
                return false;
            }

            // Make a real API call to verify the key works
            $response = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-sonnet-4-5-20250929',
                    'max_tokens' => 10,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Say "ok"',
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $content = $response->json('content.0.text', '');
                $this->line("   └─ Anthropic API key valid ✓");
                return true;
            }

            if ($response->status() === 401) {
                $this->line("   └─ API key invalid or expired");
                return false;
            }

            $this->line("   └─ API error: " . $response->status());
            return false;
        } catch (\Exception $e) {
            $this->line("   └─ API test failed: " . $e->getMessage());
            return false;
        }
    }
}
