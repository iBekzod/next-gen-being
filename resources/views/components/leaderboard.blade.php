@props(['learners' => null, 'currentUser' => null, 'limit' => 10])

@php
    $service = app(\App\Services\Tutorial\TutorialProgressService::class);
    $learners = $learners ?? $service->getTopLearners($limit);
    $currentUser = $currentUser ?? auth()->user();
    $userPosition = $currentUser ? $service->getUserLeaderboardPosition($currentUser) : null;
@endphp

<div class="rounded-xl bg-white dark:bg-slate-800 shadow-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-700/50 dark:to-slate-700/30">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="text-2xl">🏆</span>
                Top Learners
            </h2>
            @if($userPosition)
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full text-sm font-bold">
                Your Rank: #{{ $userPosition }}
            </span>
            @endif
        </div>
    </div>

    <!-- Leaderboard List -->
    <div class="divide-y divide-gray-200 dark:divide-slate-700">
        @forelse($learners as $index => $learner)
        <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors {{ $currentUser && $learner->id === $currentUser->id ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
            <div class="flex items-center justify-between gap-4">
                <!-- Rank & User Info -->
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <!-- Medal -->
                    <div class="flex-shrink-0 text-center">
                        @if($index === 0)
                        <div class="text-2xl">🥇</div>
                        <p class="text-xs font-bold text-amber-600 dark:text-amber-400">1st</p>
                        @elseif($index === 1)
                        <div class="text-2xl">🥈</div>
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400">2nd</p>
                        @elseif($index === 2)
                        <div class="text-2xl">🥉</div>
                        <p class="text-xs font-bold text-orange-600 dark:text-orange-400">3rd</p>
                        @else
                        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 dark:bg-slate-700 font-bold text-gray-700 dark:text-gray-300 text-sm">
                            {{ $index + 1 }}
                        </div>
                        @endif
                    </div>

                    <!-- User Info -->
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white truncate">
                            {{ $learner->name }}
                            @if($currentUser && $learner->id === $currentUser->id)
                            <span class="ml-1 text-xs font-normal text-blue-600 dark:text-blue-400">(You)</span>
                            @endif
                        </p>
                        @php
                            $learnerStats = $service->getUserStats($learner);
                        @endphp
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                            {{ $learnerStats['total_parts_completed'] }} parts • {{ $learnerStats['total_series_completed'] }} series
                        </p>
                    </div>
                </div>

                <!-- Points & Achievements -->
                <div class="flex-shrink-0 text-right">
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ $learner->achievements->sum('points') }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        {{ $learner->achievements->count() }} achievements
                    </p>
                </div>

                <!-- Badges (top 3 achievements) -->
                <div class="flex-shrink-0 flex gap-1">
                    @foreach($learner->achievements()->latest('earned_at')->take(3)->get() as $achievement)
                    <div class="group relative">
                        <span class="text-lg">{{ $achievement->icon }}</span>
                        <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block z-50 w-48">
                            <div class="bg-gray-900 dark:bg-black text-white text-xs rounded-lg px-3 py-2 border border-gray-700 shadow-lg">
                                <p class="font-bold">{{ $achievement->name }}</p>
                                <p class="text-gray-300 text-xs mt-1">{{ $achievement->description }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <p class="text-gray-600 dark:text-gray-400 mb-3">No learners yet</p>
            <a href="{{ route('tutorials.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700">
                Start learning and join the leaderboard
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @endforelse
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 border-t border-gray-200 dark:border-slate-700 text-center">
        <a href="{{ route('dashboard.learning-leaderboard') ?? '#' }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700">
            View Full Leaderboard →
        </a>
    </div>
</div>
