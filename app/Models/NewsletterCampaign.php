<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsletterCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'content',
        'type',
        'status',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'opened_count',
        'clicked_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'recipients_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
    ];

    // Relationships
    public function engagements()
    {
        return $this->hasMany(NewsletterEngagement::class, 'campaign_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    // Methods
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function incrementRecipients($count = 1)
    {
        $this->increment('recipients_count', $count);
    }

    public function getOpenRateAttribute()
    {
        if ($this->recipients_count === 0) {
            return 0;
        }

        return round(($this->opened_count / $this->recipients_count) * 100, 2);
    }

    public function getClickRateAttribute()
    {
        if ($this->recipients_count === 0) {
            return 0;
        }

        return round(($this->clicked_count / $this->recipients_count) * 100, 2);
    }
}
