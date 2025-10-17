<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NewsletterEngagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'subscription_id',
        'opened',
        'opened_at',
        'clicked',
        'clicked_at',
        'clicked_url',
    ];

    protected $casts = [
        'opened' => 'boolean',
        'opened_at' => 'datetime',
        'clicked' => 'boolean',
        'clicked_at' => 'datetime',
    ];

    // Relationships
    public function campaign()
    {
        return $this->belongsTo(NewsletterCampaign::class);
    }

    public function subscription()
    {
        return $this->belongsTo(NewsletterSubscription::class);
    }

    // Methods
    public function markAsOpened()
    {
        if (!$this->opened) {
            $this->update([
                'opened' => true,
                'opened_at' => now(),
            ]);

            $this->campaign->increment('opened_count');
        }
    }

    public function markAsClicked($url = null)
    {
        if (!$this->clicked) {
            $this->update([
                'clicked' => true,
                'clicked_at' => now(),
                'clicked_url' => $url,
            ]);

            $this->campaign->increment('clicked_count');
        }
    }
}
