<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'payout_method',
        'notes',
        'status',
        'admin_notes',
        'transaction_reference',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user who requested the payout
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who processed the payout
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Mark the payout as completed
     */
    public function markAsCompleted(User $processedBy, string $transactionReference): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'processed_by' => $processedBy->id,
            'transaction_reference' => $transactionReference,
        ]);

        // Mark related earnings as paid
        $this->user->earnings()
            ->where('status', 'pending')
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payout_method' => $this->payout_method,
                'payout_reference' => $transactionReference,
            ]);
    }

    /**
     * Mark the payout as rejected
     */
    public function markAsRejected(User $processedBy, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => $processedBy->id,
            'admin_notes' => $reason,
        ]);
    }

    /**
     * Check if the payout request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the payout request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
