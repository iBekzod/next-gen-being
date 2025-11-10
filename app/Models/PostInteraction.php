<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that made the interaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post being interacted with
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Scope to filter by interaction type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for likes
     */
    public function scopeLikes($query)
    {
        return $query->ofType('like');
    }

    /**
     * Scope for views
     */
    public function scopeViews($query)
    {
        return $query->ofType('view');
    }

    /**
     * Scope for comments
     */
    public function scopeComments($query)
    {
        return $query->ofType('comment');
    }

    /**
     * Scope for shares
     */
    public function scopeShares($query)
    {
        return $query->ofType('share');
    }

    /**
     * Scope for interactions in the last N days
     */
    public function scopeInLastDays($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
