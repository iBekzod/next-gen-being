<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id', 'title', 'slug', 'tagline', 'description', 'category', 'tags',
        'thumbnail', 'demo_type', 'demo_path', 'demo_url', 'status', 'is_featured',
        'plagiarism_status', 'rating', 'reviews_count', 'sales_count', 'published_at',
    ];

    protected $casts = [
        'tags'         => 'array',
        'is_featured'  => 'boolean',
        'rating'       => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Tiers are ordinary DigitalProducts grouped by listing_id, ordered cheapest-first
     * (naturally prompt -> design -> code -> bundle by price).
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(DigitalProduct::class, 'listing_id')->orderBy('price');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function cheapestPrice(): float
    {
        return (float) ($this->tiers()->min('price') ?? 0);
    }
}
