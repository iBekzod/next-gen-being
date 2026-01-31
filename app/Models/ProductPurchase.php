<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ProductPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'digital_product_id', 'amount', 'currency', 'status',
        'lemonsqueezy_order_id', 'lemonsqueezy_receipt_url',
        'license_key', 'download_count', 'download_limit', 'expires_at',
        'creator_revenue', 'platform_revenue', 'creator_paid'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'creator_revenue' => 'decimal:2',
        'platform_revenue' => 'decimal:2',
        'creator_paid' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(DigitalProduct::class, 'digital_product_id');
    }

    // Methods
    public function canDownload()
    {
        if ($this->status !== 'completed') {
            return false;
        }
        if ($this->download_count >= $this->download_limit) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }

    public function incrementDownload()
    {
        if ($this->canDownload()) {
            $this->increment('download_count');
            return true;
        }
        return false;
    }

    public static function generateLicenseKey()
    {
        return strtoupper(
            Str::random(8) . '-' .
            Str::random(8) . '-' .
            Str::random(8)
        );
    }
}
