<?php

namespace App\Observers;

use App\Models\Challenge;

class ChallengeObserver
{
    /**
     * Handle the Challenge "created" event.
     */
    public function created(Challenge $challenge): void
    {
        \Log::info('Challenge created', [
            'challenge_id' => $challenge->id,
            'title' => $challenge->title,
            'created_by' => $challenge->created_by,
        ]);
    }

    /**
     * Handle the Challenge "updated" event.
     */
    public function updated(Challenge $challenge): void
    {
        // Log status changes
        if ($challenge->isDirty('status')) {
            \Log::info('Challenge status updated', [
                'challenge_id' => $challenge->id,
                'old_status' => $challenge->getOriginal('status'),
                'new_status' => $challenge->status,
            ]);

            // If challenge was just completed, notify participants
            if ($challenge->status === 'completed' && $challenge->getOriginal('status') !== 'completed') {
                \Log::info('Challenge completed', [
                    'challenge_id' => $challenge->id,
                    'participants_count' => $challenge->participants()->count(),
                ]);
            }
        }

        // Track participant count changes
        if ($challenge->isDirty('participants_count')) {
            \Log::info('Challenge participants updated', [
                'challenge_id' => $challenge->id,
                'participants' => $challenge->participants_count,
            ]);
        }
    }

    /**
     * Handle the Challenge "deleted" event.
     */
    public function deleted(Challenge $challenge): void
    {
        \Log::info('Challenge deleted', ['challenge_id' => $challenge->id]);
    }

    /**
     * Handle the Challenge "restored" event.
     */
    public function restored(Challenge $challenge): void
    {
        \Log::info('Challenge restored', ['challenge_id' => $challenge->id]);
    }

    /**
     * Handle the Challenge "force deleted" event.
     */
    public function forceDeleted(Challenge $challenge): void
    {
        \Log::info('Challenge force deleted', ['challenge_id' => $challenge->id]);
    }
}
