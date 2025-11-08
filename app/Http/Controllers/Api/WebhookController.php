<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Services\Webhook\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WebhookController extends Controller
{
    private WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all webhooks for user
     * GET /api/webhooks
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $webhooks = $user->webhooks()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $webhooks->items(),
            'meta' => [
                'total' => $webhooks->total(),
                'per_page' => $webhooks->perPage(),
                'current_page' => $webhooks->currentPage(),
                'last_page' => $webhooks->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new webhook
     * POST /api/webhooks
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'url' => 'required|url',
                'event_type' => 'required|string',
                'events' => 'nullable|array',
                'events.*' => 'string',
                'headers' => 'nullable|array',
                'max_retries' => 'nullable|integer|min:0|max:10',
                'verify_ssl' => 'nullable|boolean',
            ]);

            $webhook = $this->webhookService->createWebhook($request->user(), $validated);

            return response()->json([
                'success' => true,
                'data' => $webhook,
                'message' => 'Webhook created successfully',
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating webhook: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get webhook details
     * GET /api/webhooks/{webhook}
     */
    public function show(Webhook $webhook, Request $request): JsonResponse
    {
        // Authorization check
        if ($webhook->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $webhook,
            'statistics' => $webhook->getStatistics(),
        ]);
    }

    /**
     * Update webhook
     * PUT /api/webhooks/{webhook}
     */
    public function update(Webhook $webhook, Request $request): JsonResponse
    {
        try {
            // Authorization check
            if ($webhook->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'string|max:255',
                'url' => 'url',
                'event_type' => 'string',
                'events' => 'nullable|array',
                'events.*' => 'string',
                'headers' => 'nullable|array',
                'max_retries' => 'integer|min:0|max:10',
                'verify_ssl' => 'boolean',
                'status' => 'in:active,inactive,failed',
            ]);

            $updated = $this->webhookService->updateWebhook($webhook, $validated);

            return response()->json([
                'success' => true,
                'data' => $updated,
                'message' => 'Webhook updated successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating webhook: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete webhook
     * DELETE /api/webhooks/{webhook}
     */
    public function destroy(Webhook $webhook, Request $request): JsonResponse
    {
        try {
            // Authorization check
            if ($webhook->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $this->webhookService->deleteWebhook($webhook);

            return response()->json([
                'success' => true,
                'message' => 'Webhook deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting webhook: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test webhook
     * POST /api/webhooks/{webhook}/test
     */
    public function test(Webhook $webhook, Request $request): JsonResponse
    {
        try {
            // Authorization check
            if ($webhook->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $result = $this->webhookService->testWebhook($webhook);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing webhook: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get webhook logs
     * GET /api/webhooks/{webhook}/logs
     */
    public function logs(Webhook $webhook, Request $request): JsonResponse
    {
        // Authorization check
        if ($webhook->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $logs = $webhook->logs()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }

    /**
     * Get available webhook events
     * GET /api/webhooks/events/available
     */
    public function getAvailableEvents(): JsonResponse
    {
        $events = WebhookService::getAvailableEvents();

        return response()->json([
            'success' => true,
            'data' => array_map(function ($value, $key) {
                return [
                    'key' => $key,
                    'name' => $value,
                ];
            }, array_values($events), array_keys($events)),
        ]);
    }

    /**
     * Get webhook statistics
     * GET /api/webhooks/{webhook}/statistics
     */
    public function statistics(Webhook $webhook, Request $request): JsonResponse
    {
        // Authorization check
        if ($webhook->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $stats = $webhook->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
