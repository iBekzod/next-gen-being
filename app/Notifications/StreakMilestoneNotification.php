<?php

namespace App\Notifications;

use App\Models\Streak;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StreakMilestoneNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $streak;
    public $milestone;

    /**
     * Create a new notification instance.
     */
    public function __construct(Streak $streak, int $milestone)
    {
        $this->streak = $streak;
        $this->milestone = $milestone;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Milestone Achievement: ' . $this->milestone . ' Day Streak!')
            ->line('Congratulations! You\'ve reached an amazing milestone!')
            ->line('Your ' . $this->streak->type . ' streak is now ' . $this->milestone . ' days strong!')
            ->action('View Your Progress', url('/dashboard/streaks'))
            ->line('Keep up the great work!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'streak_id' => $this->streak->id,
            'streak_type' => $this->streak->type,
            'milestone' => $this->milestone,
            'message' => 'You\'ve reached a ' . $this->milestone . ' day ' . $this->streak->type . ' streak!',
        ];
    }
}
