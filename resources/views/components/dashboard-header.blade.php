@props(['title', 'description' => null, 'icon' => null, 'badge' => null, 'actions' => []])

<div class="flex items-start justify-between mb-8">
    <div class="flex-1">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-sm mb-4">
            <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4m-4-4v4"/>
                </svg>
                Dashboard
            </a>
            <span class="text-gray-400 dark:text-gray-600">/</span>
            <span class="text-gray-900 dark:text-gray-200 font-medium">{{ $title }}</span>
        </nav>

        <!-- Title Section -->
        <div class="flex items-center gap-4">
            @if($icon)
            <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                {!! $icon !!}
            </div>
            @endif
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
                    @if($badge)
                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $badge['class'] }}">
                        {{ $badge['text'] }}
                    </span>
                    @endif
                </div>
                @if($description)
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $description }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if(count($actions) > 0)
    <div class="flex gap-3">
        @foreach($actions as $action)
        <a href="{{ $action['url'] }}" @class([
            'inline-flex items-center px-4 py-2 rounded-lg font-medium transition',
            'bg-blue-600 hover:bg-blue-700 text-white' => $action['type'] === 'primary',
            'bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-white' => $action['type'] === 'secondary',
        ])>
            @if($action['icon'] ?? null)
            {!! $action['icon'] !!}
            @endif
            {{ $action['label'] }}
        </a>
        @endforeach
    </div>
    @endif
</div>
