<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LemonSqueezy\Laravel\Billable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    use HasFactory, Notifiable, Billable, InteractsWithMedia;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio', 'website',
        'twitter', 'linkedin', 'is_active', 'last_seen_at',
        'ai_tier', 'groq_api_key', 'openai_api_key', 'unsplash_api_key',
        'ai_posts_generated', 'ai_images_generated', 'ai_tier_starts_at',
        'ai_tier_expires_at', 'monthly_ai_posts_limit', 'monthly_ai_images_limit',
        'ai_usage_reset_date'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
        'groq_api_key' => 'encrypted',
        'openai_api_key' => 'encrypted',
        'unsplash_api_key' => 'encrypted',
        'ai_tier_starts_at' => 'datetime',
        'ai_tier_expires_at' => 'datetime',
        'ai_usage_reset_date' => 'date',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Admin panel access
        if ($panel->getId() === 'admin') {
            return $this->hasAnyRole(['admin', 'content_manager', 'lead']);
        }

        // Blogger panel access
        if ($panel->getId() === 'blogger') {
            return $this->hasRole('blogger');
        }

        return false;
    }

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function interactions()
    {
        return $this->hasMany(UserInteraction::class);
    }

    public function helpReports()
    {
        return $this->hasMany(HelpReport::class);
    }

    public function earnings()
    {
        return $this->hasMany(BloggerEarning::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    // Role Methods
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()->get()->pluck('permissions')->flatten()->contains($permission);
    }

    // Interaction Methods
    public function hasLiked($model): bool
    {
        return $this->interactions()
            ->where('interactable_type', get_class($model))
            ->where('interactable_id', $model->id)
            ->where('type', 'like')
            ->exists();
    }

    public function hasBookmarked($model): bool
    {
        return $this->interactions()
            ->where('interactable_type', get_class($model))
            ->where('interactable_id', $model->id)
            ->where('type', 'bookmark')
            ->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->whereHas('roles', fn($q) => $q->where('slug', $role));
    }

    public function isPremium(): bool
    {
        return $this->subscribed();
    }

    public function isOnTrial(): bool
    {
        return $this->onTrial();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscribed();
    }

    /**
     * Get the LemonSqueezy store ID for this user.
     */
    public function lemonSqueezyStore(): string
    {
        return config('lemon-squeezy.store');
    }

    /**
     * Get the user's subscription tier (basic, pro, team)
     */
    public function getSubscriptionTier(): ?string
    {
        if (!$this->subscribed()) {
            return null;
        }

        $subscription = $this->subscription();
        if (!$subscription) {
            return null;
        }

        // Map variant IDs to tier names
        $variantTiers = [
            config('services.lemonsqueezy.basic_variant_id') => 'basic',
            config('services.lemonsqueezy.pro_variant_id') => 'pro',
            config('services.lemonsqueezy.team_variant_id') => 'team',
        ];

        // Get the variant ID from the subscription
        $variantId = $subscription->variant_id ?? $subscription->lemon_squeezy_variant_id;

        return $variantTiers[$variantId] ?? 'basic';
    }

    // Follow/Follower relationships
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
                    ->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
                    ->withTimestamps();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function follow(User $user): void
    {
        if (!$this->isFollowing($user) && $this->id !== $user->id) {
            $this->following()->attach($user->id);

            // Dispatch event for milestone detection
            event(new \App\Events\UserFollowed($this, $user));
        }
    }

    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }

    // AI Subscription Methods
    public function hasAISubscription(): bool
    {
        return in_array($this->ai_tier, ['basic', 'premium', 'enterprise']);
    }

    public function isAITierExpired(): bool
    {
        if (!$this->ai_tier_expires_at) {
            return false;
        }

        return now()->greaterThan($this->ai_tier_expires_at);
    }

    public function canGenerateAIContent(): bool
    {
        // Free tier requires API key
        if ($this->ai_tier === 'free') {
            return !empty($this->groq_api_key);
        }

        // Paid tiers check expiration
        if ($this->isAITierExpired()) {
            return false;
        }

        // Check quota
        if ($this->monthly_ai_posts_limit === null) {
            return true; // Unlimited
        }

        return $this->ai_posts_generated < $this->monthly_ai_posts_limit;
    }

    public function canGenerateAIImage(): bool
    {
        // Free tier requires API key
        if ($this->ai_tier === 'free') {
            return !empty($this->unsplash_api_key);
        }

        // Paid tiers check expiration
        if ($this->isAITierExpired()) {
            return false;
        }

        // Check quota
        if ($this->monthly_ai_images_limit === null) {
            return true; // Unlimited
        }

        return $this->ai_images_generated < $this->monthly_ai_images_limit;
    }

    public function getAIContentQuotaRemaining(): int|string
    {
        if ($this->monthly_ai_posts_limit === null) {
            return 'unlimited';
        }

        return max(0, $this->monthly_ai_posts_limit - $this->ai_posts_generated);
    }

    public function getAIImageQuotaRemaining(): int|string
    {
        if ($this->monthly_ai_images_limit === null) {
            return 'unlimited';
        }

        return max(0, $this->monthly_ai_images_limit - $this->ai_images_generated);
    }

    public function getAITierName(): string
    {
        return match($this->ai_tier) {
            'free' => 'Free',
            'basic' => 'Basic ($9.99/mo)',
            'premium' => 'Premium ($29.99/mo)',
            'enterprise' => 'Enterprise ($99.99/mo)',
            default => 'Free',
        };
    }

    public function resetAIQuota(): void
    {
        $this->update([
            'ai_posts_generated' => 0,
            'ai_images_generated' => 0,
            'ai_usage_reset_date' => now()->addMonth()->startOfMonth(),
        ]);
    }

    public function upgradeAITier(string $tier, int $months = 1): void
    {
        $limits = [
            'free' => ['posts' => 5, 'images' => 10],
            'basic' => ['posts' => 50, 'images' => 100],
            'premium' => ['posts' => null, 'images' => null],
            'enterprise' => ['posts' => null, 'images' => null],
        ];

        $tierLimits = $limits[$tier] ?? $limits['free'];

        $this->update([
            'ai_tier' => $tier,
            'monthly_ai_posts_limit' => $tierLimits['posts'],
            'monthly_ai_images_limit' => $tierLimits['images'],
            'ai_tier_starts_at' => now(),
            'ai_tier_expires_at' => now()->addMonths($months),
            'ai_usage_reset_date' => now()->addMonth()->startOfMonth(),
        ]);
    }

    public function downgradeAITier(): void
    {
        $this->update([
            'ai_tier' => 'free',
            'monthly_ai_posts_limit' => 5,
            'monthly_ai_images_limit' => 10,
            'ai_tier_starts_at' => null,
            'ai_tier_expires_at' => null,
        ]);
    }
}
