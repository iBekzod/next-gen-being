<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'token',
        'frequency',
        'preferences',
        'is_active',
        'verified_at',
        'last_sent_at',
    ];

    protected $casts = [
        'preferences' => 'array',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->token)) {
                $subscription->token = Str::random(32);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function engagements()
    {
        return $this->hasMany(NewsletterEngagement::class, 'subscription_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    public function scopeDueForNewsletter($query, $frequency = 'weekly')
    {
        $interval = match($frequency) {
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
        };

        return $query->active()
            ->verified()
            ->frequency($frequency)
            ->where(function($q) use ($interval) {
                $q->whereNull('last_sent_at')
                  ->orWhere('last_sent_at', '<=', $interval);
            });
    }

    // Methods
    public function verify()
    {
        $this->update(['verified_at' => now()]);
    }

    public function unsubscribe()
    {
        $this->update(['is_active' => false]);
    }

    public function updatePreferences(array $preferences)
    {
        $this->update(['preferences' => array_merge($this->preferences ?? [], $preferences)]);
    }

    public function markAsSent()
    {
        $this->update(['last_sent_at' => now()]);
    }
}
