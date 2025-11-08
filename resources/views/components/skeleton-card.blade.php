@props(['lines' => 3, 'animated' => true])

<div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
    <div class="space-y-4">
        <!-- Header skeleton -->
        <div class="h-6 bg-gray-200 dark:bg-slate-700 rounded-lg w-1/3 {{ $animated ? 'animate-pulse' : '' }}"></div>

        <!-- Content skeleton -->
        @for($i = 0; $i < $lines; $i++)
        <div class="h-4 bg-gray-200 dark:bg-slate-700 rounded-lg {{ $animated ? 'animate-pulse' : '' }}"
             style="width: {{ $i === $lines - 1 ? '80%' : '100%' }}; animation-delay: {{ $i * 100 }}ms;"></div>
        @endfor
    </div>
</div>
