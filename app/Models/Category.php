<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name', 'slug', 'description', 'color', 'icon',
        'is_active', 'sort_order', 'meta_title', 'meta_description',
        'meta_keywords', 'seo_schema'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seo_schema' => 'json',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function publishedPosts()
    {
        return $this->posts()->published();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
    /**
     * Auto-generate meta title if not set
     * Falls back to: "{Category Name} Articles - {Site Name}"
     */
    public function getMetaTitle(): string
    {
        if (!empty($this->meta_title)) {
            return $this->meta_title;
        }

        $siteName = setting('site_name', 'NextGenBeing');
        return "{$this->name} Articles - {$siteName}";
    }

    /**
     * Auto-generate meta description if not set
     * Falls back to: actual description if exists, or "{Category Name} articles and insights on {Site Name}"
     */
    public function getMetaDescription(): string
    {
        if (!empty($this->meta_description)) {
            return $this->meta_description;
        }

        if (!empty($this->description)) {
            // Truncate to 160 characters for meta description
            return substr($this->description, 0, 160);
        }

        $siteName = setting('site_name', 'NextGenBeing');
        return "Explore {$this->name} articles and insights on {$siteName}";
    }

    /**
     * Auto-generate meta keywords if not set
     * Falls back to: category name + top tags from posts + generic keywords
     */
    public function getMetaKeywords(): string
    {
        if (!empty($this->meta_keywords)) {
            return $this->meta_keywords;
        }

        $keywords = [$this->name];

        // Add top tags from posts in this category
        $topTags = $this->publishedPosts()
            ->with('tags')
            ->limit(10)
            ->get()
            ->pluck('tags')
            ->flatten()
            ->pluck('name')
            ->unique()
            ->take(3)
            ->toArray();

        $keywords = array_merge($keywords, $topTags);
        $keywords[] = 'blog';
        $keywords[] = 'articles';

        return implode(', ', array_unique($keywords));
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('seo:sitemap.xml');
        });

        static::deleted(function () {
            Cache::forget('seo:sitemap.xml');
        });
    }

}



