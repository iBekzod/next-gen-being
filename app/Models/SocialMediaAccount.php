<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialMediaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'account_type',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'platform_user_id',
        'platform_username',
        'account_name',
        'account_avatar',
        'follower_count',
        'auto_publish',
        'publish_schedule',
        'is_active',
        'last_published_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'publish_schedule' => 'array',
        'auto_publish' => 'boolean',
        'is_active' => 'boolean',
        'last_published_at' => 'datetime',
        'follower_count' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialMediaPosts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class);
    }

    // Helper methods
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return now()->greaterThan($this->token_expires_at);
    }

    public function isPlatformOfficial(): bool
    {
        return $this->account_type === 'platform_official';
    }

    public function canAutoPublish(): bool
    {
        return $this->is_active && $this->auto_publish && !$this->isTokenExpired();
    }

    public function getPlatformDisplayName(): string
    {
        return match($this->platform) {
            'youtube' => 'YouTube',
            'instagram' => 'Instagram',
            'twitter' => 'Twitter/X',
            'linkedin' => 'LinkedIn',
            'tiktok' => 'TikTok',
            'facebook' => 'Facebook',
            'telegram' => 'Telegram',
            'pinterest' => 'Pinterest',
            default => ucfirst($this->platform),
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeOfficial($query)
    {
        return $query->where('account_type', 'platform_official');
    }

    public function scopePersonal($query)
    {
        return $query->where('account_type', 'personal');
    }
}
