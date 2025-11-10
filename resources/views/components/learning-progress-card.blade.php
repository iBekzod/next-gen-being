@props(['user' => null, 'stats' => null, 'compact' => false])

@php
    if (!$stats && auth()->check()) {
        $stats = app(\App\Services\Tutorial\TutorialProgressService::class)->getUserStats(auth()->user());
    }
@endphp

@if($stats)
<div class="rounded-xl bg-gradient-to-br {{ $compact ? 'from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900' : 'from-blue-50 to-purple-50 dark:from-slate-800 dark:to-slate-900' }} border border-blue-200 dark:border-slate-700 p-6 {{ $compact ? '' : 'shadow-lg' }}">
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="text-xl">📚</span>
                    Learning Progress
                </h3>
                @if($stats['total_points'] > 0)
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-full text-sm font-bold">
                    <span>⭐</span>
                    {{ $stats['total_points'] }} pts
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Your learning journey so far</p>
        </div>

        <!-- Progress Grid -->
        <div class="grid grid-cols-{{ $compact ? '2' : '3' }} gap-4">
            <!-- Completed Parts -->
            <div class="rounded-lg bg-white dark:bg-slate-800/50 p-4 text-center border border-blue-100 dark:border-slate-700">
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_parts_completed'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Parts Completed</p>
                <div class="mt-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                    @if($stats['total_parts_read'] > 0)
                        {{ number_format(($stats['total_parts_completed'] / $stats['total_parts_read']) * 100, 0) }}%
                    @else
                        0%
                    @endif
                </div>
            </div>

            <!-- Series Completed -->
            <div class="rounded-lg bg-white dark:bg-slate-800/50 p-4 text-center border border-emerald-100 dark:border-slate-700">
                <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['total_series_completed'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Series Completed</p>
            </div>

            <!-- Hours Spent -->
            <div class="rounded-lg bg-white dark:bg-slate-800/50 p-4 text-center border border-purple-100 dark:border-slate-700">
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['total_hours_spent'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Hours Spent</p>
            </div>
        </div>

        <!-- Achievements -->
        @if($stats['total_achievements'] > 0)
        <div class="rounded-lg bg-white dark:bg-slate-800/50 p-4 border border-amber-100 dark:border-slate-700">
            <p class="text-sm font-bold text-gray-900 dark:text-white mb-3">Achievements ({{ $stats['total_achievements'] }})</p>
            <div class="flex flex-wrap gap-2">
                @foreach(auth()->user()->achievements()->latest('earned_at')->take(6)->get() as $achievement)
                <x-achievement-badge :achievement="$achievement" earned="true" size="sm" />
                @endforeach
            </div>
        </div>
        @endif

        <!-- Call to action -->
        @if($stats['total_parts_completed'] === 0)
        <div class="rounded-lg bg-blue-100 dark:bg-blue-900/30 p-4 border border-blue-200 dark:border-blue-800">
            <p class="text-sm text-blue-900 dark:text-blue-200 font-semibold">Ready to start learning?</p>
            <a href="{{ route('tutorials.index') }}" class="inline-flex items-center gap-2 mt-2 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:text-blue-800 dark:hover:text-blue-200">
                Browse Tutorials
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @elseif($stats['total_parts_completed'] > 0 && count(auth()->user()->achievements) < 3)
        <div class="rounded-lg bg-purple-100 dark:bg-purple-900/30 p-4 border border-purple-200 dark:border-purple-800">
            <p class="text-sm text-purple-900 dark:text-purple-200 font-semibold">Keep going! More achievements await!</p>
            <p class="text-xs text-purple-800 dark:text-purple-300 mt-1">Complete more parts to unlock new badges.</p>
        </div>
        @endif
    </div>
</div>
@endif
