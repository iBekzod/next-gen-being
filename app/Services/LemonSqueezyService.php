<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LemonSqueezyService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.lemonsqueezy.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.lemonsqueezy.api_key');
    }

    /**
     * Get a customer by email
     */
    public function getCustomerByEmail(string $email)
    {
        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])
            ->get("{$this->baseUrl}/customers", [
                'filter[email]' => $email,
                'filter[store_id]' => config('services.lemonsqueezy.store_id'),
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'][0] ?? null;
        }

        return null;
    }

    /**
     * Get a subscription by ID
     */
    public function getSubscription(string $subscriptionId)
    {
        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])
            ->get("{$this->baseUrl}/subscriptions/{$subscriptionId}");

        if ($response->successful()) {
            return $response->json('data');
        }

        return null;
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId)
    {
        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])
            ->delete("{$this->baseUrl}/subscriptions/{$subscriptionId}");

        return $response->successful();
    }

    /**
     * Resume a subscription
     */
    public function resumeSubscription(string $subscriptionId)
    {
        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])
            ->patch("{$this->baseUrl}/subscriptions/{$subscriptionId}", [
                'data' => [
                    'type' => 'subscriptions',
                    'id' => $subscriptionId,
                    'attributes' => [
                        'cancelled' => false,
                    ],
                ],
            ]);

        return $response->successful();
    }

    /**
     * Update payment method URL
     */
    public function getUpdatePaymentMethodUrl(string $subscriptionId): ?string
    {
        $subscription = $this->getSubscription($subscriptionId);

        if ($subscription && isset($subscription['attributes']['urls']['update_payment_method'])) {
            return $subscription['attributes']['urls']['update_payment_method'];
        }

        return null;
    }

    /**
     * Get customer portal URL
     */
    public function getCustomerPortalUrl(string $subscriptionId): ?string
    {
        $subscription = $this->getSubscription($subscriptionId);

        if ($subscription && isset($subscription['attributes']['urls']['customer_portal'])) {
            return $subscription['attributes']['urls']['customer_portal'];
        }

        return null;
    }

    /**
     * Create a checkout URL
     */
    public function createCheckout(array $data): ?string
    {
        $response = Http::withToken($this->apiKey)
            ->withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
            ])
            ->post("{$this->baseUrl}/checkouts", [
                'data' => [
                    'type' => 'checkouts',
                    'attributes' => $data,
                    'relationships' => [
                        'store' => [
                            'data' => [
                                'type' => 'stores',
                                'id' => (string) config('services.lemonsqueezy.store_id'),
                            ],
                        ],
                        'variant' => [
                            'data' => [
                                'type' => 'variants',
                                'id' => (string) $data['variant_id'],
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json('data');
            return $data['attributes']['url'] ?? null;
        }

        return null;
    }
}
