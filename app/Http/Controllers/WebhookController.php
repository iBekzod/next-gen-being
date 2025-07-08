<?php
namespace App\Http\Controllers;

use App\Services\LemonSqueezyService;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private LemonSqueezyService $lemonSqueezy
    ) {}

    public function lemonSqueezyWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Signature');

        if (!$this->lemonSqueezy->verifyWebhook($payload, $signature)) {
            Log::warning('Invalid LemonSqueezy webhook signature');
            return response('Invalid signature', 400);
        }

        $data = json_decode($payload, true);
        $eventName = $data['meta']['event_name'] ?? '';

        Log::info('LemonSqueezy webhook received', ['event' => $eventName]);

        match ($eventName) {
            'subscription_created' => $this->handleSubscriptionCreated($data),
            'subscription_updated' => $this->handleSubscriptionUpdated($data),
            'subscription_cancelled' => $this->handleSubscriptionCancelled($data),
            'subscription_resumed' => $this->handleSubscriptionResumed($data),
            'subscription_expired' => $this->handleSubscriptionExpired($data),
            'subscription_paused' => $this->handleSubscriptionPaused($data),
            'subscription_unpaused' => $this->handleSubscriptionUnpaused($data),
            default => Log::info('Unhandled LemonSqueezy webhook event', ['event' => $eventName])
        };

        return response('OK');
    }

    private function handleSubscriptionCreated(array $data): void
    {
        $attributes = $data['data']['attributes'];
        $customData = $attributes['first_subscription_item']['subscription_data']['custom'] ?? [];

        $userId = $customData['user_id'] ?? null;
        if (!$userId) {
            Log::error('User ID not found in subscription webhook');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            Log::error('User not found for subscription', ['user_id' => $userId]);
            return;
        }

        Subscription::create([
            'user_id' => $user->id,
            'lemonsqueezy_id' => $attributes['id'],
            'order_id' => $attributes['order_id'],
            'name' => 'default',
            'product_id' => $attributes['product_id'],
            'variant_id' => $attributes['variant_id'],
            'status' => $attributes['status'],
            'card_brand' => $attributes['card_brand'],
            'card_last_four' => $attributes['card_last_four'],
            'trial_ends_at' => $attributes['trial_ends_at'] ? now()->parse($attributes['trial_ends_at']) : null,
            'renews_at' => now()->parse($attributes['renews_at']),
            'ends_at' => $attributes['ends_at'] ? now()->parse($attributes['ends_at']) : null,
        ]);
    }

    private function handleSubscriptionUpdated(array $data): void
    {
        $attributes = $data['data']['attributes'];

        $subscription = Subscription::where('lemonsqueezy_id', $attributes['id'])->first();
        if (!$subscription) {
            Log::error('Subscription not found for update', ['lemonsqueezy_id' => $attributes['id']]);
            return;
        }

        $subscription->update([
            'status' => $attributes['status'],
            'card_brand' => $attributes['card_brand'],
            'card_last_four' => $attributes['card_last_four'],
            'trial_ends_at' => $attributes['trial_ends_at'] ? now()->parse($attributes['trial_ends_at']) : null,
            'renews_at' => now()->parse($attributes['renews_at']),
            'ends_at' => $attributes['ends_at'] ? now()->parse($attributes['ends_at']) : null,
        ]);
    }

    private function handleSubscriptionCancelled(array $data): void
    {
        $this->updateSubscriptionStatus($data, 'cancelled');
    }

    private function handleSubscriptionResumed(array $data): void
    {
        $this->updateSubscriptionStatus($data, 'active');
    }

    private function handleSubscriptionExpired(array $data): void
    {
        $this->updateSubscriptionStatus($data, 'expired');
    }

    private function handleSubscriptionPaused(array $data): void
    {
        $this->updateSubscriptionStatus($data, 'paused');
    }

    private function handleSubscriptionUnpaused(array $data): void
    {
        $this->updateSubscriptionStatus($data, 'active');
    }

    private function updateSubscriptionStatus(array $data, string $status): void
    {
        $attributes = $data['data']['attributes'];

        $subscription = Subscription::where('lemonsqueezy_id', $attributes['id'])->first();
        if (!$subscription) {
            Log::error('Subscription not found for status update', [
                'lemonsqueezy_id' => $attributes['id'],
                'status' => $status
            ]);
            return;
        }

        $subscription->update(['status' => $status]);
    }
}
