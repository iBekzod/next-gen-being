@props(['label' => null, 'value' => 0, 'max' => 100, 'color' => 'blue', 'showLabel' => true])

@php
$percentage = ($value / $max) * 100;
$colorClasses = [
    'blue' => 'bg-blue-500',
    'green' => 'bg-green-500',
    'red' => 'bg-red-500',
    'purple' => 'bg-purple-500',
    'amber' => 'bg-amber-500',
    'emerald' => 'bg-emerald-500',
    'gradient' => 'bg-gradient-to-r from-blue-500 to-purple-600',
];
@endphp

<div>
    @if($label || $showLabel)
    <div class="flex items-center justify-between mb-2">
        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</p>
        @if($showLabel)
        <span class="text-sm font-semibold
            @if($percentage < 50)
                text-green-600 dark:text-green-400
            @elseif($percentage < 80)
                text-amber-600 dark:text-amber-400
            @else
                text-red-600 dark:text-red-400
            @endif
        ">
            {{ number_format($percentage, 0) }}%
        </span>
        @endif
    </div>
    @endif

    <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
        <div class="{{ $colorClasses[$color] ?? $colorClasses['blue'] }} h-full transition-all duration-300 ease-out" style="width: {{ min($percentage, 100) }}%"></div>
    </div>

    @if($slot->isNotEmpty())
    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        {{ $slot }}
    </div>
    @endif
</div>
