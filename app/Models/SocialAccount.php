<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    protected $table = 'social_accounts';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_email',
        'provider_name',
        'avatar_url',
        'access_token',
        'refresh_token',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'json',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByProviderIdAndProvider($query, string $providerId, string $provider)
    {
        return $query->where('provider_id', $providerId)
                     ->where('provider', $provider);
    }

    // Methods
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->isAfter($this->expires_at);
    }

    public function getProviderDisplayName(): string
    {
        $names = [
            'google' => 'Google',
            'github' => 'GitHub',
            'discord' => 'Discord',
            'twitter' => 'X (Twitter)',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
        ];

        return $names[strtolower($this->provider)] ?? ucfirst($this->provider);
    }

    public function getProviderIcon(): string
    {
        $icons = [
            'google' => '🔵',
            'github' => '⚫',
            'discord' => '💜',
            'twitter' => '⚫',
            'facebook' => '🔵',
            'linkedin' => '🔷',
        ];

        return $icons[strtolower($this->provider)] ?? '🔗';
    }
}
