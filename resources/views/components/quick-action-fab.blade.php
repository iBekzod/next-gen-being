@props(['actions' => []])

<div class="fixed bottom-6 right-6 flex flex-col items-end gap-3 z-40">
    <!-- Main FAB Button with dropdown -->
    <div x-data="{ open: false }" class="relative">
        <!-- Backdrop -->
        <div x-show="open"
             @click="open = false"
             x-cloak
             class="fixed inset-0 z-30"
             style="background: transparent;"></div>

        <!-- Action Buttons -->
        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="absolute bottom-16 right-0 space-y-2">
            @foreach($actions as $action)
            <div class="flex items-center gap-3 group">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 px-3 py-2 rounded-lg shadow-lg border border-gray-200 dark:border-slate-700 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                    {{ $action['label'] }}
                </span>
                <a href="{{ $action['url'] }}"
                   class="inline-flex items-center justify-center w-12 h-12 rounded-full shadow-lg border-2 transition-all duration-200 hover:scale-110
                   {{ match($action['color'] ?? 'blue') {
                       'blue' => 'bg-blue-600 hover:bg-blue-700 border-blue-600 text-white',
                       'green' => 'bg-green-600 hover:bg-green-700 border-green-600 text-white',
                       'red' => 'bg-red-600 hover:bg-red-700 border-red-600 text-white',
                       'purple' => 'bg-purple-600 hover:bg-purple-700 border-purple-600 text-white',
                       'amber' => 'bg-amber-600 hover:bg-amber-700 border-amber-600 text-white',
                       default => 'bg-blue-600 hover:bg-blue-700 border-blue-600 text-white',
                   } }}">
                    @if(isset($action['icon']))
                        <span class="text-lg">{{ $action['icon'] }}</span>
                    @else
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </a>
            </div>
            @endforeach
        </div>

        <!-- Main Toggle Button -->
        <button type="button"
                @click="open = !open"
                class="inline-flex items-center justify-center w-14 h-14 rounded-full shadow-lg bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white transition-all duration-200 hover:shadow-xl hover:scale-110 relative z-40 border-2 border-blue-600">
            <svg x-show="!open" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            <svg x-show="open" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
</div>

<style>
    @media (max-width: 640px) {
        [x-cloak] { display: none; }
    }
</style>
