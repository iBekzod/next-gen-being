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
        'bookmarks_count', 'seo_meta', 'author_id', 'category_id',
        'series_title', 'series_slug', 'series_part', 'series_total_parts', 'series_description',
        'moderation_status', 'moderated_by', 'moderated_at', 'moderation_notes', 'ai_moderation_check',
        'post_type', 'video_url', 'video_duration', 'video_thumbnail', 'video_captions_url'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'moderated_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'is_premium' => 'boolean',
        'gallery' => 'array',
        'seo_meta' => 'array',
        'image_attribution' => 'array',
        'ai_moderation_check' => 'array',
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

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
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

    // Series relationships
    public function seriesPosts()
    {
        return $this->hasMany(Post::class, 'series_slug', 'series_slug')
            ->orderBy('series_part');
    }

    public function nextInSeries()
    {
        if (!$this->series_slug) {
            return null;
        }

        return Post::published()
            ->where('series_slug', $this->series_slug)
            ->where('series_part', $this->series_part + 1)
            ->first();
    }

    public function previousInSeries()
    {
        if (!$this->series_slug) {
            return null;
        }

        return Post::published()
            ->where('series_slug', $this->series_slug)
            ->where('series_part', $this->series_part - 1)
            ->first();
    }

    public function isPartOfSeries(): bool
    {
        return !empty($this->series_slug);
    }

    public function getSeriesProgress(): ?int
    {
        if (!$this->isPartOfSeries() || !$this->series_total_parts) {
            return null;
        }

        return round(($this->series_part / $this->series_total_parts) * 100);
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

    public function scopeInSeries($query, $seriesSlug)
    {
        return $query->where('series_slug', $seriesSlug)->orderBy('series_part');
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

    // Moderation scopes
    public function scopePendingModeration($query)
    {
        return $query->where('moderation_status', 'pending');
    }

    public function scopeModeratedApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    public function scopeModeratedRejected($query)
    {
        return $query->where('moderation_status', 'rejected');
    }

    // Methods
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               $this->published_at &&
               $this->published_at->isPast();
    }

    // Moderation methods
    public function isPendingModeration(): bool
    {
        return $this->moderation_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->moderation_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->moderation_status === 'rejected';
    }

    public function approve(User $moderator, ?string $notes = null): bool
    {
        $this->moderation_status = 'approved';
        $this->moderated_by = $moderator->id;
        $this->moderated_at = now();
        $this->moderation_notes = $notes;

        return $this->save();
    }

    public function reject(User $moderator, string $reason): bool
    {
        $this->moderation_status = 'rejected';
        $this->moderated_by = $moderator->id;
        $this->moderated_at = now();
        $this->moderation_notes = $reason;
        $this->status = 'draft'; // Move back to draft

        return $this->save();
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

    /**
     * Get the preview content for non-premium users (30% by default)
     */
    public function getPreviewContent(): string
    {
        if (!$this->is_premium) {
            return $this->content;
        }

        $percentage = $this->preview_percentage ?? 30;
        $content = strip_tags($this->content, '<p><br><h1><h2><h3><h4><h5><h6><strong><em><ul><ol><li><a><blockquote><code><pre>');

        // Calculate character count for preview
        $totalLength = strlen($content);
        $previewLength = (int) ($totalLength * ($percentage / 100));

        // Try to break at a sentence or paragraph
        $preview = substr($content, 0, $previewLength);

        // Find the last sentence ending
        $lastPeriod = max(
            strrpos($preview, '.'),
            strrpos($preview, '!'),
            strrpos($preview, '?'),
            strrpos($preview, '</p>')
        );

        if ($lastPeriod !== false && $lastPeriod > ($previewLength * 0.8)) {
            $preview = substr($content, 0, $lastPeriod + 1);
        }

        return $preview;
    }

    /**
     * Check if user should see the paywall
     */
    public function shouldShowPaywall(?User $user): bool
    {
        return $this->is_premium && !$this->canBeViewedBy($user);
    }

    /**
     * Get the paywall message
     */
    public function getPaywallMessage(): string
    {
        return $this->paywall_message ?? 'This is premium content. Subscribe to read the full article and unlock all premium features.';
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

    // Social Media & Video Relationships
    public function socialMediaPosts()
    {
        return $this->hasMany(SocialMediaPost::class);
    }

    public function videoGenerations()
    {
        return $this->hasMany(VideoGeneration::class);
    }

    // Video Helper Methods
    public function isArticle(): bool
    {
        return $this->post_type === 'article';
    }

    public function isVisualStory(): bool
    {
        return $this->post_type === 'visual_story';
    }

    public function isVideoBlog(): bool
    {
        return $this->post_type === 'video_blog';
    }

    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }

    public function getFormattedVideoDuration(): ?string
    {
        if (!$this->video_duration) {
            return null;
        }

        $minutes = floor($this->video_duration / 60);
        $seconds = $this->video_duration % 60;

        if ($minutes > 0) {
            return sprintf('%d:%02d', $minutes, $seconds);
        }

        return "{$seconds}s";
    }

    public function hasBeenPublishedToSocialMedia(): bool
    {
        return $this->socialMediaPosts()->published()->exists();
    }

    public function getPublishedPlatforms(): array
    {
        return $this->socialMediaPosts()
                    ->published()
                    ->pluck('platform')
                    ->unique()
                    ->toArray();
    }

    public function hasVideoGeneration(): bool
    {
        return $this->videoGenerations()->completed()->exists();
    }

    public function getLatestVideo(?string $type = null)
    {
        $query = $this->videoGenerations()->completed();

        if ($type) {
            $query->where('video_type', $type);
        }

        return $query->latest()->first();
    }

    // Scopes
    public function scopeArticles($query)
    {
        return $query->where('post_type', 'article');
    }

    public function scopeVisualStories($query)
    {
        return $query->where('post_type', 'visual_story');
    }

    public function scopeVideoBlogs($query)
    {
        return $query->where('post_type', 'video_blog');
    }
}


