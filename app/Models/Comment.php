<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content', 'status', 'likes_count', 'replies_count',
        'post_id', 'user_id', 'parent_id'
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'replies_count' => 'integer',
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function approvedReplies()
    {
        return $this->replies()->approved();
    }

    public function interactions()
    {
        return $this->morphMany(UserInteraction::class, 'interactable');
    }

    public function likes()
    {
        return $this->interactions()->where('type', 'like');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Methods
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved']);
        $this->post->increment('comments_count');

        if ($this->parent_id) {
            $this->parent->increment('replies_count');
        }
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }
}
