<?php

namespace App\Services\Webhook;

use App\Models\Webhook;
use App\Models\WebhookLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookService
{
    private const TIMEOUT = 30; // seconds
    private const CONNECT_TIMEOUT = 10; // seconds

    /**
     * Trigger webhooks for an event
     */
    public function trigger(string $eventType, array $payload, ?User $user = null): void
    {
        try {
            $query = Webhook::where('status', 'active');

            if ($user) {
                $query->where('user_id', $user->id);
            }

            $webhooks = $query->get();

            foreach ($webhooks as $webhook) {
                if ($webhook->shouldTrigger($eventType)) {
                    // Fire webhook asynchronously if possible, otherwise execute directly
                    $this->executeWebhook($webhook, $eventType, $payload);
                }
            }

        } catch (Exception $e) {
            Log::error("Error triggering webhooks: {$e->getMessage()}");
        }
    }

    /**
     * Execute webhook call
     */
    public function executeWebhook(Webhook $webhook, string $eventType, array $payload): bool
    {
        $startTime = microtime(true);

        try {
            // Prepare request
            $headers = $this->prepareHeaders($webhook, $eventType, $payload);
            $data = [
                'event' => $eventType,
                'timestamp' => now()->toIso8601String(),
                'data' => $payload,
            ];

            // Make the request
            $response = Http::withHeaders($headers)
                ->timeout(self::TIMEOUT)
                ->connectTimeout(self::CONNECT_TIMEOUT)
                ->verify($webhook->verify_ssl)
                ->post($webhook->url, $data);

            $endTime = microtime(true);
            $responseTime = (int) (($endTime - $startTime) * 1000);

            // Log the request
            $this->logWebhookCall(
                $webhook,
                $eventType,
                $data,
                $response->status(),
                $response->body(),
                true,
                null,
                $responseTime
            );

            // Update webhook status
            if ($response->successful()) {
                $webhook->markAsSuccess();
                return true;
            } else {
                $error = "HTTP {$response->status()}: {$response->body()}";
                $webhook->markAsFailed($error);
                return false;
            }

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $endTime = microtime(true);
            $responseTime = (int) (($endTime - $startTime) * 1000);

            // Log the failed request
            $this->logWebhookCall(
                $webhook,
                $eventType,
                $data ?? [],
                $e->response ? $e->response->status() : null,
                $e->response ? $e->response->body() : null,
                false,
                $e->getMessage(),
                $responseTime
            );

            $webhook->markAsFailed($e->getMessage());

            Log::warning("Webhook {$webhook->id} failed: {$e->getMessage()}");
            return false;

        } catch (Exception $e) {
            $endTime = microtime(true);
            $responseTime = (int) (($endTime - $startTime) * 1000);

            // Log the error
            $this->logWebhookCall(
                $webhook,
                $eventType,
                $data ?? [],
                null,
                null,
                false,
                $e->getMessage(),
                $responseTime
            );

            $webhook->markAsFailed($e->getMessage());

            Log::error("Webhook {$webhook->id} error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Prepare headers for webhook request
     */
    private function prepareHeaders(Webhook $webhook, string $eventType, array $payload): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'NextGenBeing/1.0 (Webhook)',
            'X-NextGen-Event' => $eventType,
            'X-NextGen-Timestamp' => now()->toIso8601String(),
        ];

        // Add signature for verification
        $payload = json_encode([
            'event' => $eventType,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ]);

        $signature = hash_hmac('sha256', $payload, config('services.webhook.secret', 'secret'));
        $headers['X-NextGen-Signature'] = "sha256={$signature}";

        // Add custom headers
        if ($webhook->headers) {
            $headers = array_merge($headers, $webhook->headers);
        }

        return $headers;
    }

    /**
     * Log webhook call
     */
    private function logWebhookCall(
        Webhook $webhook,
        string $eventType,
        array $requestPayload,
        ?int $responseStatus,
        ?string $responseBody,
        bool $success,
        ?string $errorMessage,
        int $responseTime
    ): void {
        WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event_type' => $eventType,
            'response_status' => $responseStatus,
            'request_payload' => $requestPayload,
            'response_body' => $responseBody,
            'success' => $success,
            'error_message' => $errorMessage,
            'response_time_ms' => $responseTime,
        ]);
    }

    /**
     * Create webhook
     */
    public function createWebhook(User $user, array $data): Webhook
    {
        return Webhook::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'url' => $data['url'],
            'event_type' => $data['event_type'],
            'events' => $data['events'] ?? [],
            'headers' => $data['headers'] ?? null,
            'status' => 'active',
            'max_retries' => $data['max_retries'] ?? 3,
            'verify_ssl' => $data['verify_ssl'] ?? true,
        ]);
    }

    /**
     * Update webhook
     */
    public function updateWebhook(Webhook $webhook, array $data): Webhook
    {
        $webhook->update([
            'name' => $data['name'] ?? $webhook->name,
            'url' => $data['url'] ?? $webhook->url,
            'event_type' => $data['event_type'] ?? $webhook->event_type,
            'events' => $data['events'] ?? $webhook->events,
            'headers' => $data['headers'] ?? $webhook->headers,
            'max_retries' => $data['max_retries'] ?? $webhook->max_retries,
            'verify_ssl' => $data['verify_ssl'] ?? $webhook->verify_ssl,
            'status' => $data['status'] ?? $webhook->status,
        ]);

        return $webhook;
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(Webhook $webhook): void
    {
        $webhook->delete();
    }

    /**
     * Test webhook
     */
    public function testWebhook(Webhook $webhook): array
    {
        $testPayload = [
            'user_id' => $webhook->user_id,
            'test' => true,
            'message' => 'This is a test webhook from NextGen Being',
        ];

        $success = $this->executeWebhook($webhook, 'test.event', $testPayload);

        return [
            'success' => $success,
            'message' => $success ? 'Webhook test successful' : 'Webhook test failed - check logs',
            'logs' => $webhook->recentFailedLogs(1)->toArray(),
        ];
    }

    /**
     * Get available event types
     */
    public static function getAvailableEvents(): array
    {
        return [
            'post.published' => 'Post Published',
            'post.updated' => 'Post Updated',
            'post.deleted' => 'Post Deleted',
            'post.commented' => 'Post Commented',
            'post.liked' => 'Post Liked',
            'post.unliked' => 'Post Unliked',
            'post.bookmarked' => 'Post Bookmarked',
            'earning.created' => 'Earning Created',
            'earning.updated' => 'Earning Updated',
            'earning.paid' => 'Earning Paid',
            'payout.requested' => 'Payout Requested',
            'payout.paid' => 'Payout Paid',
            'payout.failed' => 'Payout Failed',
            'follower.added' => 'Follower Added',
            'follower.removed' => 'Follower Removed',
            'social.published' => 'Social Media Published',
            'social.metrics.updated' => 'Social Media Metrics Updated',
            'subscription.created' => 'Subscription Created',
            'subscription.cancelled' => 'Subscription Cancelled',
            'user.profile.updated' => 'User Profile Updated',
        ];
    }

    /**
     * Get event type display name
     */
    public static function getEventDisplayName(string $eventType): string
    {
        $events = self::getAvailableEvents();
        return $events[$eventType] ?? $eventType;
    }

    /**
     * Retry failed webhooks
     */
    public function retryFailedWebhooks(): void
    {
        $webhooks = Webhook::where('status', 'failed')
            ->where('retry_count', '<', Webhook::max('max_retries'))
            ->get();

        foreach ($webhooks as $webhook) {
            Log::info("Retrying failed webhook {$webhook->id}");

            // Get the last failed log
            $lastLog = $webhook->logs()
                ->where('success', false)
                ->latest()
                ->first();

            if ($lastLog) {
                $this->executeWebhook(
                    $webhook,
                    $lastLog->event_type,
                    $lastLog->request_payload
                );
            }
        }
    }

    /**
     * Clean up old webhook logs
     */
    public function cleanupOldLogs(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return WebhookLog::where('created_at', '<', $cutoffDate)->delete();
    }
}
