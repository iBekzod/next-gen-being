<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use LemonSqueezy\Laravel\Events\WebhookHandled;

class HandleLemonSqueezyWebhook
{
    /**
     * Handle LemonSqueezy webhook events for AI subscriptions
     */
    public function handle(WebhookHandled $event): void
    {
        $payload = $event->payload;
        $eventName = $payload['meta']['event_name'] ?? null;

        Log::info('LemonSqueezy Webhook Received', [
            'event' => $eventName,
            'payload' => $payload,
        ]);

        match ($eventName) {
            'subscription_created' => $this->handleSubscriptionCreated($payload),
            'subscription_updated' => $this->handleSubscriptionUpdated($payload),
            'subscription_cancelled' => $this->handleSubscriptionCancelled($payload),
            'subscription_resumed' => $this->handleSubscriptionResumed($payload),
            'subscription_expired' => $this->handleSubscriptionExpired($payload),
            'subscription_paused' => $this->handleSubscriptionPaused($payload),
            'subscription_unpaused' => $this->handleSubscriptionUnpaused($payload),
            default => null,
        };
    }

    /**
     * Handle subscription created event
     */
    protected function handleSubscriptionCreated(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;
        $variantId = $attributes['variant_id'] ?? null;

        // Find user by LemonSqueezy customer ID
        $user = User::where('lemon_squeezy_customer_id', $customerId)->first();

        if (!$user) {
            Log::warning('User not found for LemonSqueezy customer', ['customer_id' => $customerId]);
            return;
        }

        // Map variant ID to AI tier
        $tier = $this->mapVariantToAITier($variantId);

        if ($tier) {
            $user->upgradeAITier($tier);

            Log::info('AI subscription created', [
                'user_id' => $user->id,
                'tier' => $tier,
                'variant_id' => $variantId,
            ]);
        }
    }

    /**
     * Handle subscription updated event
     */
    protected function handleSubscriptionUpdated(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;
        $variantId = $attributes['variant_id'] ?? null;
        $status = $attributes['status'] ?? null;

        $user = User::where('lemon_squeezy_customer_id', $customerId)->first();

        if (!$user) {
            Log::warning('User not found for LemonSqueezy customer', ['customer_id' => $customerId]);
            return;
        }

        // If variant changed (plan upgrade/downgrade)
        $newTier = $this->mapVariantToAITier($variantId);

        if ($newTier && $newTier !== $user->ai_tier) {
            $user->upgradeAITier($newTier);

            Log::info('AI subscription tier updated', [
                'user_id' => $user->id,
                'old_tier' => $user->ai_tier,
                'new_tier' => $newTier,
            ]);
        }

        // If subscription status changed
        if ($status === 'cancelled' || $status === 'expired') {
            $this->downgradeUserToFree($user);
        }
    }

    /**
     * Handle subscription cancelled event
     */
    protected function handleSubscriptionCancelled(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;
        $endsAt = $attributes['ends_at'] ?? null;

        $user = User::where('lemon_squeezy_customer_id', $customerId)->first();

        if (!$user) {
            return;
        }

        // Update expiration date but don't downgrade immediately
        // Allow access until end of billing period
        if ($endsAt) {
            $user->update([
                'ai_tier_expires_at' => $endsAt,
            ]);

            Log::info('AI subscription cancelled', [
                'user_id' => $user->id,
                'expires_at' => $endsAt,
            ]);
        }
    }

    /**
     * Handle subscription resumed event
     */
    protected function handleSubscriptionResumed(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;
        $variantId = $attributes['variant_id'] ?? null;

        $user = User::where('lemon_squeezy_customer_id', $customerId)->first();

        if (!$user) {
            return;
        }

        $tier = $this->mapVariantToAITier($variantId);

        if ($tier) {
            $user->upgradeAITier($tier);

            Log::info('AI subscription resumed', [
                'user_id' => $user->id,
                'tier' => $tier,
            ]);
        }
    }

    /**
     * Handle subscription expired event
     */
    protected function handleSubscriptionExpired(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;

        $user = User::where('lemon_squeezy_customer_id', $customerId)->first();

        if (!$user) {
            return;
        }

        $this->downgradeUserToFree($user);

        Log::info('AI subscription expired', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Handle subscription paused event
     */
    protected function handleSubscriptionPaused(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;

        $user = User::where('lemon_squeezy_customer_id', $customerId)->first();

        if (!$user) {
            return;
        }

        // Optionally pause AI access
        Log::info('AI subscription paused', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Handle subscription unpaused event
     */
    protected function handleSubscriptionUnpaused(array $payload): void
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $customerId = $attributes['customer_id'] ?? null;
        $variantId = $attributes['variant_id'] ?? null;

        $user = User::where('lemon_squeezy_customer_id', $customerId)->first();

        if (!$user) {
            return;
        }

        $tier = $this->mapVariantToAITier($variantId);

        if ($tier) {
            $user->upgradeAITier($tier);

            Log::info('AI subscription unpaused', [
                'user_id' => $user->id,
                'tier' => $tier,
            ]);
        }
    }

    /**
     * Map LemonSqueezy variant ID to AI tier
     */
    protected function mapVariantToAITier(?string $variantId): ?string
    {
        if (!$variantId) {
            return null;
        }

        // Map variant IDs to AI tiers (update these with your actual variant IDs from LemonSqueezy)
        $variantTiers = [
            config('services.lemonsqueezy.ai_basic_variant_id') => 'basic',
            config('services.lemonsqueezy.ai_premium_variant_id') => 'premium',
            config('services.lemonsqueezy.ai_enterprise_variant_id') => 'enterprise',
        ];

        return $variantTiers[$variantId] ?? null;
    }

    /**
     * Downgrade user to free tier
     */
    protected function downgradeUserToFree(User $user): void
    {
        $user->downgradeAITier();

        Log::info('User downgraded to free AI tier', [
            'user_id' => $user->id,
        ]);
    }
}
