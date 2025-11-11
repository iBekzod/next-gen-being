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
        'ai_usage_reset_date',
        'video_tier', 'videos_generated', 'monthly_video_limit',
        'video_tier_starts_at', 'video_tier_expires_at',
        'custom_video_intro_url', 'custom_video_outro_url', 'custom_video_logo_url',
        'oauth_provider', 'oauth_provider_id', 'password_updated_at'
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
        'video_tier_starts_at' => 'datetime',
        'video_tier_expires_at' => 'datetime',
        'password_updated_at' => 'datetime',
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

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
                    ->withPivot('achieved_at')
                    ->withTimestamps();
    }

    public function tutorialProgress()
    {
        return $this->hasMany(TutorialProgress::class);
    }

    public function learningPaths()
    {
        return $this->hasMany(LearningPath::class);
    }

    public function aiRecommendations()
    {
        return $this->hasMany(AIRecommendation::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
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

    // Social Media Relationships
    public function socialMediaAccounts()
    {
        return $this->hasMany(SocialMediaAccount::class);
    }

    public function videoGenerations()
    {
        return $this->hasMany(VideoGeneration::class);
    }

    // Video Tier Methods
    public function hasVideoProSubscription(): bool
    {
        return $this->video_tier === 'video_pro';
    }

    public function isVideoTierExpired(): bool
    {
        if (!$this->video_tier_expires_at) {
            return false;
        }

        return now()->greaterThan($this->video_tier_expires_at);
    }

    public function canGenerateVideo(): bool
    {
        // Free tier cannot generate videos
        if ($this->video_tier === 'free') {
            return false;
        }

        // Check if subscription expired
        if ($this->isVideoTierExpired()) {
            return false;
        }

        // Video Pro has unlimited generations
        if ($this->monthly_video_limit === null) {
            return true;
        }

        return $this->videos_generated < $this->monthly_video_limit;
    }

    public function getVideosRemainingQuota(): int|string
    {
        if ($this->video_tier === 'free') {
            return 0;
        }

        if ($this->monthly_video_limit === null) {
            return 'unlimited';
        }

        return max(0, $this->monthly_video_limit - $this->videos_generated);
    }

    public function upgradeToVideoPro(int $months = 1): void
    {
        $this->update([
            'video_tier' => 'video_pro',
            'monthly_video_limit' => null, // unlimited
            'videos_generated' => 0,
            'video_tier_starts_at' => now(),
            'video_tier_expires_at' => now()->addMonths($months),
        ]);
    }

    public function downgradeVideoTier(): void
    {
        $this->update([
            'video_tier' => 'free',
            'monthly_video_limit' => 0,
            'videos_generated' => 0,
            'video_tier_starts_at' => null,
            'video_tier_expires_at' => null,
            'custom_video_intro_url' => null,
            'custom_video_outro_url' => null,
            'custom_video_logo_url' => null,
        ]);
    }

    public function getConnectedPlatforms(): array
    {
        return $this->socialMediaAccounts()
                    ->where('is_active', true)
                    ->pluck('platform')
                    ->toArray();
    }

    public function hasPlatformConnected(string $platform): bool
    {
        return $this->socialMediaAccounts()
                    ->where('platform', $platform)
                    ->where('is_active', true)
                    ->exists();
    }

    /**
     * Get user's notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get user's notification preferences
     */
    public function notificationPreferences()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Get or create notification preferences
     */
    public function getNotificationPreferences()
    {
        return $this->notificationPreferences ?? $this->notificationPreferences()->create([]);
    }

    /**
     * Get unread notification count
     */
    public function unreadNotificationCount(): int
    {
        return Notification::unreadCountFor($this);
    }

    /**
     * Get unread notifications
     */
    public function getUnreadNotifications(int $limit = 10)
    {
        return Notification::getUnreadFor($this, $limit);
    }

    /**
     * Get user's badges
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withTimestamps()
            ->withPivot('earned_at')
            ->orderByPivot('earned_at', 'desc');
    }

    /**
     * Get user's reputation
     */
    public function reputation()
    {
        return $this->hasOne(UserReputation::class);
    }

    /**
     * Get or create reputation record
     */
    public function getOrCreateReputation()
    {
        return $this->reputation ?? $this->reputation()->create();
    }

    /**
     * Check if user has badge
     */
    public function hasBadge(string $badgeSlug): bool
    {
        return $this->badges()->where('slug', $badgeSlug)->exists();
    }

    /**
     * Notify user of level up
     */
    public function notifyLevelUp(string $newLevel): void
    {
        // Create notification
        app('NotificationService')->create(
            user: $this,
            type: 'earnings_milestone',
            title: 'Level Up!',
            message: "Congratulations! You've reached {$newLevel} level!",
            actionUrl: route('dashboard.reputation'),
            notifiable: $this,
            data: ['level' => $newLevel]
        );
    }

    // Collaboration Relationships
    public function collaborations()
    {
        return $this->hasMany(PostCollaborator::class, 'user_id');
    }

    public function collaboratedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_collaborators', 'user_id', 'post_id')
                    ->withPivot('role', 'joined_at', 'left_at')
                    ->withTimestamps();
    }

    public function receivedInvitations()
    {
        return $this->hasMany(CollaborationInvitation::class, 'user_id');
    }

    public function sentInvitations()
    {
        return $this->hasMany(CollaborationInvitation::class, 'inviter_id');
    }

    public function collaborationComments()
    {
        return $this->hasMany(CollaborationComment::class, 'user_id');
    }

    public function collaborationActivities()
    {
        return $this->hasMany(CollaborationActivity::class, 'user_id');
    }

    // Collaboration Helper Methods
    public function getCollaboratedPosts(int $limit = 10)
    {
        return $this->collaboratedPosts()
                    ->wherePivotNull('left_at')
                    ->latest('updated_at')
                    ->limit($limit)
                    ->get();
    }

    public function getPendingInvitations(int $limit = 10)
    {
        return $this->receivedInvitations()
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now())
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();
    }

    public function getActiveCollaborations()
    {
        return $this->collaborations()
                    ->whereNull('left_at')
                    ->with('post')
                    ->get();
    }
}
