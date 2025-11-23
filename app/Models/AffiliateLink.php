<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'referral_code',
        'affiliate_url',
        'commission_rate',
        'description',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCreator($query, User $creator)
    {
        return $query->where('creator_id', $creator->id);
    }

    // Helper methods
    public function generateCode(): string
    {
        return Str::slug($this->creator->username) . '_' . Str::random(6);
    }

    public function getShareableUrl(): string
    {
        return route('affiliate.click', ['code' => $this->referral_code]);
    }

    public function getClickCountAttribute(): int
    {
        return $this->clicks()->count();
    }

    public function getConversionCountAttribute(): int
    {
        return $this->conversions()->count();
    }

    public function getConversionRateAttribute(): float
    {
        if ($this->getClickCountAttribute() === 0) {
            return 0;
        }

        return round(($this->getConversionCountAttribute() / $this->getClickCountAttribute()) * 100, 2);
    }

    public function getTotalEarningsAttribute(): float
    {
        return (float) $this->conversions()
            ->where('status', 'completed')
            ->sum('commission_amount');
    }

    public static function createForCreator(User $creator, array $data): self
    {
        $link = new self($data);
        $link->creator_id = $creator->id;
        $link->referral_code = $link->generateCode();
        $link->is_active = true;
        $link->save();

        return $link;
    }
}
