<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Cache;

class Tag extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'slug', 'color', 'is_active', 'usage_count', 'meta_title', 'meta_description', 'meta_keywords'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }

    public function publishedPosts()
    {
        return $this->posts()->published();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderByDesc('usage_count')->limit($limit);
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function decrementUsage()
    {
        $this->decrement('usage_count');
    }

    /**
     * Auto-generate meta title if not set
     * Falls back to: "#{Tag Name} Articles - {Site Name}"
     */
    public function getMetaTitle(): string
    {
        if (!empty($this->meta_title)) {
            return $this->meta_title;
        }

        $siteName = setting('site_name', 'NextGenBeing');
        return "#{$this->name} Articles - {$siteName}";
    }

    /**
     * Auto-generate meta description if not set
     * Falls back to: "Articles tagged with {Tag Name} on {Site Name}"
     */
    public function getMetaDescription(): string
    {
        if (!empty($this->meta_description)) {
            return $this->meta_description;
        }

        $siteName = setting('site_name', 'NextGenBeing');
        $count = $this->publishedPosts()->count();
        $postText = $count === 1 ? 'article' : 'articles';

        return "Discover {$count} {$postText} tagged with #{$this->name} on {$siteName}";
    }

    /**
     * Auto-generate meta keywords if not set
     * Falls back to: tag name + related tags + generic keywords
     */
    public function getMetaKeywords(): string
    {
        if (!empty($this->meta_keywords)) {
            return $this->meta_keywords;
        }

        $keywords = [$this->name];

        // Add related tags (tags from posts with this tag)
        $relatedTags = $this->publishedPosts()
            ->with('tags')
            ->limit(15)
            ->get()
            ->pluck('tags')
            ->flatten()
            ->where('id', '!=', $this->id)
            ->pluck('name')
            ->unique()
            ->take(2)
            ->toArray();

        $keywords = array_merge($keywords, $relatedTags);
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

