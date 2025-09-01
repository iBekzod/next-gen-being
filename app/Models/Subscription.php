<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'lemonsqueezy_id', // Keep for backward compatibility
        'order_id',
        'name',
        'product_id',
        'variant_id',
        'price_id',
        'status',
        'card_brand',
        'card_last_four',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'renews_at',
        'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'renews_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               (!$this->ends_at || $this->ends_at->isFuture());
    }

    /**
     * Check if the subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if the subscription is paused.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Check if the subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' ||
               ($this->ends_at && $this->ends_at->isPast());
    }

    /**
     * Check if the subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the subscription has ended.
     */
    public function hasEnded(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Get the subscription status with human-readable labels.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'cancelled' => 'Cancelled',
            'paused' => 'Paused',
            'expired' => 'Expired',
            'unpaid' => 'Unpaid',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get the plan name based on price_id or variant_id.
     */
    public function getPlanNameAttribute(): string
    {
        if ($this->provider === 'paddle') {
            return match($this->price_id) {
                config('services.paddle.basic_price_id') => 'Basic',
                config('services.paddle.pro_price_id') => 'Pro',
                config('services.paddle.enterprise_price_id') => 'Enterprise',
                default => 'Unknown'
            };
        } else {
            return match($this->variant_id) {
                config('services.lemonsqueezy.basic_variant_id') => 'Basic',
                config('services.lemonsqueezy.pro_variant_id') => 'Pro',
                config('services.lemonsqueezy.enterprise_variant_id') => 'Enterprise',
                default => 'Unknown'
            };
        }
    }

    /**
     * Get the plan price.
     */
    public function getPlanPriceAttribute(): float
    {
        $planName = $this->plan_name;

        return match($planName) {
            'Basic' => 9.99,
            'Pro' => 19.99,
            'Enterprise' => 49.99,
            default => 0.00
        };
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('ends_at')
                          ->orWhere('ends_at', '>', now());
                    });
    }

    /**
     * Scope a query to only include cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include subscriptions for a specific provider.
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Get days remaining until renewal or expiry.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->isActive() && $this->renews_at) {
            return now()->diffInDays($this->renews_at);
        }

        if ($this->ends_at) {
            return now()->diffInDays($this->ends_at);
        }

        return null;
    }

    /**
     * Check if subscription will renew.
     */
    public function willRenew(): bool
    {
        return $this->isActive() && !$this->ends_at && $this->renews_at;
    }

    /**
     * Get the next billing date.
     */
    public function getNextBillingDateAttribute(): ?string
    {
        if ($this->willRenew()) {
            return $this->renews_at->format('F j, Y');
        }

        if ($this->ends_at) {
            return 'Ends on ' . $this->ends_at->format('F j, Y');
        }

        return null;
    }
}
