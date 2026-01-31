<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DigitalProduct extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'creator_id', 'title', 'slug', 'description', 'short_description',
        'type', 'price', 'original_price', 'tier_required', 'is_free',
        'file_path', 'preview_file_path', 'files', 'thumbnail', 'gallery',
        'tags', 'category', 'content', 'features', 'includes',
        'seo_meta', 'status', 'published_at', 'revenue_share_percentage',
        'lemonsqueezy_product_id', 'lemonsqueezy_variant_id',
        'downloads_count', 'purchases_count', 'rating', 'reviews_count'
    ];

    protected $casts = [
        'files' => 'array',
        'gallery' => 'array',
        'tags' => 'array',
        'features' => 'array',
        'includes' => 'array',
        'seo_meta' => 'array',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'revenue_share_percentage' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_free' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function purchases()
    {
        return $this->hasMany(ProductPurchase::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false);
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('downloads_count');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function isPurchasedBy($user)
    {
        if (!$user) {
            return false;
        }
        return $this->purchases()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();
    }

    // Methods
    public function incrementDownloads()
    {
        $this->increment('downloads_count');
    }

    public function incrementPurchases()
    {
        $this->increment('purchases_count');
    }

    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
