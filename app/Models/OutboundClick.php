<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundClick extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'slug', 'post_id', 'is_affiliate', 'referrer', 'ip_hash', 'user_agent', 'lang',
    ];

    protected $casts = [
        'is_affiliate' => 'boolean',
    ];
}
