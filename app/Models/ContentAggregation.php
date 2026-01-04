<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAggregation extends Model
{
    use HasFactory;

    protected $table = 'content_aggregations';

    protected $fillable = [
        'topic',
        'description',
        'source_ids',
        'collected_content_ids',
        'primary_source_id',
        'confidence_score',
    ];

    protected $casts = [
        'source_ids' => 'json',
        'collected_content_ids' => 'json',
        'confidence_score' => 'float',
    ];

    // Relationships
    public function sources()
    {
        return $this->belongsToMany(
            ContentSource::class,
            'content_aggregation_sources',
            'aggregation_id',
            'content_source_id'
        );
    }

    public function collectedContent()
    {
        return $this->belongsToMany(
            CollectedContent::class,
            'content_aggregation_items',
            'content_aggregation_id',
            'collected_content_id'
        );
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'content_aggregation_id');
    }

    public function primarySource()
    {
        return $this->belongsTo(ContentSource::class, 'primary_source_id');
    }

    // Scopes
    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeHighConfidence($query)
    {
        return $query->where('confidence_score', '>=', 0.85);
    }

    public function scopeByTopic($query, $topic)
    {
        return $query->where('topic', 'like', "%{$topic}%");
    }

    public function scopeNotYetCurated($query)
    {
        return $query->whereDoesntHave('posts', function ($q) {
            $q->where('is_curated', true);
        });
    }

    // Methods
    public function getSourceCount(): int
    {
        return is_array($this->source_ids) ? count($this->source_ids) : 0;
    }

    public function getContentCount(): int
    {
        return is_array($this->collected_content_ids) ? count($this->collected_content_ids) : 0;
    }

    public function addSource(int $sourceId): void
    {
        $sourceIds = $this->source_ids ?? [];
        if (!in_array($sourceId, $sourceIds)) {
            $sourceIds[] = $sourceId;
            $this->update(['source_ids' => $sourceIds]);
        }
    }

    public function addContent(int $contentId): void
    {
        $contentIds = $this->collected_content_ids ?? [];
        if (!in_array($contentId, $contentIds)) {
            $contentIds[] = $contentId;
            $this->update(['collected_content_ids' => $contentIds]);
        }
    }

    public function getSourceNames(): array
    {
        if (!$this->source_ids) {
            return [];
        }

        return ContentSource::whereIn('id', $this->source_ids)
            ->pluck('name')
            ->toArray();
    }

    public function getConfidencePercentage(): int
    {
        return (int) ($this->confidence_score * 100);
    }

    public function getPublishedPost(): ?Post
    {
        return $this->posts()
            ->where('status', 'published')
            ->where('is_curated', true)
            ->first();
    }

    public function hasBeenCurated(): bool
    {
        return $this->posts()
            ->where('is_curated', true)
            ->exists();
    }
}
