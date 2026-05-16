@extends('layouts.app')

@section('title', $collection->title . ' · Tutorial Collection')
@section('description', \Illuminate\Support\Str::limit($collection->description, 160))

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-5xl">
        <a href="{{ route('tutorial-collections.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-blue-200 hover:text-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All collections
        </a>
        <div class="mt-6">
            <span class="px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full bg-purple-500/20 text-purple-200">{{ $collection->topic }}</span>
            <h1 class="mt-4 text-4xl font-bold tracking-tight">{{ $collection->title }}</h1>
            <p class="mt-4 text-lg text-slate-300">{{ $collection->description }}</p>
            <dl class="mt-6 flex flex-wrap gap-6 text-sm">
                <div><dt class="text-slate-400 uppercase text-xs">Posts</dt><dd class="font-semibold text-white">{{ $posts->count() }}</dd></div>
                <div><dt class="text-slate-400 uppercase text-xs">Estimated</dt><dd class="font-semibold text-white">~{{ $collection->estimated_hours ?? 0 }} hours</dd></div>
                <div><dt class="text-slate-400 uppercase text-xs">Skill level</dt><dd class="font-semibold text-white">{{ $collection->skill_level ?? 'intermediate' }}</dd></div>
            </dl>
        </div>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900 py-12">
    <div class="px-6 mx-auto max-w-5xl space-y-3">
        @foreach($posts as $i => $post)
        <a href="{{ route('posts.show', $post->slug) }}"
           class="block p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 font-bold text-sm">{{ $i + 1 }}</div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $post->title }}</h3>
                    @if($post->excerpt)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $post->clean_excerpt }}</p>
                    @endif
                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                        @if($post->category)<span>{{ $post->category->name }}</span><span>·</span>@endif
                        <span>{{ $post->read_time }} min read</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
