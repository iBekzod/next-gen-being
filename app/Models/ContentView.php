<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentView extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'session_id',
        'ip_address',
        'user_agent',
        'is_premium_content',
        'viewed_as_trial',
        'converted_to_paid',
        'time_on_page',
        'scroll_depth',
        'clicked_upgrade',
        'referrer',
        'viewed_at',
    ];

    protected $casts = [
        'is_premium_content' => 'boolean',
        'viewed_as_trial' => 'boolean',
        'converted_to_paid' => 'boolean',
        'clicked_upgrade' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium_content', true);
    }

    public function scopeConverted($query)
    {
        return $query->where('converted_to_paid', true);
    }
}
