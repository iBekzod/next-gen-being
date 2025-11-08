<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderAnalytics extends Model
{
    protected $table = 'reader_analytics';

    protected $fillable = [
        'post_id',
        'total_readers_today',
        'authenticated_readers_today',
        'anonymous_readers_today',
        'peak_concurrent_readers',
        'peak_time',
        'top_countries',
        'hourly_breakdown',
        'date',
    ];

    protected $casts = [
        'top_countries' => 'array',
        'hourly_breakdown' => 'array',
        'date' => 'date',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // Scopes
    public function scopeForPost($query, int $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeLastDays($query, int $days)
    {
        return $query->where('date', '>=', today()->subDays($days));
    }

    // Methods
    public function getAnonymousReaderCount(): int
    {
        return $this->anonymous_readers_today;
    }

    public function getAuthenticatedReaderCount(): int
    {
        return $this->authenticated_readers_today;
    }

    public function getTotalReaderCount(): int
    {
        return $this->total_readers_today;
    }

    public function getTopCountries(): array
    {
        return $this->top_countries ?? [];
    }

    public function getHourlyBreakdown(): array
    {
        return $this->hourly_breakdown ?? [];
    }

    public function getPeakConcurrentReaders(): int
    {
        return $this->peak_concurrent_readers;
    }

    public function getPeakTime(): ?string
    {
        return $this->peak_time;
    }
}
