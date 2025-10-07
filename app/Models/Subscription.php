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
        'paddle_id',
        'paddle_subscription_id',
        'paddle_price_id',
        'name',
        'status',
        'quantity',
        'card_brand',
        'card_last_four',
        'trial_ends_at',
        'renews_at',
        'ends_at',
        'paused_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'renews_at' => 'datetime',
        'ends_at' => 'datetime',
        'paused_at' => 'datetime',
        'quantity' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
               (!$this->ends_at || $this->ends_at->isFuture());
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
