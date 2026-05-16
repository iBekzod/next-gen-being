@extends('layouts.app')

@section('title', 'Learning Paths · NextGenBeing')
@section('description', 'Curated learning sequences combining the best tutorials and articles into structured paths.')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-5xl">
        <h1 class="text-4xl font-bold tracking-tight">Learning paths</h1>
        <p class="mt-4 text-lg text-slate-300">Curated reading sequences that take you from fundamentals to production. Each path is hand-picked from our deep-dive articles and tutorial series.</p>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900 py-12">
    <div class="px-6 mx-auto max-w-5xl space-y-4">
        @forelse($paths as $path)
        <a href="{{ route('learning-paths.show', $path) }}"
           class="block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:shadow-lg hover:border-blue-300 transition">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full {{ $path->skill_level === 'beginner' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : ($path->skill_level === 'advanced' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300') }}">{{ $path->skill_level }}</span>
                        <span class="text-xs text-gray-500">{{ $path->items_count }} {{ \Illuminate\Support\Str::plural('part', $path->items_count) }} · ~{{ $path->estimated_duration_hours }}h</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $path->name }}</h2>
                    @if($path->description)
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $path->description }}</p>
                    @endif
                    @if($path->goal)
                    <p class="mt-3 text-xs text-gray-500"><strong>Goal:</strong> {{ $path->goal }}</p>
                    @endif
                </div>
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
        @empty
        <p class="text-gray-600 dark:text-gray-400">No learning paths yet — check back soon.</p>
        @endforelse
    </div>
</div>
@endsection
