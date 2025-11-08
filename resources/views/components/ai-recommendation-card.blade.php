@props(['recommendation' => null])

@php
    if (!$recommendation) {
        return;
    }

    $iconMap = [
        'next_tutorial' => '📚',
        'skill_gap' => '⬆️',
        'related_content' => '🔗',
        'challenge' => '💪',
        'review' => '🔄',
    ];

    $colorMap = [
        'next_tutorial' => 'blue',
        'skill_gap' => 'purple',
        'related_content' => 'indigo',
        'challenge' => 'orange',
        'review' => 'emerald',
    ];

    $icon = $iconMap[$recommendation->recommendation_type] ?? '💡';
    $color = $colorMap[$recommendation->recommendation_type] ?? 'gray';
    $colorClasses = match($color) {
        'blue' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
        'purple' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800',
        'indigo' => 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800',
        'orange' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
        'emerald' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800',
        default => 'bg-gray-50 dark:bg-gray-900/20 border-gray-200 dark:border-gray-800',
    };
@endphp

<div class="rounded-lg {{ $colorClasses }} border p-4 hover:shadow-lg transition-shadow group cursor-pointer">
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1">
            <!-- Header -->
            <div class="flex items-start gap-3 mb-2">
                <span class="text-2xl flex-shrink-0">{{ $icon }}</span>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2">{{ $recommendation->title }}</h4>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">{{ ucfirst(str_replace('_', ' ', $recommendation->recommendation_type)) }}</p>
                </div>
            </div>

            <!-- Description -->
            @if($recommendation->description)
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 line-clamp-2">{{ $recommendation->description }}</p>
            @endif

            <!-- Reason -->
            @if($recommendation->reason)
            <div class="inline-flex items-center gap-1 px-2 py-1 bg-white dark:bg-slate-800 rounded border border-gray-200 dark:border-slate-700 text-xs text-gray-600 dark:text-gray-400 mb-3">
                <span>💡</span>
                <span class="line-clamp-1">{{ $recommendation->reason }}</span>
            </div>
            @endif

            <!-- Confidence & Actions -->
            <div class="flex items-center justify-between pt-2 border-t border-current border-opacity-10">
                <div class="flex items-center gap-2">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                        {{ $recommendation->getConfidencePercentage() }}% match
                    </div>
                    <div class="w-16 h-1.5 bg-white dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-yellow-400 to-green-500 transition-all"
                             style="width: {{ $recommendation->confidence_score * 100 }}%"></div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button class="p-1.5 hover:bg-white dark:hover:bg-slate-800 rounded text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                            title="Dismiss"
                            @click="$dispatch('dismiss-recommendation', { id: {{ $recommendation->id }} })">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
