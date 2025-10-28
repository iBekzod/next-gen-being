<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPlan extends Model
{
    protected $fillable = [
        'month',
        'theme',
        'description',
        'planned_topics',
        'generated_topics',
        'status',
    ];

    protected $casts = [
        'planned_topics' => 'array',
        'generated_topics' => 'array',
    ];

    /**
     * Get the active plan for current month
     */
    public static function getCurrentPlan()
    {
        $currentMonth = now()->format('Y-m');
        return self::where('month', $currentMonth)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Mark a topic as generated
     */
    public function markTopicGenerated(string $topic, string $postId)
    {
        $generated = $this->generated_topics ?? [];
        $generated[] = [
            'topic' => $topic,
            'post_id' => $postId,
            'generated_at' => now()->toIso8601String(),
        ];

        $this->update(['generated_topics' => $generated]);
    }

    /**
     * Check if all topics are generated
     */
    public function isComplete(): bool
    {
        $plannedCount = count($this->planned_topics);
        $generatedCount = count($this->generated_topics ?? []);

        return $generatedCount >= $plannedCount;
    }
}
