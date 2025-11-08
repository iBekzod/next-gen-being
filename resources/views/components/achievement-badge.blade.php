@props(['achievement', 'earned' => false, 'size' => 'md'])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-12 h-12 text-lg',
        'lg' => 'w-20 h-20 text-4xl',
        default => 'w-16 h-16 text-2xl',
    };

    $colorClass = match($achievement->color) {
        'blue' => 'bg-blue-100 dark:bg-blue-900/30 border-blue-300 dark:border-blue-700',
        'green' => 'bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-700',
        'red' => 'bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700',
        'purple' => 'bg-purple-100 dark:bg-purple-900/30 border-purple-300 dark:border-purple-700',
        'amber' => 'bg-amber-100 dark:bg-amber-900/30 border-amber-300 dark:border-amber-700',
        'emerald' => 'bg-emerald-100 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700',
        'indigo' => 'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700',
        'cyan' => 'bg-cyan-100 dark:bg-cyan-900/30 border-cyan-300 dark:border-cyan-700',
        'yellow' => 'bg-yellow-100 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700',
        default => 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600',
    };
@endphp

<div class="group cursor-help relative">
    <!-- Badge -->
    <div class="flex items-center justify-center {{ $sizeClasses }} rounded-xl border-2 {{ $colorClass }} transition-all duration-200 {{ $earned ? 'hover:scale-110 shadow-lg' : 'opacity-30' }}"
         :class="{ 'ring-2 ring-yellow-400': {{ $earned ? 'true' : 'false' }} }">
        <span class="filter {{ !$earned ? 'grayscale' : '' }}">{{ $achievement->icon }}</span>
    </div>

    <!-- Tooltip -->
    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-50">
        <div class="bg-gray-900 dark:bg-black text-white text-xs rounded-lg px-3 py-2 whitespace-nowrap border border-gray-700 shadow-lg">
            <p class="font-bold">{{ $achievement->name }}</p>
            <p class="text-gray-300 text-xs mt-1">{{ $achievement->description }}</p>
            <p class="text-yellow-400 text-xs mt-1 font-semibold">{{ $achievement->points }} pts</p>

            @if($earned)
            <p class="text-green-400 text-xs mt-1">✓ Earned</p>
            @endif
        </div>

        <!-- Tooltip arrow -->
        <div class="absolute top-full left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-900 dark:bg-black border-r border-b border-gray-700 transform rotate-45"></div>
    </div>
</div>
