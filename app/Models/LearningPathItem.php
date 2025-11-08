<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathItem extends Model
{
    use HasFactory;

    protected $table = 'learning_path_items';

    protected $fillable = [
        'learning_path_id',
        'post_id',
        'order',
        'title',
        'description',
        'reason_for_recommendation',
        'difficulty_level',
        'estimated_duration_minutes',
        'completed',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'metadata' => 'json',
    ];

    // Relationships
    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
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

    public function scopePending($query)
    {
        return $query->where('completed', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // Methods
    public function markAsCompleted()
    {
        $this->completed = true;
        $this->completed_at = now();
        $this->save();
    }

    public function getEstimatedTimeLabel(): string
    {
        if ($this->estimated_duration_minutes < 60) {
            return $this->estimated_duration_minutes . ' min';
        }
        $hours = round($this->estimated_duration_minutes / 60, 1);
        return $hours . ' hrs';
    }

    public function getDifficultyColor(): string
    {
        return match($this->difficulty_level) {
            'beginner' => 'green',
            'intermediate' => 'blue',
            'advanced' => 'orange',
            'expert' => 'red',
            default => 'gray',
        };
    }
}
