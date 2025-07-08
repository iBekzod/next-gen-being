<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LemonSqueezyService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.lemonsqueezy.com/v1';
    private string $storeId;

    public function __construct()
    {
        $this->apiKey = config('services.lemonsqueezy.api_key');
        $this->storeId = config('services.lemonsqueezy.store_id');
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->{$method}($this->baseUrl . $endpoint, $data);

        if (!$response->successful()) {
            Log::error('LemonSqueezy API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception('LemonSqueezy API request failed: ' . $response->body());
        }

        return $response->json();
    }

    public function createCheckout(array $data): array
    {
        $checkoutData = [
            'data' => [
                'type' => 'checkouts',
                'attributes' => [
                    'checkout_data' => [
                        'email' => $data['email'],
                        'name' => $data['name'],
                        'custom' => [
                            'user_id' => $data['user_id'],
                        ]
                    ]
                ],
                'relationships' => [
                    'store' => [
                        'data' => [
                            'type' => 'stores',
                            'id' => $this->storeId
                        ]
                    ],
                    'variant' => [
                        'data' => [
                            'type' => 'variants',
                            'id' => $data['variant_id']
                        ]
                    ]
                ]
            ]
        ];

        return $this->makeRequest('post', '/checkouts', $checkoutData);
    }

    public function getSubscription(string $subscriptionId): array
    {
        return $this->makeRequest('get', "/subscriptions/{$subscriptionId}");
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->makeRequest('delete', "/subscriptions/{$subscriptionId}");
    }

    public function pauseSubscription(string $subscriptionId): array
    {
        $data = [
            'data' => [
                'type' => 'subscriptions',
                'id' => $subscriptionId,
                'attributes' => [
                    'pause' => null
                ]
            ]
        ];

        return $this->makeRequest('patch', "/subscriptions/{$subscriptionId}", $data);
    }

    public function resumeSubscription(string $subscriptionId): array
    {
        $data = [
            'data' => [
                'type' => 'subscriptions',
                'id' => $subscriptionId,
                'attributes' => [
                    'pause' => null
                ]
            ]
        ];

        return $this->makeRequest('patch', "/subscriptions/{$subscriptionId}", $data);
    }

    public function getProducts(): array
    {
        return $this->makeRequest('get', "/stores/{$this->storeId}/products");
    }

    public function getVariants(string $productId): array
    {
        return $this->makeRequest('get', "/products/{$productId}/variants");
    }

    public function verifyWebhook(string $payload, string $signature): bool
    {
        $secret = config('services.lemonsqueezy.signing_secret');
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($signature, $expectedSignature);
    }
}
