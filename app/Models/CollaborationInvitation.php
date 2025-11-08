<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CollaborationInvitation extends Model
{
    protected $table = 'collaboration_invitations';

    protected $fillable = [
        'post_id',
        'inviter_id',
        'email',
        'user_id',
        'role',
        'status',
        'token',
        'expires_at',
        'accepted_at',
        'declined_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    // Methods
    public static function generateToken(): string
    {
        return Str::random(60);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function accept(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Add user as collaborator
        PostCollaborator::updateOrCreate(
            [
                'post_id' => $this->post_id,
                'user_id' => $this->user_id,
            ],
            [
                'role' => $this->role,
                'joined_at' => now(),
            ]
        );

        // Mark invitation as accepted
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return true;
    }

    public function decline(): bool
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);

        return true;
    }

    public function cancel(): bool
    {
        $this->update(['status' => 'cancelled']);
        return true;
    }

    public function getAcceptanceUrl(): string
    {
        return route('collaboration.invitation.accept', ['token' => $this->token]);
    }
}
