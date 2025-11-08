<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'email_comment_reply',
        'email_post_liked',
        'email_post_commented',
        'email_user_followed',
        'email_mention',
        'app_comment_reply',
        'app_post_liked',
        'app_post_commented',
        'app_user_followed',
        'app_mention',
        'digest_frequency',
        'digest_enabled',
    ];

    protected $casts = [
        'email_comment_reply' => 'boolean',
        'email_post_liked' => 'boolean',
        'email_post_commented' => 'boolean',
        'email_user_followed' => 'boolean',
        'email_mention' => 'boolean',
        'app_comment_reply' => 'boolean',
        'app_post_liked' => 'boolean',
        'app_post_commented' => 'boolean',
        'app_user_followed' => 'boolean',
        'app_mention' => 'boolean',
        'digest_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if email notification should be sent for type
     */
    public function shouldSendEmailFor(string $type): bool
    {
        $key = 'email_' . str($type)->snake();
        return $this->{$key} ?? false;
    }

    /**
     * Check if in-app notification should be created for type
     */
    public function shouldCreateAppNotificationFor(string $type): bool
    {
        $key = 'app_' . str($type)->snake();
        return $this->{$key} ?? false;
    }
}
