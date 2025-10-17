<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Cache;

class Post extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia, Searchable;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'content_json',
        'featured_image', 'image_attribution', 'gallery', 'status', 'published_at',
        'scheduled_at', 'is_featured', 'allow_comments', 'is_premium',
        'premium_tier', 'preview_percentage', 'paywall_message',
        'read_time', 'views_count', 'likes_count', 'comments_count',
        'bookmarks_count', 'seo_meta', 'author_id', 'category_id'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'is_premium' => 'boolean',
        'gallery' => 'array',
        'seo_meta' => 'array',
        'image_attribution' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->comments()->approved();
    }

    public function interactions()
    {
        return $this->morphMany(UserInteraction::class, 'interactable');
    }

    public function likes()
    {
        return $this->interactions()->where('type', 'like');
    }

    public function bookmarks()
    {
        return $this->interactions()->where('type', 'bookmark');
    }

    public function views()
    {
        return $this->interactions()->where('type', 'view');
    }

    public function socialShares()
    {
        return $this->hasMany(SocialShare::class);
    }

    public function contentViews()
    {
        return $this->hasMany(ContentView::class);
    }

    public function paywallInteractions()
    {
        return $this->hasMany(PaywallInteraction::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
    }

    public function scopeByTag($query, $tagSlug)
    {
        return $query->whereHas('tags', fn($q) => $q->where('slug', $tagSlug));
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('views_count')
                    ->orderByDesc('likes_count');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at');
    }

    // Methods
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               $this->published_at &&
               $this->published_at->isPast();
    }

    public function canBeViewedBy(?User $user): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        if (!$this->is_premium) {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $this->userHasRequiredTier($user);
    }

    public function userHasRequiredTier(?User $user): bool
    {
        if (!$user || (!$user->subscribed() && !$user->onTrial())) {
            return false;
        }

        if ($this->premium_tier === null) {
            return true;
        }

        $userTier = $user->getSubscriptionTier();
        if (!$userTier) {
            return false;
        }

        $tierHierarchy = ['basic' => 1, 'pro' => 2, 'team' => 3];
        $userLevel = $tierHierarchy[$userTier] ?? 0;
        $requiredLevel = $tierHierarchy[$this->premium_tier] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    public function getTierDisplayName(): string
    {
        return match($this->premium_tier) {
            'basic' => 'Basic',
            'pro' => 'Pro',
            'team' => 'Team',
            default => 'Premium',
        };
    }

    public function getMinimumTierPrice(): string
    {
        return match($this->premium_tier) {
            'basic' => '$9.99',
            'pro' => '$19.99',
            'team' => '$49.99',
            default => '$9.99',
        };
    }

    public function recordView(?User $user = null): void
    {
        if ($user) {
            $this->interactions()->updateOrCreate([
                'user_id' => $user->id,
                'type' => 'view'
            ], [
                'created_at' => now()
            ]);
        }

        $this->increment('views_count');
    }

    public function calculateReadTime(): int
    {
        $wordsPerMinute = 200;
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($post) {
            if (!$post->read_time) {
                $post->read_time = $post->calculateReadTime();
            }
        });

        static::saved(function () {
            Cache::forget('seo:sitemap.xml');
        });

        static::deleted(function () {
            Cache::forget('seo:sitemap.xml');
        });
    }

    // Search
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => strip_tags($this->content ?? ''),
            'category' => $this->category?->name ?? '',
            'tags' => $this->tags->pluck('name')->join(' '),
            'author' => $this->author?->name ?? '',
        ];
    }
}


