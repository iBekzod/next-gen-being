@props(['label', 'value', 'change' => null, 'icon' => null, 'color' => 'blue', 'format' => 'default', 'href' => null])

@php
$colorClasses = [
    'blue' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    'green' => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    'red' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    'purple' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
    'amber' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
    'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
];

$iconClass = $colorClasses[$color] ?? $colorClasses['blue'];
$Element = $href ? 'a' : 'div';
@endphp

<{{ $Element }} @if($href) href="{{ $href }}" @endif @class([
    'rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6',
    'hover:shadow-md transition cursor-pointer' => $href,
])>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $label }}</p>
            <div class="mt-3 flex items-baseline gap-2">
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    @if($format === 'currency')
                        ${{ number_format($value, 2) }}
                    @elseif($format === 'percent')
                        {{ number_format($value, 1) }}%
                    @elseif($format === 'number')
                        {{ number_format($value) }}
                    @else
                        {{ $value }}
                    @endif
                </p>
                @if($change)
                <span class="text-sm font-medium
                    @if($change > 0)
                        text-green-600 dark:text-green-400
                    @else
                        text-red-600 dark:text-red-400
                    @endif
                ">
                    @if($change > 0) ↑ @else ↓ @endif {{ abs($change) }}%
                </span>
                @endif
            </div>
        </div>
        @if($icon)
        <div class="{{ $iconClass }} p-3 rounded-lg">
            {!! $icon !!}
        </div>
        @endif
    </div>
</{{ $Element }}>
