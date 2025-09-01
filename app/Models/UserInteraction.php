<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'interactable_id', 'interactable_type',
        'type', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interactable()
    {
        return $this->morphTo();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLikes($query)
    {
        return $query->where('type', 'like');
    }

    public function scopeBookmarks($query)
    {
        return $query->where('type', 'bookmark');
    }

    public function scopeViews($query)
    {
        return $query->where('type', 'view');
    }
}
