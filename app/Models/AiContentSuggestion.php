<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiContentSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'source_url', 'topics', 'keywords',
        'relevance_score', 'status', 'suggested_by', 'reviewed_by', 'reviewed_at'
    ];

    protected $casts = [
        'topics' => 'array',
        'keywords' => 'array',
        'relevance_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function suggestedBy()
    {
        return $this->belongsTo(User::class, 'suggested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeHighRelevance($query, float $threshold = 0.7)
    {
        return $query->where('relevance_score', '>=', $threshold);
    }

    public function approve(User $reviewer): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()
        ]);
    }

    public function reject(User $reviewer): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now()
        ]);
    }

    public function markAsUsed(): void
    {
        $this->update(['status' => 'used']);
    }
}
