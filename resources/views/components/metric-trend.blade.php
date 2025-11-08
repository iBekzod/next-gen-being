@props(['label' => null, 'value' => 0, 'change' => 0, 'icon' => null, 'color' => 'blue', 'format' => 'number'])

@php
    $isPositive = $change >= 0;
    $colorClasses = match($color) {
        'green' => 'bg-green-50 dark:bg-green-950/30 border-green-200 dark:border-green-800 text-green-900 dark:text-green-100',
        'red' => 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800 text-red-900 dark:text-red-100',
        'amber' => 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-100',
        'purple' => 'bg-purple-50 dark:bg-purple-950/30 border-purple-200 dark:border-purple-800 text-purple-900 dark:text-purple-100',
        'blue' => 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-100',
        default => 'bg-gray-50 dark:bg-slate-900 border-gray-200 dark:border-slate-700 text-gray-900 dark:text-white',
    };

    $trendColor = match(true) {
        $isPositive => 'text-green-600 dark:text-green-400',
        default => 'text-red-600 dark:text-red-400',
    };

    $formattedValue = match($format) {
        'currency' => '$' . number_format($value, 2),
        'percent' => number_format($value, 1) . '%',
        'short' => $value >= 1000 ? round($value / 1000, 1) . 'k' : $value,
        default => number_format($value),
    };
@endphp

<div class="rounded-lg border {{ $colorClasses }} p-4">
    <div class="flex items-start justify-between mb-2">
        <div class="flex-1">
            @if($label)
            <p class="text-sm font-medium opacity-75">{{ $label }}</p>
            @endif
            <p class="mt-1 text-2xl font-bold">{{ $formattedValue }}</p>
        </div>

        @if($icon)
        <div class="flex-shrink-0 text-xl opacity-50">
            {{ $icon }}
        </div>
        @endif
    </div>

    @if($change !== 0)
    <div class="flex items-center gap-1 {{ $trendColor }}">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            @if($isPositive)
            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414-1.414L13.586 7H12z" clip-rule="evenodd" />
            @else
            <path fill-rule="evenodd" d="M12 13a1 1 0 110 2H7a1 1 0 01-1-1V9a1 1 0 112 0v3.586l4.293-4.293a1 1 0 011.414 1.414L9.414 13H12z" clip-rule="evenodd" />
            @endif
        </svg>
        <span class="text-sm font-semibold">
            {{ abs($change) }}%
            <span class="text-xs opacity-75 ml-1">vs last period</span>
        </span>
    </div>
    @endif
</div>
