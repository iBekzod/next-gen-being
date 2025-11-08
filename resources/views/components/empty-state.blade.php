@props(['icon' => '📭', 'title' => 'No data yet', 'description' => null, 'action' => null])

<div class="flex flex-col items-center justify-center py-12">
    <div class="text-6xl mb-4">{{ $icon }}</div>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
    @if($description)
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center max-w-sm">{{ $description }}</p>
    @endif

    @if($action)
    <a href="{{ $action['url'] }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
        @if($action['icon'] ?? null)
        {!! $action['icon'] !!}
        @endif
        {{ $action['label'] }}
    </a>
    @endif
</div>
