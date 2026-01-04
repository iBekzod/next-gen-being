<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TutorialCollection extends Model implements \Spatie\MediaLibrary\HasMedia
{
    use HasFactory, HasSlug, \Spatie\MediaLibrary\InteractsWithMedia;

    protected $table = 'tutorial_collections';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'topic',
        'source_ids',
        'collected_content_ids',
        'references',
        'steps',
        'code_examples',
        'best_practices',
        'common_pitfalls',
        'skill_level',
        'language',
        'estimated_hours',
        'reading_time_minutes',
        'compiled_content',
        'featured_image',
        'status',
        'published_at',
        'reviewed_by',
        'review_notes',
        'view_count',
        'share_count',
        'completion_count',
    ];

    protected $casts = [
        'source_ids' => 'json',
        'collected_content_ids' => 'json',
        'references' => 'json',
        'steps' => 'json',
        'code_examples' => 'json',
        'best_practices' => 'json',
        'common_pitfalls' => 'json',
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'share_count' => 'integer',
        'completion_count' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    // Relationships
    public function sources()
    {
        return $this->belongsToMany(
            ContentSource::class,
            'tutorial_collection_sources',
            'tutorial_collection_id',
            'content_source_id'
        );
    }

    public function collectedContent()
    {
        return $this->belongsToMany(
            CollectedContent::class,
            'tutorial_collection_items',
            'tutorial_collection_id',
            'collected_content_id'
        );
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'review');
    }

    public function scopeBySkillLevel($query, $level)
    {
        return $query->where('skill_level', $level);
    }

    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeByTopic($query, $topic)
    {
        return $query->where('topic', $topic);
    }

    public function scopePopular($query)
    {
        return $query->orderByDesc('view_count')
            ->orderByDesc('share_count');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('published_at');
    }

    // Methods
    public function publish(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_notes' => $notes,
        ]);
    }

    public function reject(User $reviewer, string $reason): void
    {
        $this->update([
            'status' => 'draft',
            'reviewed_by' => $reviewer->id,
            'review_notes' => $reason,
        ]);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               $this->published_at &&
               $this->published_at->isPast();
    }

    public function getStepCount(): int
    {
        return is_array($this->steps) ? count($this->steps) : 0;
    }

    public function getSourceCount(): int
    {
        return is_array($this->source_ids) ? count($this->source_ids) : 0;
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

    public function getReadingTimeDisplay(): string
    {
        if (!$this->reading_time_minutes) {
            return 'Unknown duration';
        }

        if ($this->reading_time_minutes < 60) {
            return "{$this->reading_time_minutes} min read";
        }

        $hours = floor($this->reading_time_minutes / 60);
        $minutes = $this->reading_time_minutes % 60;

        if ($minutes === 0) {
            return "{$hours}h read";
        }

        return "{$hours}h {$minutes}m read";
    }

    public function getEstimatedTimeDisplay(): string
    {
        if (!$this->estimated_hours) {
            return 'Self-paced';
        }

        return "~{$this->estimated_hours} hour" . ($this->estimated_hours > 1 ? 's' : '');
    }

    public function recordView(): void
    {
        $this->increment('view_count');
    }

    public function recordShare(): void
    {
        $this->increment('share_count');
    }

    public function recordCompletion(): void
    {
        $this->increment('completion_count');
    }

    public function getCompletionRate(): float
    {
        // Rough estimate: completions / views
        if ($this->view_count === 0) {
            return 0;
        }

        return round(($this->completion_count / $this->view_count) * 100, 2);
    }

    public function getNextStep(int $currentStepNum = 0): ?array
    {
        if (!$this->steps || !is_array($this->steps)) {
            return null;
        }

        return $this->steps[$currentStepNum] ?? null;
    }
}
