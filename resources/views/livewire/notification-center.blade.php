<div class="relative" @click.away="@this.set('showDropdown', false)" x-data="{ showDropdown: @entangle('showDropdown') }">
    <!-- Notification Bell Button -->
    <button @click="showDropdown = !showDropdown; $wire.loadNotifications()"
        class="relative p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

        <!-- Unread Badge -->
        @if ($unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
                wire:key="badge-{{ $unreadCount }}">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div x-show="showDropdown" x-transition
        class="absolute right-0 z-50 w-96 mt-2 bg-white rounded-lg shadow-xl dark:bg-gray-800 max-h-96 overflow-y-auto">

        <!-- Header -->
        <div class="sticky top-0 px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
                @if ($unreadCount > 0)
                    <button wire:click="markAllAsRead"
                        class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                        Mark all as read
                    </button>
                @endif
            </div>
        </div>

        <!-- Notifications List -->
        @if (empty($notifications))
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400">No notifications yet</p>
            </div>
        @else
            @foreach ($notifications as $notif)
                <div wire:key="notif-{{ $notif['id'] }}"
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer group"
                    @click="$wire.markAsRead({{ $notif['id'] }}); window.location.href = '{{ $notif['action_url'] }}'">

                    <div class="flex items-start space-x-3">
                        <!-- Avatar -->
                        @if ($notif['actor_avatar'])
                            <img src="{{ asset('storage/' . $notif['actor_avatar']) }}"
                                alt="{{ $notif['actor_name'] }}"
                                class="w-10 h-10 rounded-full flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl">{{ $this->getNotificationIcon($notif['type']) }}</span>
                            </div>
                        @endif

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $notif['title'] }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mt-1">
                                {{ $notif['message'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                {{ $notif['created_at'] }}
                            </p>
                        </div>

                        <!-- Close Button -->
                        <button wire:click.stop="deleteNotification({{ $notif['id'] }})"
                            class="hidden group-hover:block text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 ml-2 flex-shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        @endif

        <!-- Footer -->
        <div class="sticky bottom-0 px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <a href="{{ route('notifications.index') }}"
                class="text-sm text-center block text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                View all notifications
            </a>
        </div>
    </div>
</div>
