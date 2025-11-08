@props(['icon' => '📭', 'title' => 'No data yet', 'description' => null, 'actions' => [], 'size' => 'md'])

@php
    $iconSizeClass = match($size) {
        'sm' => 'w-10 h-10 text-lg',
        'lg' => 'w-16 h-16 text-4xl',
        default => 'w-12 h-12 text-2xl',
    };

    $paddingClass = match($size) {
        'sm' => 'py-6',
        'lg' => 'py-16',
        default => 'py-12',
    };
@endphp

<div class="flex flex-col items-center justify-center {{ $paddingClass }}">
    <!-- Icon -->
    <div class="mb-4 flex items-center justify-center {{ $iconSizeClass }} rounded-full bg-gray-100 dark:bg-slate-700">
        @if(strpos($icon, '<') === 0)
            {!! $icon !!}
        @else
            <span>{{ $icon }}</span>
        @endif
    </div>

    <!-- Title -->
    <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>

    <!-- Description -->
    @if($description)
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 max-w-xs text-center">
        {{ $description }}
    </p>
    @endif

    <!-- Actions -->
    @if(count($actions) > 0)
    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
        @foreach($actions as $action)
        <a href="{{ $action['url'] }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition
           {{ match($action['type'] ?? 'primary') {
               'secondary' => 'bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white hover:bg-gray-200 dark:hover:bg-slate-600',
               'danger' => 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/30',
               'success' => 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/30',
               default => 'bg-blue-600 dark:bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-700',
           } }}">
            @if(isset($action['icon']))
                <span>{{ $action['icon'] }}</span>
            @endif
            {{ $action['label'] }}
        </a>
        @endforeach
    </div>
    @endif

    <!-- Slot Content -->
    @if($slot->isNotEmpty())
    <div class="mt-6 text-center">
        {{ $slot }}
    </div>
    @endif
</div>
