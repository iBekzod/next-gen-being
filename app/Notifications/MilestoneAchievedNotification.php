<?php

namespace App\Notifications;

use App\Models\BloggerEarning;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MilestoneAchievedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public int $milestone,
        public float $amount,
        public BloggerEarning $earning
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Congratulations! You reached ' . number_format($this->milestone) . ' followers!')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('You just reached a major milestone: ' . number_format($this->milestone) . ' followers!')
            ->line('As a reward, you have earned $' . number_format($this->amount, 2) . ' which has been added to your pending earnings.')
            ->line('Keep creating amazing content and growing your audience!')
            ->action('View Your Earnings', route('filament.blogger.resources.earnings.index'))
            ->line('Thank you for being part of our blogging community!');
    }

    /**
     * Get the array representation of the notification (for database storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'milestone_achieved',
            'milestone' => $this->milestone,
            'amount' => $this->amount,
            'earning_id' => $this->earning->id,
            'message' => 'You reached ' . number_format($this->milestone) . ' followers and earned $' . number_format($this->amount, 2) . '!',
        ];
    }
}
