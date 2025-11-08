<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
    <!-- Header with Level -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-3">
            <span class="text-4xl">{{ $this->getLevelIcon() }}</span>
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">Level</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white capitalize">{{ $level }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500 dark:text-gray-400">Reputation</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($reputationPoints) }}</p>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Progress to next level</span>
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $levelProgress }}%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500"
                style="width: {{ $levelProgress }}%"></div>
        </div>
    </div>

    <!-- Badges Section -->
    @if (!empty($badges))
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Badges ({{ count($badges) }})</h4>
                <button @click="@this.toggleDetails()"
                    class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                    {{ $showDetails ? 'Hide' : 'View all' }}
                </button>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach (array_slice($badges, 0, 5) as $badge)
                    <div class="group relative">
                        <div class="text-2xl hover:scale-110 transition-transform cursor-help">
                            {{ $badge['icon'] ?? '⭐' }}
                        </div>
                        <div class="absolute left-0 bottom-full mb-2 hidden group-hover:block bg-gray-900 dark:bg-gray-700 text-white text-xs rounded py-1 px-2 whitespace-nowrap z-10">
                            {{ $badge['name'] ?? 'Badge' }}
                        </div>
                    </div>
                @endforeach

                @if (count($badges) > 5)
                    <div class="text-2xl flex items-center justify-center w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded text-sm font-bold text-gray-600 dark:text-gray-300">
                        +{{ count($badges) - 5 }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Achievements Section (if showing details) -->
    @if ($showDetails && !empty($achievements))
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Recent Achievements</h4>
            <div class="space-y-2">
                @foreach (array_slice($achievements, 0, 5) as $achievement)
                    <div class="flex items-center space-x-2 text-sm">
                        <span class="text-lg">✓</span>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $achievement['achievement_code'] ?? 'Achievement' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($achievement['achieved_at'])->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-4">
        <div class="text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->posts()->count() }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Posts</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->followers()->count() }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Followers</p>
        </div>
    </div>
</div>
