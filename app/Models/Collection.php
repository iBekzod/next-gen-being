<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'name',
        'description',
        'cover_image_url',
        'is_public',
        'is_featured',
        'view_count',
        'saved_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the creator of this collection
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for creator() to work with Filament resources
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all posts in this collection
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'collection_posts')
            ->withPivot('order', 'added_at')
            ->orderBy('collection_posts.order')
            ->withTimestamps();
    }

    /**
     * Get collection items (pivots with metadata)
     */
    public function items(): HasMany
    {
        return $this->hasMany(CollectionPost::class);
    }

    /**
     * Get users who saved this collection
     */
    public function savedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_collections')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCreator($query, User $creator)
    {
        return $query->where('user_id', $creator->id);
    }

    public function scopeTrending($query, $days = 7)
    {
        return $query->public()
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('view_count')
            ->orderByDesc('saved_count');
    }

    /**
     * Helper Methods
     */
    public function getPostCountAttribute(): int
    {
        return $this->posts()->count();
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function incrementSaves(): void
    {
        $this->increment('saved_count');
    }

    public function isSavedBy(User $user): bool
    {
        return $this->savedBy()->where('user_id', $user->id)->exists();
    }

    /**
     * Add a post to the collection
     */
    public function addPost(Post $post, int $order = 0): CollectionPost
    {
        $maxOrder = $this->items()->max('order') ?? 0;

        return $this->items()->create([
            'post_id' => $post->id,
            'order' => $order === 0 ? $maxOrder + 1 : $order,
            'added_at' => now(),
        ]);
    }

    /**
     * Remove a post from the collection
     */
    public function removePost(Post $post): int
    {
        return $this->items()->where('post_id', $post->id)->delete();
    }

    /**
     * Reorder posts in collection
     */
    public function reorderPosts(array $postIds): void
    {
        foreach ($postIds as $order => $postId) {
            $this->items()->where('post_id', $postId)->update(['order' => $order + 1]);
        }
    }
}
