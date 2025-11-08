@props(['title' => null, 'icon' => null, 'hover' => true, 'clickable' => false, 'border' => 'default'])

<div @class([
    'rounded-xl bg-white dark:bg-slate-800 shadow-sm border overflow-hidden',
    'hover:shadow-md transition' => $hover,
    'cursor-pointer' => $clickable,
    'border-gray-200 dark:border-slate-700' => $border === 'default',
    'border-blue-200 dark:border-blue-700' => $border === 'blue',
    'border-green-200 dark:border-green-700' => $border === 'green',
    'border-red-200 dark:border-red-700' => $border === 'red',
])>
    @if($title)
    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if($icon)
            <div class="text-2xl">{{ $icon }}</div>
            @endif
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
        </div>
        {{ $header ?? '' }}
    </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @if($footer = $footer ?? null)
    <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
        {{ $footer }}
    </div>
    @endif
</div>
