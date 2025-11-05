<div class="flex items-center gap-3">
    @auth
        @if(Auth::id() !== $blogger->id)
            <button
                wire:click="toggleFollow"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white transition-colors duration-150 ease-in-out
                    {{ $isFollowing ? 'bg-gray-600 hover:bg-gray-700' : 'bg-blue-600 hover:bg-blue-700' }}
                    disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="toggleFollow">
                    {{ $isFollowing ? 'Following' : 'Follow' }}
                </span>
                <span wire:loading wire:target="toggleFollow">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        @endif
    @else
        <button
            onclick="alert('Please login to follow this blogger')"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-150 ease-in-out"
        >
            Follow
        </button>
    @endauth

    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <span class="font-semibold">{{ number_format($followerCount) }}</span>
        <span class="ml-1">{{ $followerCount === 1 ? 'Follower' : 'Followers' }}</span>
    </div>
</div>
