<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preferred_categories',
        'preferred_authors',
        'preferred_tags',
        'disliked_categories',
        'disliked_authors',
        'disliked_tags',
        'content_type_preferences',
        'reading_patterns',
        'engagement_data',
        'last_updated',
    ];

    protected $casts = [
        'preferred_categories' => 'array',
        'preferred_authors' => 'array',
        'preferred_tags' => 'array',
        'disliked_categories' => 'array',
        'disliked_authors' => 'array',
        'disliked_tags' => 'array',
        'content_type_preferences' => 'array',
        'reading_patterns' => 'array',
        'engagement_data' => 'array',
        'last_updated' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the reader
     */
    public function reader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper Methods
     */
    public function addPreferredCategory(int $categoryId, float $weight = 1.0): void
    {
        $categories = $this->preferred_categories ?? [];
        $categories[$categoryId] = ($categories[$categoryId] ?? 0) + $weight;
        $this->update(['preferred_categories' => $categories]);
    }

    public function addPreferredAuthor(int $authorId, float $weight = 1.0): void
    {
        $authors = $this->preferred_authors ?? [];
        $authors[$authorId] = ($authors[$authorId] ?? 0) + $weight;
        $this->update(['preferred_authors' => $authors]);
    }

    public function addPreferredTag(string $tag, float $weight = 1.0): void
    {
        $tags = $this->preferred_tags ?? [];
        $tags[$tag] = ($tags[$tag] ?? 0) + $weight;
        $this->update(['preferred_tags' => $tags]);
    }

    public function addDislikedCategory(int $categoryId): void
    {
        $categories = $this->disliked_categories ?? [];
        if (!in_array($categoryId, $categories)) {
            $categories[] = $categoryId;
            $this->update(['disliked_categories' => $categories]);
        }
    }

    public function addDislikedAuthor(int $authorId): void
    {
        $authors = $this->disliked_authors ?? [];
        if (!in_array($authorId, $authors)) {
            $authors[] = $authorId;
            $this->update(['disliked_authors' => $authors]);
        }
    }

    public function removeDislikedCategory(int $categoryId): void
    {
        $categories = $this->disliked_categories ?? [];
        $categories = array_filter($categories, fn($id) => $id !== $categoryId);
        $this->update(['disliked_categories' => array_values($categories)]);
    }

    public function removeDislikedAuthor(int $authorId): void
    {
        $authors = $this->disliked_authors ?? [];
        $authors = array_filter($authors, fn($id) => $id !== $authorId);
        $this->update(['disliked_authors' => array_values($authors)]);
    }

    /**
     * Get top preferred categories
     */
    public function getTopPreferredCategories($limit = 5): array
    {
        $categories = $this->preferred_categories ?? [];
        arsort($categories);
        return array_slice(array_keys($categories), 0, $limit);
    }

    /**
     * Get top preferred authors
     */
    public function getTopPreferredAuthors($limit = 5): array
    {
        $authors = $this->preferred_authors ?? [];
        arsort($authors);
        return array_slice(array_keys($authors), 0, $limit);
    }

    /**
     * Get top preferred tags
     */
    public function getTopPreferredTags($limit = 10): array
    {
        $tags = $this->preferred_tags ?? [];
        arsort($tags);
        return array_slice(array_keys($tags), 0, $limit);
    }

    /**
     * Get content type score (long-form, short-form, technical, storytelling, news, etc.)
     */
    public function getContentTypeScores(): array
    {
        return $this->content_type_preferences ?? [
            'long_form' => 0,
            'short_form' => 0,
            'technical' => 0,
            'storytelling' => 0,
            'news' => 0,
            'educational' => 0,
            'opinion' => 0,
            'tutorial' => 0,
        ];
    }

    /**
     * Get best reading times
     */
    public function getBestReadingTimes(): array
    {
        return $this->reading_patterns ?? [
            'preferred_days' => [],
            'preferred_hours' => [],
            'average_session_length' => 0,
            'reading_frequency' => 'daily', // daily, weekly, sporadic
        ];
    }

    /**
     * Get engagement summary
     */
    public function getEngagementSummary(): array
    {
        return $this->engagement_data ?? [
            'total_posts_read' => 0,
            'total_time_spent' => 0,
            'posts_saved' => 0,
            'comments_written' => 0,
            'posts_shared' => 0,
            'average_read_time' => 0,
        ];
    }
}
