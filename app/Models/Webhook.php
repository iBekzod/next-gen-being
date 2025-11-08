<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'event_type',
        'events',
        'headers',
        'status',
        'retry_count',
        'max_retries',
        'last_triggered_at',
        'last_failed_at',
        'last_error',
        'verify_ssl',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'last_triggered_at' => 'datetime',
        'last_failed_at' => 'datetime',
        'verify_ssl' => 'boolean',
    ];

    /**
     * Get the user that owns this webhook
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get logs for this webhook
     */
    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Get recent successful logs
     */
    public function recentSuccessfulLogs(int $limit = 10)
    {
        return $this->logs()
            ->where('success', true)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent failed logs
     */
    public function recentFailedLogs(int $limit = 10)
    {
        return $this->logs()
            ->where('success', false)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Check if webhook should trigger for event
     */
    public function shouldTrigger(string $eventType): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $events = $this->events ?? [];

        return empty($events) || in_array($eventType, $events);
    }

    /**
     * Mark webhook as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->increment('retry_count');
        $this->update([
            'last_failed_at' => now(),
            'last_error' => $error,
            'status' => $this->retry_count >= $this->max_retries ? 'failed' : 'active',
        ]);
    }

    /**
     * Mark webhook as successful
     */
    public function markAsSuccess(): void
    {
        $this->update([
            'last_triggered_at' => now(),
            'retry_count' => 0,
            'last_error' => null,
            'status' => 'active',
        ]);
    }

    /**
     * Reset retry count
     */
    public function resetRetries(): void
    {
        $this->update([
            'retry_count' => 0,
            'status' => 'active',
            'last_error' => null,
        ]);
    }

    /**
     * Get statistics for webhook
     */
    public function getStatistics(): array
    {
        $allLogs = $this->logs();
        $totalCalls = $allLogs->count();
        $successfulCalls = $allLogs->where('success', true)->count();
        $failedCalls = $totalCalls - $successfulCalls;

        return [
            'total_calls' => $totalCalls,
            'successful_calls' => $successfulCalls,
            'failed_calls' => $failedCalls,
            'success_rate' => $totalCalls > 0 ? round(($successfulCalls / $totalCalls) * 100, 2) : 0,
            'avg_response_time' => $allLogs->whereNotNull('response_time_ms')->avg('response_time_ms'),
            'last_triggered_at' => $this->last_triggered_at,
            'last_failed_at' => $this->last_failed_at,
        ];
    }
}
