<?php

namespace App\Observers;

use App\Models\Tip;

class TipObserver
{
    /**
     * Handle the Tip "created" event.
     */
    public function created(Tip $tip): void
    {
        // Update recipient's total tips received count
        if ($tip->recipient) {
            $tip->recipient->increment('tips_received_count');
            $tip->recipient->increment('total_tips_amount', $tip->amount);
        }

        // Update sender's tips given count
        if ($tip->tipper) {
            $tip->tipper->increment('tips_given_count');
        }

        // Log activity for analytics
        \Log::info('Tip created', [
            'tip_id' => $tip->id,
            'amount' => $tip->amount,
            'tipper_id' => $tip->tipper_id,
            'recipient_id' => $tip->recipient_id,
        ]);

        // Send notification to recipient (if notification class exists)
        try {
            if (class_exists(\App\Notifications\TipReceivedNotification::class)) {
                $tip->recipient?->notify(new \App\Notifications\TipReceivedNotification($tip));
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send tip notification', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the Tip "updated" event.
     */
    public function updated(Tip $tip): void
    {
        // Log status changes
        if ($tip->isDirty('status')) {
            \Log::info('Tip status updated', [
                'tip_id' => $tip->id,
                'old_status' => $tip->getOriginal('status'),
                'new_status' => $tip->status,
            ]);
        }
    }

    /**
     * Handle the Tip "deleted" event.
     */
    public function deleted(Tip $tip): void
    {
        \Log::info('Tip deleted', ['tip_id' => $tip->id]);
    }

    /**
     * Handle the Tip "restored" event.
     */
    public function restored(Tip $tip): void
    {
        \Log::info('Tip restored', ['tip_id' => $tip->id]);
    }

    /**
     * Handle the Tip "force deleted" event.
     */
    public function forceDeleted(Tip $tip): void
    {
        \Log::info('Tip force deleted', ['tip_id' => $tip->id]);
    }
}
