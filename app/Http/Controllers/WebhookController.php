<?php
namespace App\Http\Controllers;

use App\Services\PaddleService;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private PaddleService $paddle
    ) {}

    public function paddleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Paddle-Signature');

        if (!$this->paddle->verifyWebhook($payload, $signature)) {
            Log::warning('Invalid Paddle webhook signature');
            return response('Invalid signature', 400);
        }

        $data = json_decode($payload, true);
        $eventType = $data['event_type'] ?? '';

        Log::info('Paddle webhook received', ['event' => $eventType]);

        match ($eventType) {
            'transaction.completed' => $this->handlePaddleTransactionCompleted($data),
            'subscription.created' => $this->handlePaddleSubscriptionCreated($data),
            'subscription.updated' => $this->handlePaddleSubscriptionUpdated($data),
            'subscription.canceled' => $this->handlePaddleSubscriptionCanceled($data),
            'subscription.paused' => $this->handlePaddleSubscriptionPaused($data),
            'subscription.resumed' => $this->handlePaddleSubscriptionResumed($data),
            default => Log::info('Unhandled Paddle webhook event', ['event' => $eventType])
        };

        return response('OK');
    }

    // Paddle webhook handlers
    private function handlePaddleTransactionCompleted(array $data): void
    {
        $transaction = $data['data'];
        $customData = $transaction['custom_data'] ?? [];
        $userId = $customData['user_id'] ?? null;

        if (!$userId) {
            Log::error('User ID not found in Paddle transaction webhook');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            Log::error('User not found for Paddle transaction', ['user_id' => $userId]);
            return;
        }

        // If this transaction is for a subscription, it will be handled by subscription.created
        // This handles one-time payments
        if (empty($transaction['subscription_id'])) {
            // Handle one-time payment logic here if needed
            Log::info('One-time payment completed', ['transaction_id' => $transaction['id']]);
        }
    }

    private function handlePaddleSubscriptionCreated(array $data): void
    {
        $subscription = $data['data'];
        $customData = $subscription['custom_data'] ?? [];
        $userId = $customData['user_id'] ?? null;

        if (!$userId) {
            Log::error('User ID not found in Paddle subscription webhook');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            Log::error('User not found for Paddle subscription', ['user_id' => $userId]);
            return;
        }

        Subscription::create([
            'user_id' => $user->id,
            'provider' => 'paddle',
            'provider_id' => $subscription['id'],
            'name' => 'default',
            'status' => $subscription['status'],
            'price_id' => $subscription['items'][0]['price']['id'] ?? null,
            'trial_ends_at' => isset($subscription['trial_dates']['ends_at'])
                ? now()->parse($subscription['trial_dates']['ends_at']) : null,
            'current_period_start' => now()->parse($subscription['current_billing_period']['starts_at']),
            'current_period_end' => now()->parse($subscription['current_billing_period']['ends_at']),
            'renews_at' => isset($subscription['next_billed_at'])
                ? now()->parse($subscription['next_billed_at']) : null,
        ]);
    }

    private function handlePaddleSubscriptionUpdated(array $data): void
    {
        $subscriptionData = $data['data'];

        $subscription = Subscription::where('provider', 'paddle')
            ->where('provider_id', $subscriptionData['id'])
            ->first();

        if (!$subscription) {
            Log::error('Paddle subscription not found for update', ['id' => $subscriptionData['id']]);
            return;
        }

        $subscription->update([
            'status' => $subscriptionData['status'],
            'current_period_start' => now()->parse($subscriptionData['current_billing_period']['starts_at']),
            'current_period_end' => now()->parse($subscriptionData['current_billing_period']['ends_at']),
            'renews_at' => isset($subscriptionData['next_billed_at'])
                ? now()->parse($subscriptionData['next_billed_at']) : null,
        ]);
    }

    private function handlePaddleSubscriptionCanceled(array $data): void
    {
        $this->updatePaddleSubscriptionStatus($data, 'cancelled');
    }

    private function handlePaddleSubscriptionPaused(array $data): void
    {
        $this->updatePaddleSubscriptionStatus($data, 'paused');
    }

    private function handlePaddleSubscriptionResumed(array $data): void
    {
        $this->updatePaddleSubscriptionStatus($data, 'active');
    }

    private function updatePaddleSubscriptionStatus(array $data, string $status): void
    {
        $subscriptionData = $data['data'];

        $subscription = Subscription::where('provider', 'paddle')
            ->where('provider_id', $subscriptionData['id'])
            ->first();

        if (!$subscription) {
            Log::error('Paddle subscription not found for status update', [
                'id' => $subscriptionData['id'],
                'status' => $status
            ]);
            return;
        }

        $updateData = ['status' => $status];

        if ($status === 'cancelled' && isset($subscriptionData['canceled_at'])) {
            $updateData['ends_at'] = now()->parse($subscriptionData['canceled_at']);
        }

        $subscription->update($updateData);
    }
}
