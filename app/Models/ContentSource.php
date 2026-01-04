<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContentSource extends Model
{
    use HasFactory;

    protected $table = 'content_sources';

    protected $fillable = [
        'name',
        'url',
        'category',
        'language',
        'trust_level',
        'scraping_enabled',
        'description',
        'css_selectors',
        'rate_limit_per_sec',
        'last_scraped_at',
    ];

    protected $casts = [
        'scraping_enabled' => 'boolean',
        'trust_level' => 'integer',
        'rate_limit_per_sec' => 'integer',
        'last_scraped_at' => 'datetime',
        'css_selectors' => 'json',
    ];

    // Relationships
    public function collectedContent()
    {
        return $this->hasMany(CollectedContent::class, 'content_source_id');
    }

    public function aggregations()
    {
        return $this->belongsToMany(
            ContentAggregation::class,
            'content_aggregation_sources',
            'content_source_id',
            'aggregation_id'
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('scraping_enabled', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeHighTrust($query)
    {
        return $query->where('trust_level', '>=', 80);
    }

    public function scopeNeedsScraping($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('last_scraped_at')
                  ->orWhere('last_scraped_at', '<', now()->subHours(24));
            });
    }

    // Methods
    public function markAsScraped(): void
    {
        $this->update(['last_scraped_at' => now()]);
    }

    public function isHighTrust(): bool
    {
        return $this->trust_level >= 80;
    }

    public function getUniqueName(): string
    {
        return "{$this->name} ({$this->category})";
    }

    public function canScrape(): bool
    {
        return $this->scraping_enabled && $this->isHighTrust();
    }

    public function getLastScrapedAgo(): ?string
    {
        if (!$this->last_scraped_at) {
            return 'Never';
        }

        return $this->last_scraped_at->diffForHumans();
    }
}
