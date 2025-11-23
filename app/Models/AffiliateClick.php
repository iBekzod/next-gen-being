<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_link_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'converted',
    ];

    protected $casts = [
        'converted' => 'boolean',
    ];

    // Relationships
    public function link(): BelongsTo
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->nullable();
    }

    // Scopes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }
}
