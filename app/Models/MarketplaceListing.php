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

    /**
     * Social proof is only ever shown when it is real.
     */
    public function hasSales(): bool
    {
        return (int) $this->sales_count > 0;
    }

    public function hasReviews(): bool
    {
        return (int) $this->reviews_count > 0 && (float) $this->rating > 0;
    }

    /**
     * Recompute denormalised stats from real data. sales_count = completed,
     * paid purchases of this listing's tiers (the free prompt magnet is not a
     * sale). There is no listing review system yet, so rating/reviews_count
     * stay 0 until one exists - never invent them.
     */
    public function refreshStats(): void
    {
        $sales = ProductPurchase::query()
            ->where('status', 'completed')
            ->where('amount', '>', 0)
            ->whereIn('digital_product_id', $this->tiers()->withoutGlobalScopes()->select('digital_products.id'))
            ->count();

        $this->forceFill(['sales_count' => $sales, 'reviews_count' => 0, 'rating' => 0])->saveQuietly();
    }
}
