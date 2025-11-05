<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $follower
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Send database notification, but email only if user hasn't received
        // too many follower notifications recently (to avoid spam)
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $followerCount = $notifiable->followers()->count();

        return (new MailMessage)
            ->subject($this->follower->name . ' started following you!')
            ->greeting('New Follower!')
            ->line($this->follower->name . ' (@' . $this->follower->username . ') is now following you!')
            ->line('You now have ' . number_format($followerCount) . ' ' . ($followerCount === 1 ? 'follower' : 'followers') . '.')
            ->action('View Profile', route('bloggers.profile', $this->follower->username))
            ->line('Keep up the great content!');
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_follower',
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->name,
            'follower_username' => $this->follower->username,
            'follower_avatar' => $this->follower->avatar,
            'message' => $this->follower->name . ' started following you!',
        ];
    }
}
