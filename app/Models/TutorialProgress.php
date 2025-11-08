<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorialProgress extends Model
{
    use HasFactory;

    protected $table = 'tutorial_progress';

    protected $fillable = [
        'user_id',
        'post_id',
        'series_slug',
        'series_part',
        'completed',
        'read_count',
        'started_at',
        'completed_at',
        'time_spent_minutes',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopeInSeries($query, $seriesSlug)
    {
        return $query->where('series_slug', $seriesSlug);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function markAsRead()
    {
        $this->read_count++;
        $this->started_at ??= now();
        $this->save();
    }

    public function markAsCompleted()
    {
        $this->completed = true;
        $this->completed_at ??= now();
        $this->save();
    }

    public function addTimeSpent($minutes)
    {
        $this->time_spent_minutes += $minutes;
        $this->save();
    }
}
