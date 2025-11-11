@props(['learningPath' => null])

@php
    if (!$learningPath) {
        return;
    }

    $items = $learningPath->items()->ordered()->get();
    $totalItems = $items->count();
    $completedItems = $items->where('completed', true)->count();
    $progressPercentage = $totalItems > 0 ? ($completedItems / $totalItems) * 100 : 0;
@endphp

<div class="rounded-xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-indigo-700 dark:to-blue-800 border-b border-blue-200 dark:border-indigo-600">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="text-2xl">📈</span>
                {{ $learningPath->name }}
            </h3>
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-white/20 text-white rounded-full text-sm font-bold border border-white/30">
                {{ $completedItems }}/{{ $totalItems }} completed
            </span>
        </div>
        <p class="text-sm text-blue-100">{{ $learningPath->description }}</p>
    </div>

    <!-- Overall Progress -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Overall Progress</span>
            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($progressPercentage, 1) }}%</span>
        </div>
        <div class="w-full h-3 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-500 to-blue-500 rounded-full transition-all duration-500"
                 style="width: {{ $progressPercentage }}%"></div>
        </div>
    </div>

    <!-- Items List -->
    @if($items->isNotEmpty())
    <div class="divide-y divide-gray-200 dark:divide-slate-700">
        @foreach($items as $item)
        <div class="px-6 py-4 {{ !$item->completed && $item->order == ($completedItems + 1) ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : 'hover:bg-gray-50 dark:hover:bg-slate-700/50' }} transition-colors">
            <div class="flex items-start gap-4">
                <!-- Order Badge -->
                <div class="flex-shrink-0">
                    @if($item->completed)
                    <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 font-bold text-sm shadow-sm">
                        ✓
                    </div>
                    @elseif($item->order == ($completedItems + 1))
                    <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-500 dark:bg-blue-600 text-white font-bold text-sm shadow-md">
                        {{ str_pad($item->order, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    @else
                    <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-gray-300 font-bold text-sm">
                        {{ str_pad($item->order, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1">
                            <h4 class="font-semibold {{ $item->completed ? 'text-gray-600 dark:text-gray-400 line-through' : ($item->order == ($completedItems + 1) ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-white') }}">
                                {{ $item->title }}
                            </h4>
                            @if($item->description)
                            <p class="text-sm {{ $item->order == ($completedItems + 1) ? 'text-blue-800 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400' }} mt-1 line-clamp-2">{{ $item->description }}</p>
                            @endif
                        </div>

                        <!-- Status -->
                        <div class="flex-shrink-0">
                            @if($item->completed)
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-full text-xs font-bold shadow-sm">
                                <span>✓</span>
                                <span>Done</span>
                            </span>
                            @elseif($item->order == ($completedItems + 1))
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-bold shadow-md">
                                <span>◉</span>
                                <span>In Progress</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-300 rounded-full text-xs font-bold">
                                <span>○</span>
                                <span>Pending</span>
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Meta Info -->
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-xs {{ $item->order == ($completedItems + 1) ? 'text-blue-700 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400' }}">
                        @if($item->estimated_duration_minutes)
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $item->getEstimatedTimeLabel() }}
                        </span>
                        @endif

                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                              style="background-color: rgba({{ match($item->difficulty_level) {
                                  'beginner' => '59, 130, 246',
                                  'intermediate' => '99, 102, 241',
                                  'advanced' => '251, 146, 60',
                                  'expert' => '239, 68, 68',
                                  default => '107, 114, 128'
                              } }}, 0.1); color: {{ match($item->difficulty_level) {
                                  'beginner' => '#3b82f6',
                                  'intermediate' => '#6366f1',
                                  'advanced' => '#fb923c',
                                  'expert' => '#ef4444',
                                  default => '#6b7280'
                              } }};">
                            {{ ucfirst($item->difficulty_level) }}
                        </span>

                        @if($item->reason_for_recommendation)
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01"/>
                            </svg>
                            {{ $item->reason_for_recommendation }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Action Button -->
                @if(!$item->completed && $item->post)
                <div class="flex-shrink-0">
                    <a href="{{ route('posts.show', $item->post) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-semibold text-xs transition-colors whitespace-nowrap">
                        <span>Start</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Completion Message -->
    @if($progressPercentage === 100)
    <div class="px-6 py-6 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-t border-green-200 dark:border-emerald-700 text-center">
        <p class="text-2xl mb-2">🎉</p>
        <p class="text-lg font-bold text-green-900 dark:text-green-200">Congratulations!</p>
        <p class="text-sm text-green-700 dark:text-green-300 mt-1">You've completed this learning path!</p>
    </div>
    @endif
    @else
    <div class="px-6 py-12 text-center">
        <p class="text-gray-600 dark:text-gray-400">This learning path has no items yet.</p>
    </div>
    @endif
</div>
