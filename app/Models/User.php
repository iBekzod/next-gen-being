<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    use HasFactory, Notifiable, Billable, InteractsWithMedia;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio', 'website',
        'twitter', 'linkedin', 'is_active', 'last_seen_at'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['admin', 'content_manager', 'lead']);
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

        public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isPremium(): bool
    {
        $subscription = $this->subscription;
        return $subscription && $subscription->isActive();
    }

    public function isOnTrial(): bool
    {
        $subscription = $this->subscription;
        return $subscription && $subscription->onTrial();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->isPremium();
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
        }
    }

    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }
}
