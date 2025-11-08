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

        // TODO: Send email notification if user preferences allow
        // TODO: Broadcast notification for real-time updates

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
}
