@props(['learningPath' => null])

@php
    if (!$learningPath) {
        return;
    }

    $progress = $learningPath->getProgressPercentage();
    $completedItems = $learningPath->getCompletedItemsCount();
    $totalItems = $learningPath->getTotalItemsCount();
    $nextItem = $learningPath->getNextItem();
@endphp

<div class="rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-slate-800 dark:to-slate-900 border border-indigo-200 dark:border-slate-700 shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
    <!-- Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-indigo-100 to-blue-100 dark:from-slate-700 dark:to-slate-700 border-b border-indigo-200 dark:border-slate-700">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-2xl">🎯</span>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $learningPath->name }}</h3>
                </div>
                @if($learningPath->goal)
                <p class="text-sm text-gray-600 dark:text-gray-400">Goal: {{ ucfirst(str_replace('_', ' ', $learningPath->goal)) }}</p>
                @endif
            </div>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center gap-1 px-3 py-1 {{ $learningPath->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' }} rounded-full text-xs font-bold">
                    {{ ucfirst($learningPath->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div class="px-6 py-4 border-b border-indigo-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Progress</span>
            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($progress, 1) }}%</span>
        </div>
        <div class="w-full h-2 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-500 to-blue-500 rounded-full transition-all duration-300"
                 style="width: {{ $progress }}%"></div>
        </div>
        <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
            {{ $completedItems }} of {{ $totalItems }} items completed
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-3 gap-3 px-6 py-4 border-b border-indigo-200 dark:border-slate-700">
        <div class="text-center">
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $totalItems }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Total Items</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $learningPath->estimated_duration_hours }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Est. Hours</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $completedItems }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Completed</p>
        </div>
    </div>

    <!-- Next Item -->
    @if($nextItem)
    <div class="px-6 py-4 bg-blue-50 dark:bg-slate-700/30 border-b border-indigo-200 dark:border-slate-700">
        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">NEXT ITEM</p>
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-sm font-bold">
                    {{ $nextItem->order }}
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $nextItem->title }}</p>
                @if($nextItem->estimated_duration_minutes)
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">⏱️ {{ $nextItem->getEstimatedTimeLabel() }}</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="px-6 py-4 flex gap-2">
        @if($learningPath->status === 'draft')
        <a href="{{ route('learning-paths.start', $learningPath) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors">
            <span>Start Path</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @else
        <a href="{{ route('learning-paths.show', $learningPath) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors">
            <span>Continue Learning</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif
        <button class="px-4 py-2 bg-gray-200 dark:bg-slate-700 hover:bg-gray-300 dark:hover:bg-slate-600 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors">
            View Details
        </button>
    </div>
</div>
