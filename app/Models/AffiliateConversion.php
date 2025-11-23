<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_link_id',
        'click_id',
        'user_id',
        'conversion_type', // 'signup', 'subscription', 'purchase', 'custom'
        'conversion_value',
        'commission_rate',
        'commission_amount',
        'status', // 'pending', 'completed', 'refunded'
        'metadata',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REFUNDED = 'refunded';

    // Relationships
    public function link(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id');
    }

    public function click(): BelongsTo
    {
        return $this->belongsTo(AffiliateClick::class, 'click_id')->nullable();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('conversion_type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function refund(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'metadata' => array_merge($this->metadata ?? [], ['refund_reason' => $reason]),
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public static function createFromClick(AffiliateClick $click, string $type, float $value, float $commissionRate): self
    {
        $commissionAmount = $value * ($commissionRate / 100);

        return self::create([
            'affiliate_link_id' => $click->link_id,
            'click_id' => $click->id,
            'user_id' => $click->user_id,
            'conversion_type' => $type,
            'conversion_value' => $value,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'status' => self::STATUS_PENDING,
        ]);
    }
}
