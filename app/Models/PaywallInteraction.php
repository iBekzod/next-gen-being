<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaywallInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'session_id',
        'interaction_type',
        'paywall_type',
        'converted',
        'metadata',
        'interacted_at',
    ];

    protected $casts = [
        'converted' => 'boolean',
        'metadata' => 'array',
        'interacted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('interaction_type', $type);
    }
}
