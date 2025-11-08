@extends('layouts.app')

@section('title', 'Notifications - Dashboard')
@section('description', 'Manage your notifications, mentions, and alerts')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-purple-500/20 text-purple-200">🔔 Notifications</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Notification Center</h1>
        <p class="mt-4 text-base text-slate-300">Stay updated with all your activity, mentions, and alerts in one place.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        <!-- Notification Statistics -->
        <div class="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-3 lg:grid-cols-4">
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Notifications</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalNotifications }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unread</p>
                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $unreadCount }}</p>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Mentions</p>
                <p class="mt-2 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $mentionCount }}</p>
            </div>

            @if($notificationsByType->count() > 0)
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Types</p>
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($notificationsByType->take(3) as $type => $count)
                    <span class="inline-block px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 rounded">
                        {{ ucfirst($type) }}: {{ $count }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Filter Tabs -->
        <div class="mb-8 flex gap-4 border-b border-gray-200 dark:border-slate-700">
            <a href="{{ route('dashboard.notifications', ['filter' => 'all']) }}"
                class="px-4 py-3 font-medium text-sm transition {{ $filter === 'all' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                All Notifications
            </a>
            <a href="{{ route('dashboard.notifications', ['filter' => 'unread']) }}"
                class="px-4 py-3 font-medium text-sm transition {{ $filter === 'unread' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                Unread
            </a>
            <a href="{{ route('dashboard.notifications', ['filter' => 'mentions']) }}"
                class="px-4 py-3 font-medium text-sm transition {{ $filter === 'mentions' ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}">
                Mentions
            </a>
        </div>

        <!-- Notifications List -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            @if($notifications->isNotEmpty())
            <div class="divide-y divide-gray-200 dark:divide-slate-700">
                @foreach($notifications as $notification)
                <div class="p-6 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition {{ $notification->isUnread() ? 'bg-blue-50 dark:bg-blue-950/30 border-l-4 border-blue-500' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <!-- Notification Icon -->
                                <div class="flex-shrink-0">
                                    @switch($notification->type)
                                        @case('mention')
                                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                                @
                                            </div>
                                            @break
                                        @case('like')
                                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                                ❤️
                                            </div>
                                            @break
                                        @case('comment')
                                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                                💬
                                            </div>
                                            @break
                                        @case('share')
                                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                                🔗
                                            </div>
                                            @break
                                        @case('follower')
                                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                                👤
                                            </div>
                                            @break
                                        @default
                                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                🔔
                                            </div>
                                    @endswitch
                                </div>

                                <!-- Title and Status -->
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $notification->title }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ ucfirst($notification->type) }} • {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                @if($notification->isUnread())
                                <div class="flex-shrink-0">
                                    <span class="inline-block w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                                </div>
                                @endif
                            </div>

                            <!-- Message -->
                            <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $notification->message }}</p>

                            <!-- Actor Info -->
                            @if($notification->actor)
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                by <span class="font-medium text-gray-900 dark:text-white">{{ $notification->actor->name }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                                View
                            </a>
                            @endif

                            <div class="relative group">
                                <button type="button" class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-700 rounded-lg shadow-lg border border-gray-200 dark:border-slate-600 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition z-10">
                                    @if($notification->isUnread())
                                    <form action="{{ route('notification.mark-read', $notification) }}" method="POST" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-600 first:rounded-t-lg">
                                            Mark as Read
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('notification.mark-unread', $notification) }}" method="POST" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-600 first:rounded-t-lg">
                                            Mark as Unread
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('notification.delete', $notification) }}" method="POST" class="block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-slate-600 last:rounded-b-lg" onclick="return confirm('Delete notification?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                {{ $notifications->links() }}
            </div>
            @endif
            @else
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No notifications</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    @if($filter === 'unread')
                        All caught up! You have no unread notifications.
                    @elseif($filter === 'mentions')
                        No mentions yet. Start a conversation!
                    @else
                        You haven't received any notifications yet.
                    @endif
                </p>
            </div>
            @endif
        </div>

        <!-- Recently Read Section -->
        @if($recentRead->isNotEmpty())
        <div class="mt-12 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recently Read</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-slate-700 max-h-64 overflow-y-auto">
                @foreach($recentRead as $notification)
                <div class="px-6 py-3 flex items-start justify-between gap-4 opacity-75">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $notification->title }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">{{ $notification->read_at?->format('M d, H:i') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>

<style>
    @supports (scrollbar-gutter: stable) {
        .max-h-64 {
            scrollbar-gutter: stable;
        }
    }
</style>
@endsection
