@props(['type' => 'info', 'title' => null, 'closeable' => true])

@php
    $typeConfig = match($type) {
        'success' => [
            'bg' => 'bg-green-50 dark:bg-green-950/30',
            'border' => 'border-green-200 dark:border-green-800',
            'icon' => '✓',
            'text' => 'text-green-800 dark:text-green-300',
            'title' => 'text-green-900 dark:text-green-200',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/30',
            'border' => 'border-amber-200 dark:border-amber-800',
            'icon' => '⚠',
            'text' => 'text-amber-800 dark:text-amber-300',
            'title' => 'text-amber-900 dark:text-amber-200',
        ],
        'danger' => [
            'bg' => 'bg-red-50 dark:bg-red-950/30',
            'border' => 'border-red-200 dark:border-red-800',
            'icon' => '✕',
            'text' => 'text-red-800 dark:text-red-300',
            'title' => 'text-red-900 dark:text-red-200',
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-950/30',
            'border' => 'border-blue-200 dark:border-blue-800',
            'icon' => 'ℹ',
            'text' => 'text-blue-800 dark:text-blue-300',
            'title' => 'text-blue-900 dark:text-blue-200',
        ],
        default => [
            'bg' => 'bg-gray-50 dark:bg-slate-900',
            'border' => 'border-gray-200 dark:border-slate-700',
            'icon' => '●',
            'text' => 'text-gray-800 dark:text-gray-300',
            'title' => 'text-gray-900 dark:text-white',
        ],
    };
@endphp

<div x-data="{ open: true }"
     x-show="open"
     x-transition:leave="transition ease-in duration-200"
     class="rounded-lg border {{ $typeConfig['bg'] }} {{ $typeConfig['border'] }} p-4 {{ $typeConfig['text'] }}">
    <div class="flex gap-4">
        <!-- Icon -->
        <div class="flex-shrink-0 flex items-start pt-0.5">
            <span class="text-lg font-bold {{ $typeConfig['title'] }}">{{ $typeConfig['icon'] }}</span>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            @if($title)
            <h3 class="font-semibold {{ $typeConfig['title'] }}">{{ $title }}</h3>
            @endif
            <div @class(['text-sm mt-1' => $title, 'text-sm' => !$title])>
                {{ $slot }}
            </div>
        </div>

        <!-- Close Button -->
        @if($closeable)
        <div class="flex-shrink-0">
            <button type="button"
                    @click="open = false"
                    class="inline-flex text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @endif
    </div>
</div>
