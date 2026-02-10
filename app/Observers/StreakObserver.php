<?php

namespace App\Observers;

use App\Models\Streak;

class StreakObserver
{
    /**
     * Handle the Streak "created" event.
     */
    public function created(Streak $streak): void
    {
        \Log::info('Streak created', [
            'streak_id' => $streak->id,
            'user_id' => $streak->user_id,
            'type' => $streak->type,
            'count' => $streak->count,
        ]);
    }

    /**
     * Handle the Streak "updated" event.
     */
    public function updated(Streak $streak): void
    {
        // Check if streak reached a milestone
        if ($streak->isDirty('count')) {
            $milestones = [7, 30, 90, 180, 365];
            $newCount = $streak->count;

            if (in_array($newCount, $milestones)) {
                \Log::info('Streak milestone reached', [
                    'streak_id' => $streak->id,
                    'user_id' => $streak->user_id,
                    'type' => $streak->type,
                    'milestone' => $newCount,
                ]);

                // Send milestone notification (if class exists)
                try {
                    if (class_exists(\App\Notifications\StreakMilestoneNotification::class)) {
                        $streak->user?->notify(new \App\Notifications\StreakMilestoneNotification($streak, $newCount));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to send streak milestone notification', ['error' => $e->getMessage()]);
                }
            }
        }

        // Check if streak was broken
        if ($streak->isDirty('is_active') && !$streak->is_active) {
            \Log::info('Streak broken', [
                'streak_id' => $streak->id,
                'user_id' => $streak->user_id,
                'final_count' => $streak->count,
            ]);
        }
    }

    /**
     * Handle the Streak "deleted" event.
     */
    public function deleted(Streak $streak): void
    {
        \Log::info('Streak deleted', ['streak_id' => $streak->id]);
    }

    /**
     * Handle the Streak "restored" event.
     */
    public function restored(Streak $streak): void
    {
        \Log::info('Streak restored', ['streak_id' => $streak->id]);
    }

    /**
     * Handle the Streak "force deleted" event.
     */
    public function forceDeleted(Streak $streak): void
    {
        \Log::info('Streak force deleted', ['streak_id' => $streak->id]);
    }
}
