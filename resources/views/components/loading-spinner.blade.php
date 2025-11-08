@props(['message' => 'Loading...', 'size' => 'md'])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-6 h-6',
        'lg' => 'w-12 h-12',
        default => 'w-10 h-10',
    };
@endphp

<div class="flex flex-col items-center justify-center py-12">
    <div class="relative {{ $sizeClasses }}">
        <!-- Outer spinning ring -->
        <svg class="absolute inset-0 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="color: rgb(203, 213, 225);"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="color: rgb(59, 130, 246);"></path>
        </svg>

        <!-- Center dot -->
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
        </div>
    </div>

    @if($message)
    <p class="mt-4 text-gray-600 dark:text-gray-400 text-sm font-medium">{{ $message }}</p>
    @endif
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>
