<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationCenter extends Component
{
    public bool $showDropdown = false;
    public int $unreadCount = 0;
    public array $notifications = [];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $this->unreadCount = $user->unreadNotificationCount();
        $this->notifications = $user->getUnreadNotifications(15)
            ->map(fn ($notif) => [
                'id' => $notif->id,
                'type' => $notif->type,
                'title' => $notif->title,
                'message' => $notif->message,
                'action_url' => $notif->action_url,
                'actor_name' => $notif->actor?->name,
                'actor_avatar' => $notif->actor?->avatar,
                'created_at' => $notif->created_at->diffForHumans(),
                'created_at_timestamp' => $notif->created_at->timestamp,
            ])
            ->toArray();
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function closeDropdown(): void
    {
        $this->showDropdown = false;
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = Notification::find($notificationId);
        if ($notification && $notification->user_id === Auth::id()) {
            $notification->markAsRead();
            $this->loadNotifications();
            $this->dispatch('notification-read', notificationId: $notificationId);
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->loadNotifications();
        $this->dispatch('all-notifications-read');
    }

    public function deleteNotification(int $notificationId): void
    {
        $notification = Notification::find($notificationId);
        if ($notification && $notification->user_id === Auth::id()) {
            $notification->delete();
            $this->loadNotifications();
        }
    }

    public function getNotificationIcon(string $type): string
    {
        return match($type) {
            'comment_reply' => '💬',
            'post_liked' => '❤️',
            'post_commented' => '💬',
            'comment_liked' => '❤️',
            'user_followed' => '👤',
            'mention' => '@',
            'payout_completed' => '💰',
            'earnings_milestone' => '🎉',
            'premium_access' => '💎',
            default => '📢',
        };
    }

    public function render()
    {
        return view('livewire.notification-center');
    }
}
