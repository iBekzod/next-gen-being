@extends('layouts.app')

@section('title', 'Page not found · ' . setting('site_name', 'NextGenBeing'))
@section('description', 'The page you are looking for could not be found.')

@php
    $popularPosts = \Cache::remember('errors.404.popular', 3600, fn() =>
        \App\Models\Post::where('status', 'published')
            ->whereNotNull('slug')
            ->with(['category'])
            ->orderByDesc('views_count')
            ->limit(4)
            ->get()
    );
@endphp

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-20 mx-auto max-w-3xl text-center">
        <p class="text-7xl font-bold text-blue-400/20 select-none">404</p>
        <h1 class="mt-4 text-3xl sm:text-4xl font-bold tracking-tight">Lost in the codebase</h1>
        <p class="mt-4 text-lg text-slate-300">That URL doesn't exist or has moved. Here are some good places to land instead.</p>
        <div class="mt-8 flex flex-wrap gap-3 justify-center text-sm">
            <a href="{{ url('/') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 font-semibold">Home</a>
            <a href="{{ route('posts.index') }}" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 font-semibold">All articles</a>
            <a href="{{ route('tutorials.index') }}" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 font-semibold">Tutorials</a>
            <a href="{{ route('authors.index') }}" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 font-semibold">Authors</a>
        </div>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900 py-12">
    <div class="px-6 mx-auto max-w-5xl">
        @if($popularPosts->count() > 0)
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Or try one of our most-read articles</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($popularPosts as $post)
            <a href="{{ route('posts.show', $post->slug) }}" class="block p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                @if($post->category)
                <span class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">{{ $post->category->name }}</span>
                @endif
                <h3 class="mt-2 font-bold text-gray-900 dark:text-white line-clamp-2">{{ $post->title }}</h3>
                <p class="mt-2 text-xs text-gray-500">{{ $post->read_time }} min · {{ number_format($post->views_count) }} views</p>
            </a>
            @endforeach
        </div>
        @endif

        <div class="mt-12 p-6 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-slate-700 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Still looking for something specific?</p>
            <form action="{{ route('search') }}" method="GET" class="flex max-w-md mx-auto gap-2">
                <input type="text" name="q" placeholder="Search articles, topics, tags..."
                       class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white">
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">Search</button>
            </form>
        </div>
    </div>
</div>
@endsection
