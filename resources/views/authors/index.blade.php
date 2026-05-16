@extends('layouts.app')

@section('title', 'Authors - NextGenBeing')
@section('description', 'Meet the engineers and writers behind NextGenBeing tutorials and deep dives.')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-6xl">
        <h1 class="text-4xl font-bold tracking-tight">Authors</h1>
        <p class="mt-4 text-lg text-slate-300">The engineers and writers behind NextGenBeing.</p>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900 py-16">
    <div class="px-6 mx-auto max-w-6xl">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($authors as $author)
            <a href="{{ route('authors.show', $author->slug) }}"
               class="block p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-lg transition">
                <div class="flex items-start gap-4">
                    <img src="{{ $author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=2563eb&color=fff&size=120' }}"
                         alt="{{ $author->name }}"
                         class="w-16 h-16 rounded-full object-cover flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <h2 class="font-bold text-gray-900 dark:text-white">{{ $author->name }}</h2>
                        @if($author->bio)
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $author->bio }}</p>
                        @endif
                        <p class="mt-3 text-xs text-gray-500">{{ $author->posts_count }} {{ \Illuminate\Support\Str::plural('article', $author->posts_count) }}</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
