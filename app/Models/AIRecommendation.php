<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIRecommendation extends Model
{
    use HasFactory;

    protected $table = 'ai_recommendations';

    protected $fillable = [
        'user_id',
        'learning_path_id',
        'post_id',
        'recommendation_type',
        'title',
        'description',
        'reason',
        'confidence_score',
        'metadata',
        'dismissed_at',
        'acted_on_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'dismissed_at' => 'datetime',
        'acted_on_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('dismissed_at');
    }

    public function scopeDismissed($query)
    {
        return $query->whereNotNull('dismissed_at');
    }

    public function scopeActedOn($query)
    {
        return $query->whereNotNull('acted_on_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('recommendation_type', $type);
    }

    public function scopeHighConfidence($query, $threshold = 0.7)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    // Methods
    public function dismiss()
    {
        $this->dismissed_at = now();
        $this->save();
    }

    public function markAsActedOn()
    {
        $this->acted_on_at = now();
        $this->save();
    }

    public function getConfidencePercentage(): int
    {
        return round($this->confidence_score * 100);
    }

    public function isRecentlyGenerated(): bool
    {
        return $this->created_at->diffInMinutes(now()) < 60;
    }
}
