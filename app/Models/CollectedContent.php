<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectedContent extends Model
{
    use HasFactory;

    protected $table = 'collected_content';

    protected $fillable = [
        'content_source_id',
        'external_url',
        'title',
        'excerpt',
        'full_content',
        'author',
        'published_at',
        'language',
        'content_type',
        'image_url',
        'is_processed',
        'is_duplicate',
        'duplicate_of',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_processed' => 'boolean',
        'is_duplicate' => 'boolean',
    ];

    // Relationships
    public function source()
    {
        return $this->belongsTo(ContentSource::class, 'content_source_id');
    }

    public function aggregation()
    {
        return $this->belongsToMany(
            ContentAggregation::class,
            'content_aggregation_items',
            'collected_content_id',
            'aggregation_id'
        );
    }

    public function duplicateOf()
    {
        return $this->belongsTo(CollectedContent::class, 'duplicate_of');
    }

    public function duplicates()
    {
        return $this->hasMany(CollectedContent::class, 'duplicate_of');
    }

    public function sourceReference()
    {
        return $this->hasMany(SourceReference::class, 'collected_content_id');
    }

    // Scopes
    public function scopeUnprocessed($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeNotDuplicate($query)
    {
        return $query->where('is_duplicate', false);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at');
    }

    public function scopeBySource($query, $sourceId)
    {
        return $query->where('content_source_id', $sourceId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('content_type', $type);
    }

    // Methods
    public function markAsProcessed(): void
    {
        $this->update(['is_processed' => true]);
    }

    public function markAsDuplicate($originalId): void
    {
        $this->update([
            'is_duplicate' => true,
            'duplicate_of' => $originalId,
        ]);
    }

    public function getDomain(): string
    {
        $parsed = parse_url($this->external_url);
        return $parsed['host'] ?? 'unknown';
    }

    public function getExcerpt(int $length = 150): string
    {
        if (strlen($this->excerpt) <= $length) {
            return $this->excerpt;
        }

        return substr($this->excerpt, 0, $length) . '...';
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->full_content ?? ''));
    }

    public function isArticle(): bool
    {
        return $this->content_type === 'article';
    }

    public function isTutorial(): bool
    {
        return $this->content_type === 'tutorial';
    }
}
