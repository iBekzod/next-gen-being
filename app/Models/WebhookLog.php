<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_id',
        'event_type',
        'response_status',
        'request_payload',
        'response_body',
        'success',
        'error_message',
        'response_time_ms',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'success' => 'boolean',
        'response_time_ms' => 'integer',
    ];

    /**
     * Get the webhook that owns this log
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    /**
     * Get status indicator
     */
    public function getStatusIndicator(): string
    {
        if ($this->success) {
            return '✓ Success';
        } elseif ($this->response_status) {
            return "✗ Error {$this->response_status}";
        } else {
            return '✗ Connection Error';
        }
    }
}
