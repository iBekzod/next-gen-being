<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Platform</h3>
            <p class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->socialMediaAccount->platform === 'youtube') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                    @elseif($record->socialMediaAccount->platform === 'instagram') bg-pink-100 text-pink-800 dark:bg-pink-900/20 dark:text-pink-400
                    @elseif($record->socialMediaAccount->platform === 'facebook') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                    @elseif($record->socialMediaAccount->platform === 'twitter') bg-sky-100 text-sky-800 dark:bg-sky-900/20 dark:text-sky-400
                    @elseif($record->socialMediaAccount->platform === 'linkedin') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                    @endif">
                    {{ match($record->socialMediaAccount->platform) {
                        'youtube' => '▶️ YouTube',
                        'instagram' => '📷 Instagram',
                        'facebook' => '📘 Facebook',
                        'twitter' => '🐦 Twitter',
                        'linkedin' => '💼 LinkedIn',
                        'telegram' => '✈️ Telegram',
                        default => ucfirst($record->socialMediaAccount->platform)
                    } }}
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</h3>
            <p class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->status === 'pending') bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                    @elseif($record->status === 'publishing') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                    @elseif($record->status === 'published') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                    @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                    @endif">
                    {{ ucfirst($record->status) }}
                </span>
            </p>
        </div>
    </div>

    @if($record->platform_url)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Platform URL</h3>
        <div class="mt-1">
            <a href="{{ $record->platform_url }}" target="_blank" class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                {{ $record->platform_url }}
                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
            </a>
        </div>
    </div>
    @endif

    @if($record->platform_post_id)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Platform Post ID</h3>
        <p class="mt-1 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $record->platform_post_id }}</p>
    </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($record->views_count ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Views</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($record->likes_count ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Likes</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($record->comments_count ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Comments</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($record->shares_count ?? 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Shares</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Published At</h3>
            <p class="mt-1 text-gray-900 dark:text-gray-100">
                {{ $record->published_at ? $record->published_at->format('M d, Y h:i A') : 'Not published yet' }}
            </p>
            @if($record->published_at)
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->published_at->diffForHumans() }}</p>
            @endif
        </div>

        @if($record->last_synced_at)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Metrics Sync</h3>
            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $record->last_synced_at->format('M d, Y h:i A') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->last_synced_at->diffForHumans() }}</p>
        </div>
        @endif
    </div>

    @if($record->error_message)
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Error</h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <p>{{ $record->error_message }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
