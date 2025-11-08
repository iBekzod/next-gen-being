<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): RedirectResponse
    {
        $this->authorize('view', $notification);

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(Notification $notification): RedirectResponse
    {
        $this->authorize('view', $notification);

        $notification->markAsUnread();

        return back()->with('success', 'Notification marked as unread.');
    }

    /**
     * Delete notification.
     */
    public function delete(Notification $notification): RedirectResponse
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return back()->with('success', 'Notification deleted.');
    }
}
