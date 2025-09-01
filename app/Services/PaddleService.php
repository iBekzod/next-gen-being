<?php

namespace App\Services;

// use Paddle\SDK\Client;
// use Paddle\SDK\Resources\Subscriptions\Operations\CreateSubscription;
// use Paddle\SDK\Resources\Subscriptions\Operations\UpdateSubscription;
// use Paddle\SDK\Resources\Transactions\Operations\CreateTransaction;
// use Illuminate\Support\Facades\Log;

class PaddleService
{
    // private Client $paddle;
    // private string $environment;

    // public function __construct()
    // {
    //     $this->paddle = new Client(
    //         apiKey: config('services.paddle.api_key'),
    //         options: [
    //             'environment' => config('services.paddle.environment', 'sandbox') // 'production' for live
    //         ]
    //     );
    //     $this->environment = config('services.paddle.environment', 'sandbox');
    // }

    // /**
    //  * Create a checkout for one-time payment or subscription
    //  */
    // public function createCheckout(array $data): array
    // {
    //     try {
    //         $checkoutData = [
    //             'items' => [
    //                 [
    //                     'price_id' => $data['price_id'],
    //                     'quantity' => 1
    //                 ]
    //             ],
    //             'customer_email' => $data['email'],
    //             'custom_data' => [
    //                 'user_id' => (string) $data['user_id'],
    //                 'user_name' => $data['name']
    //             ],
    //             'return_url' => route('subscription.success'),
    //             'discount_code' => $data['discount_code'] ?? null,
    //         ];

    //         $response = $this->paddle->transactions->create($checkoutData);

