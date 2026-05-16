@extends('layouts.app')

@section('title', 'Tutorial Collections · NextGenBeing')
@section('description', 'Themed bundles of tutorials grouped by topic for focused learning.')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-5xl">
        <h1 class="text-4xl font-bold tracking-tight">Tutorial collections</h1>
        <p class="mt-4 text-lg text-slate-300">Themed bundles that group related tutorial series into one focused learning track.</p>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900 py-12">
    <div class="px-6 mx-auto max-w-5xl space-y-4">
        @forelse($collections as $c)
        <a href="{{ route('tutorial-collections.show', $c->slug) }}"
           class="block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:shadow-lg transition">
            <div class="flex items-start gap-4">
                @if($c->featured_image)
                <img src="{{ $c->featured_image }}" alt="" class="w-24 h-24 rounded-lg object-cover flex-shrink-0">
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">{{ $c->topic }}</span>
                        <span class="text-xs text-gray-500">{{ count($c->collected_content_ids ?? []) }} posts · ~{{ $c->estimated_hours ?? 0 }}h</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $c->title }}</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $c->description }}</p>
                </div>
            </div>
        </a>
        @empty
        <p class="text-gray-600 dark:text-gray-400">No collections yet.</p>
        @endforelse
    </div>
</div>
@endsection
