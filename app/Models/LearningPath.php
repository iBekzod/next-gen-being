<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningPath extends Model
{
    use HasFactory;

    protected $table = 'learning_paths';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'goal',
        'skill_level',
        'estimated_duration_hours',
        'status',
        'ai_generated',
        'metadata',
        'generated_at',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'generated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LearningPathItem::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(AIRecommendation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAIGenerated($query)
    {
        return $query->where('ai_generated', true);
    }

    // Methods
    public function markAsStarted()
    {
        $this->status = 'active';
        $this->save();
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function getProgressPercentage(): float
    {
        $items = $this->items;
        if ($items->isEmpty()) {
            return 0;
        }

        $completed = $items->where('completed', true)->count();
        return round(($completed / $items->count()) * 100, 2);
    }

    public function getCompletedItemsCount(): int
    {
        return $this->items()->where('completed', true)->count();
    }

    public function getTotalItemsCount(): int
    {
        return $this->items()->count();
    }

    public function getNextItem(): ?LearningPathItem
    {
        return $this->items()
            ->where('completed', false)
            ->orderBy('order')
            ->first();
    }
}
