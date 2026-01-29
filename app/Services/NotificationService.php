<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Create a notification
     */
    public function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?User $actor = null,
        ?Model $notifiable = null,
        ?array $data = null
    ): ?Notification {
        // Check user preferences
        $prefs = $user->getNotificationPreferences();
        if (!$prefs->shouldCreateAppNotificationFor($type)) {
            return null;
        }

        $notification = Notification::create([
            'user_id' => $user->id,
            'actor_id' => $actor?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'notifiable_type' => $notifiable?->getMorphClass(),
            'notifiable_id' => $notifiable?->id,
            'data' => $data,
        ]);

        // Send email notification if user preferences allow
        if ($prefs->shouldSendEmailFor($type)) {
            $this->sendEmailNotification($user, $notification, $actionUrl);
        }

        // Broadcast notification for real-time updates
        $this->broadcastNotification($user, $notification);

        return $notification;
    }

    /**
     * Notify post author when someone comments
     */
    public function notifyPostCommented(Post $post, Comment $comment, User $commenter): void
    {
        if ($post->author_id === $commenter->id) {
            return; // Don't notify yourself
        }

        $this->create(
            user: $post->author,
            type: 'post_commented',
            title: 'New comment on your post',
            message: "{$commenter->name} commented on \"{$post->title}\"",
            actionUrl: route('posts.show', $post->slug) . "#comment-{$comment->id}",
            actor: $commenter,
            notifiable: $post,
            data: [
                'post_title' => $post->title,
                'post_slug' => $post->slug,
                'comment_excerpt' => substr($comment->content, 0, 100),
            ]
        );
    }

    /**
     * Notify comment author when someone replies
     */
    public function notifyCommentReply(Comment $parentComment, Comment $reply, User $replier): void
    {
        if ($parentComment->author_id === $replier->id) {
            return; // Don't notify yourself
        }

        $this->create(
            user: $parentComment->author,
            type: 'comment_reply',
            title: 'New reply to your comment',
            message: "{$replier->name} replied to your comment",
            actionUrl: route('posts.show', $parentComment->post->slug) . "#comment-{$reply->id}",
            actor: $replier,
            notifiable: $reply,
            data: [
                'post_title' => $parentComment->post->title,
                'post_slug' => $parentComment->post->slug,
                'parent_comment_excerpt' => substr($parentComment->content, 0, 100),
                'reply_excerpt' => substr($reply->content, 0, 100),
            ]
        );
    }

    /**
     * Notify post author when someone likes their post
     */
    public function notifyPostLiked(Post $post, User $liker): void
    {
        if ($post->author_id === $liker->id) {
            return; // Don't notify yourself
        }

        // Check if we already notified in the last 30 minutes (avoid spam)
        $recentNotification = Notification::where('user_id', $post->author_id)
            ->where('type', 'post_liked')
            ->where('actor_id', $liker->id)
            ->where('notifiable_type', Post::class)
            ->where('notifiable_id', $post->id)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();

        if ($recentNotification) {
            return;
        }

        $this->create(
            user: $post->author,
            type: 'post_liked',
            title: 'Post liked',
            message: "{$liker->name} liked your post \"{$post->title}\"",
            actionUrl: route('posts.show', $post->slug),
            actor: $liker,
            notifiable: $post,
            data: ['post_title' => $post->title]
        );
    }

    /**
     * Notify user when they're followed
     */
    public function notifyUserFollowed(User $follower, User $followee): void
    {
        $this->create(
            user: $followee,
            type: 'user_followed',
            title: 'New follower',
            message: "{$follower->name} started following you",
            actionUrl: route('bloggers.profile', $follower->username ?? $follower->id),
            actor: $follower,
            notifiable: $follower,
            data: ['follower_name' => $follower->name]
        );
    }

    /**
     * Notify user when mentioned in a comment
     */
    public function notifyMention(Comment $comment, User $mentionedUser, User $commenter): void
    {
        if ($mentionedUser->id === $commenter->id) {
            return; // Don't notify yourself
        }

        $this->create(
            user: $mentionedUser,
            type: 'mention',
            title: 'You were mentioned',
            message: "{$commenter->name} mentioned you in a comment on \"{$comment->post->title}\"",
            actionUrl: route('posts.show', $comment->post->slug) . "#comment-{$comment->id}",
            actor: $commenter,
            notifiable: $comment,
            data: [
                'post_title' => $comment->post->title,
                'post_slug' => $comment->post->slug,
                'comment_excerpt' => substr($comment->content, 0, 100),
            ]
        );
    }

    /**
     * Notify user of premium access granted
     */
    public function notifyPremiumAccess(User $user, string $reason = 'subscription'): void
    {
        $this->create(
            user: $user,
            type: 'premium_access',
            title: 'Premium access granted',
            message: 'You now have access to all premium content',
            actionUrl: route('subscription.manage'),
            notifiable: $user,
            data: ['reason' => $reason]
        );
    }

    /**
     * Notify user of earnings milestone
     */
    public function notifyEarningsMilestone(User $user, float $amount): void
    {
        $this->create(
            user: $user,
            type: 'earnings_milestone',
            title: 'Earnings milestone reached!',
            message: "Congratulations! You've earned ${$amount}",
            actionUrl: route('dashboard.earnings'),
            notifiable: $user,
            data: ['amount' => $amount]
        );
    }

    /**
     * Notify user of payout completion
     */
    public function notifyPayoutCompleted(User $user, float $amount, string $method): void
    {
        $this->create(
            user: $user,
            type: 'payout_completed',
            title: 'Payout completed',
            message: "Your payout of ${$amount} via {$method} has been completed",
            actionUrl: route('dashboard.payouts'),
            notifiable: $user,
            data: ['amount' => $amount, 'method' => $method]
        );
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }

    // Email Notification Methods

    /**
     * Send email notification for tip received
     */
    public static function notifyTipReceived(User $recipient, User $tipper, float $amount, ?Post $post = null): void
    {
        try {
            $prefs = $recipient->getNotificationPreferences();
            if (!$prefs->shouldSendEmailFor('tip_received')) {
                return;
            }

            \Mail::send('emails.tip-received', [
                'recipient' => $recipient,
                'tipper' => $tipper,
                'amount' => $amount,
                'post' => $post,
            ], function ($message) use ($recipient, $amount) {
                $message->to($recipient->email)
                    ->subject("You received a \${$amount} tip!");
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send tip notification email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send email notification for streak milestone
     */
    public static function notifyStreakMilestone(User $user, string $type, int $count): void
    {
        try {
            $prefs = $user->getNotificationPreferences();
            if (!$prefs->shouldSendEmailFor('streak_milestone')) {
                return;
            }

            \Mail::send('emails.streak-milestone', [
                'user' => $user,
                'type' => $type,
                'count' => $count,
            ], function ($message) use ($user, $type, $count) {
                $message->to($user->email)
                    ->subject("🔥 Congratulations! {$count}-day {$type} streak!");
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send streak milestone email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send email notification for streak at risk
     */
    public static function notifyStreakAtRisk(User $user, string $type, int $currentCount): void
    {
        try {
            $prefs = $user->getNotificationPreferences();
            if (!$prefs->shouldSendEmailFor('streak_warning')) {
                return;
            }

            \Mail::send('emails.streak-at-risk', [
                'user' => $user,
                'type' => $type,
                'currentCount' => $currentCount,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('⚠️ Your streak is at risk! Come back now');
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send streak warning email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send email notification for challenge completed
     */
    public static function notifyChallengeCompleted(User $user, $challenge): void
    {
        try {
            $prefs = $user->getNotificationPreferences();
            if (!$prefs->shouldSendEmailFor('challenge_completed')) {
                return;
            }

            \Mail::send('emails.challenge-completed', [
                'user' => $user,
                'challenge' => $challenge,
            ], function ($message) use ($user, $challenge) {
                $message->to($user->email)
                    ->subject("🏆 Challenge completed: {$challenge->name}");
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send challenge completed email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send weekly digest email
     */
    public static function sendWeeklyDigest(User $user): void
    {
        try {
            $prefs = $user->getNotificationPreferences();
            if (!$prefs->shouldSendEmailFor('weekly_digest')) {
                return;
            }

            // Get top posts from the week
            $topPosts = Post::published()
                ->where('published_at', '>=', now()->subDays(7))
                ->orderBy('views_count', 'desc')
                ->limit(5)
                ->get();

            // Get stats
            $stats = [
                'posts_read' => $user->interactions()
                    ->where('type', 'view')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'posts_liked' => $user->interactions()
                    ->where('type', 'like')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'followers_gained' => $user->followers()
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ];

            \Mail::send('emails.weekly-digest', [
                'user' => $user,
                'topPosts' => $topPosts,
                'stats' => $stats,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('📬 Your weekly digest');
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send weekly digest', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send email notification for post mentioned
     */
    public static function notifyPostMentioned(User $user, Post $post, int $mentionCount): void
    {
        try {
            $prefs = $user->getNotificationPreferences();
            if (!$prefs->shouldSendEmailFor('post_mentioned')) {
                return;
            }

            \Mail::send('emails.post-trending', [
                'user' => $user,
                'post' => $post,
                'mentionCount' => $mentionCount,
            ], function ($message) use ($user, $post) {
                $message->to($user->email)
                    ->subject("🚀 Your post is trending: {$post->title}");
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send post trending email', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send email notification to user
     */
    private function sendEmailNotification(User $user, Notification $notification, ?string $actionUrl): void
    {
        try {
            \Mail::send('emails.notification', [
                'user' => $user,
                'notification' => $notification,
                'actionUrl' => $actionUrl,
            ], function ($message) use ($user, $notification) {
                $message->to($user->email)
                    ->subject($notification->title);
            });
        } catch (\Exception $e) {
            \Log::warning('Failed to send notification email', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Broadcast notification for real-time updates via WebSocket
     */
    private function broadcastNotification(User $user, Notification $notification): void
    {
        try {
            // Broadcast event for real-time notification delivery
            // Using Laravel Broadcasting with event channels
            \Illuminate\Support\Facades\Event::dispatch(
                new \App\Events\NotificationCreated($user, $notification)
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast notification', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
