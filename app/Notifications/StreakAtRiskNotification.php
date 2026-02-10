<?php

namespace App\Notifications;

use App\Models\Streak;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StreakAtRiskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $streak;

    public function __construct(Streak $streak)
    {
        $this->streak = $streak;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Your ' . $this->streak->count . ' Day Streak is at Risk!')
            ->line('Don\'t lose your momentum!')
            ->line('Your ' . $this->streak->type . ' streak of ' . $this->streak->count . ' days will expire soon if you don\'t take action.')
            ->action('Continue Your Streak', url('/dashboard'))
            ->line('Keep going - you\'ve got this!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'streak_id' => $this->streak->id,
            'streak_type' => $this->streak->type,
            'count' => $this->streak->count,
            'message' => 'Your ' . $this->streak->count . ' day streak is at risk!',
        ];
    }
}