    //         return [
    //             'checkout_url' => $response['checkout']['url'] ?? null,
    //             'transaction_id' => $response['id'] ?? null,
    //             'data' => $response
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error('Paddle checkout creation failed', [
    //             'error' => $e->getMessage(),
    //             'data' => $data
    //         ]);
    //         throw new \Exception('Failed to create checkout: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Create a subscription directly
    //  */
    // public function createSubscription(array $data): array
    // {
    //     try {
    //         $subscriptionData = [
    //             'items' => [
    //                 [
    //                     'price_id' => $data['price_id'],
    //                     'quantity' => 1
    //                 ]
    //             ],
    //             'customer_id' => $data['customer_id'] ?? null,
    //             'customer_email' => $data['email'] ?? null,
    //             'billing_cycle' => [
    //                 'interval' => 'month',
    //                 'frequency' => 1
    //             ],
    //             'custom_data' => [
    //                 'user_id' => (string) $data['user_id']
    //             ]
    //         ];

    //         if (!isset($data['customer_id'])) {
    //             // Create customer if not exists
    //             $customer = $this->createCustomer([
    //                 'email' => $data['email'],
    //                 'name' => $data['name']
    //             ]);
    //             $subscriptionData['customer_id'] = $customer['id'];
    //         }

    //         $response = $this->paddle->subscriptions->create($subscriptionData);
    //         return $response;
    //     } catch (\Exception $e) {
    //         Log::error('Paddle subscription creation failed', [
    //             'error' => $e->getMessage(),
    //             'data' => $data
    //         ]);
    //         throw new \Exception('Failed to create subscription: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Create customer
    //  */
    // public function createCustomer(array $data): array
    // {
    //     try {
    //         $customerData = [
    //             'email' => $data['email'],
    //             'name' => $data['name'] ?? '',
    //         ];

    //         return $this->paddle->customers->create($customerData);
    //     } catch (\Exception $e) {
    //         Log::error('Paddle customer creation failed', [
    //             'error' => $e->getMessage(),
    //             'data' => $data
    //         ]);
    //         throw new \Exception('Failed to create customer: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Get subscription details
    //  */
    // public function getSubscription(string $subscriptionId): array
    // {
    //     try {
    //         return $this->paddle->subscriptions->get($subscriptionId);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to get Paddle subscription', [
    //             'subscription_id' => $subscriptionId,
    //             'error' => $e->getMessage()
    //         ]);
    //         throw new \Exception('Failed to get subscription: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Cancel subscription
    //  */
    // public function cancelSubscription(string $subscriptionId, bool $immediately = false): array
    // {
    //     try {
    //         $updateData = [
    //             'scheduled_change' => [
    //                 'action' => 'cancel',
    //                 'effective_at' => $immediately ? 'immediately' : 'next_billing_period'
    //             ]
    //         ];

    //         return $this->paddle->subscriptions->update($subscriptionId, $updateData);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to cancel Paddle subscription', [
    //             'subscription_id' => $subscriptionId,
    //             'error' => $e->getMessage()
    //         ]);
    //         throw new \Exception('Failed to cancel subscription: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Pause subscription
    //  */
    // public function pauseSubscription(string $subscriptionId): array
    // {
    //     try {
    //         $updateData = [
    //             'scheduled_change' => [
    //                 'action' => 'pause'
    //             ]
    //         ];

    //         return $this->paddle->subscriptions->update($subscriptionId, $updateData);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to pause Paddle subscription', [
    //             'subscription_id' => $subscriptionId,
    //             'error' => $e->getMessage()
    //         ]);
    //         throw new \Exception('Failed to pause subscription: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Resume subscription
    //  */
    // public function resumeSubscription(string $subscriptionId): array
    // {
    //     try {
    //         $updateData = [
    //             'scheduled_change' => [
    //                 'action' => 'resume'
    //             ]
    //         ];

    //         return $this->paddle->subscriptions->update($subscriptionId, $updateData);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to resume Paddle subscription', [
    //             'subscription_id' => $subscriptionId,
    //             'error' => $e->getMessage()
    //         ]);
    //         throw new \Exception('Failed to resume subscription: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Get all products
    //  */
    // public function getProducts(): array
    // {
    //     try {
    //         return $this->paddle->products->list();
    //     } catch (\Exception $e) {
    //         Log::error('Failed to get Paddle products', [
    //             'error' => $e->getMessage()
    //         ]);
    //         throw new \Exception('Failed to get products: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Get prices for a product
    //  */
    // public function getPrices(string $productId = null): array
    // {
    //     try {
    //         $params = [];
    //         if ($productId) {
    //             $params['product_id'] = $productId;
    //         }
    //         return $this->paddle->prices->list($params);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to get Paddle prices', [
    //             'product_id' => $productId,
    //             'error' => $e->getMessage()
    //         ]);
    //         throw new \Exception('Failed to get prices: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Verify webhook signature
    //  */
    // public function verifyWebhook(string $payload, string $signature): bool
    // {
    //     try {
    //         $secret = config('services.paddle.webhook_secret');

    //         // Paddle uses HMAC SHA256
    //         $expectedSignature = hash_hmac('sha256', $payload, $secret);

    //         // Extract signature from header (format: "ts=timestamp;h1=signature")
    //         if (preg_match('/h1=([a-f0-9]+)/', $signature, $matches)) {
    //             $providedSignature = $matches[1];
    //             return hash_equals($expectedSignature, $providedSignature);
    //         }

    //         return false;
    //     } catch (\Exception $e) {
    //         Log::error('Paddle webhook verification failed', [
    //             'error' => $e->getMessage()
    //         ]);
    //         return false;
    //     }
    // }

    // /**
    //  * Get customer portal URL for subscription management
    //  */
    // public function getCustomerPortalUrl(string $customerId): string
    // {
    //     // Paddle automatically provides customer portal
    //     // You can customize this URL in your Paddle dashboard
    //     $baseUrl = $this->environment === 'production'
    //         ? 'https://checkout.paddle.com/customer-portal'
    //         : 'https://sandbox-checkout.paddle.com/customer-portal';

    //     return $baseUrl . '?customer_id=' . $customerId;
    // }
}
