<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user_1_id',
        'user_2_id',
        'subject',
        'last_message_at',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_1_id');
    }

    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_2_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function otherUser(User $user): User
    {
        return $user->id === $this->user_1_id ? $this->user2 : $this->user1;
    }

    public function unreadCount(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
