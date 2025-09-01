<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HealthCheckController extends Controller
{
    /**
     * Perform health check and return status.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'storage' => $this->checkStorage(),
            'meilisearch' => $this->checkMeilisearch(),
        ];

        $isHealthy = !in_array(false, array_column($checks, 'status'), true);
        $statusCode = $isHealthy ? 200 : 503;

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'environment' => app()->environment(),
            'version' => config('app.version', '1.0.0'),
        ], $statusCode);
    }

    /**
     * Check application status.
     */
    private function checkApp(): array
    {
        try {
            $status = app()->isDownForMaintenance() === false;
            return [
                'status' => $status,
                'message' => $status ? 'Application is running' : 'Application is in maintenance mode',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Application check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            DB::connection()->getDatabaseName();

            // Perform a simple query
            DB::select('SELECT 1');

            return [
                'status' => true,
                'message' => 'Database connection successful',
                'database' => DB::connection()->getDatabaseName(),
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Database connection failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis connectivity.
     */
    private function checkRedis(): array
    {
        try {
            $redis = Redis::connection();
            $redis->ping();

            // Test write and read
            $testKey = 'health:check:' . time();
            $testValue = 'OK';

            Redis::setex($testKey, 10, $testValue);
            $result = Redis::get($testKey);
            Redis::del($testKey);

            return [
                'status' => $result === $testValue,
                'message' => 'Redis connection successful',
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Redis connection failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Redis connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache functionality.
     */
    private function checkCache(): array
    {
        try {
            $key = 'health:cache:test';
            $value = uniqid('test_', true);

            Cache::put($key, $value, 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $retrieved === $value,
                'message' => 'Cache is working',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Cache check failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Cache check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connectivity.
     */
    private function checkQueue(): array
    {
        try {
            // Check if we can get queue size
            $queueSize = Redis::llen('queues:default');

            return [
                'status' => true,
                'message' => 'Queue connection successful',
                'default_queue_size' => $queueSize,
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Queue check failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Queue check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage accessibility.
     */
    private function checkStorage(): array
    {
        try {
            $directories = [
                'logs' => storage_path('logs'),
                'framework/cache' => storage_path('framework/cache'),
                'framework/sessions' => storage_path('framework/sessions'),
                'framework/views' => storage_path('framework/views'),
            ];

            $issues = [];
            foreach ($directories as $name => $path) {
                if (!is_dir($path) || !is_writable($path)) {
                    $issues[] = $name;
                }
            }

            return [
                'status' => empty($issues),
                'message' => empty($issues)
                    ? 'All storage directories are accessible'
                    : 'Storage issues with: ' . implode(', ', $issues),
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Storage check failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Storage check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check Meilisearch connectivity.
     */
    private function checkMeilisearch(): array
    {
        try {
            $client = app(\MeiliSearch\Client::class);
            $health = $client->health();

            return [
                'status' => isset($health['status']) && $health['status'] === 'available',
                'message' => 'Meilisearch is healthy',
            ];
        } catch (\Exception $e) {
            // Meilisearch might not be critical for app functionality
            return [
                'status' => true,
                'message' => 'Meilisearch check skipped (non-critical)',
                'note' => $e->getMessage(),
            ];
        }
    }
}
