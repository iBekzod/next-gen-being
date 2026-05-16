@extends('layouts.app')

@section('title', $author->name . ' - NextGenBeing')
@section('description', \Illuminate\Support\Str::limit($author->bio ?? 'Articles by ' . $author->name, 160))

@push('head')
<meta property="og:type" content="profile">
<meta property="og:title" content="{{ $author->name }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit($author->bio, 160) }}">
<meta property="og:image" content="{{ $author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=2563eb&color=fff&size=400' }}">
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $author->name,
    'description' => $author->bio,
    'image' => $author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=2563eb&color=fff&size=400',
    'url' => route('authors.show', $author->slug),
    'sameAs' => array_values(array_filter([$author->linkedin, $author->twitter])),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-5xl">
        <a href="{{ route('authors.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-blue-200 hover:text-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All authors
        </a>

        <div class="mt-6 flex flex-col md:flex-row gap-8 items-start">
            <img src="{{ $author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=2563eb&color=fff&size=240' }}"
                 alt="{{ $author->name }}"
                 class="w-32 h-32 md:w-40 md:h-40 rounded-full ring-4 ring-blue-500/30 object-cover">
            <div class="flex-1">
                <h1 class="text-4xl font-bold tracking-tight">{{ $author->name }}</h1>
                @if($author->bio)
                <p class="mt-4 text-lg text-slate-300 leading-relaxed">{{ $author->bio }}</p>
                @endif

                <div class="mt-6 flex flex-wrap gap-4 text-sm">
                    @if($author->linkedin)
                    <a href="{{ $author->linkedin }}" target="_blank" rel="noopener author"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.063 2.063 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        LinkedIn
                    </a>
                    @endif
                    @if($author->twitter)
                    <a href="{{ $author->twitter }}" target="_blank" rel="noopener author"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297a12 12 0 100 24 12 12 0 000-24zm5.568 8.16c-.169 3.6-2.733 8.4-7.768 8.4-1.55 0-2.992-.45-4.2-1.224a5.487 5.487 0 004.062-1.13 2.74 2.74 0 01-2.558-1.9 2.83 2.83 0 001.24-.045 2.74 2.74 0 01-2.195-2.685v-.034a2.72 2.72 0 001.24.343 2.738 2.738 0 01-.847-3.654 7.77 7.77 0 005.638 2.857 2.74 2.74 0 014.668-2.497 5.5 5.5 0 001.737-.664 2.747 2.747 0 01-1.204 1.514 5.475 5.475 0 001.572-.43 5.563 5.563 0 01-1.365 1.414z"/></svg>
                        More work
                    </a>
                    @endif
                </div>

                <dl class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-white/5">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Articles</dt>
                        <dd class="mt-1 text-2xl font-bold">{{ number_format($stats['total_posts']) }}</dd>
                    </div>
                    <div class="p-4 rounded-xl bg-white/5">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Views</dt>
                        <dd class="mt-1 text-2xl font-bold">{{ number_format($stats['total_views']) }}</dd>
                    </div>
                    <div class="p-4 rounded-xl bg-white/5">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Likes</dt>
                        <dd class="mt-1 text-2xl font-bold">{{ number_format($stats['total_likes']) }}</dd>
                    </div>
                    <div class="p-4 rounded-xl bg-white/5">
                        <dt class="text-xs uppercase tracking-wide text-slate-400">Member since</dt>
                        <dd class="mt-1 text-lg font-bold">{{ $stats['member_since'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900 py-16">
    <div class="px-6 mx-auto max-w-6xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Articles by {{ $author->name }}</h2>
            @if($categories->count() > 1)
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('authors.show', $author->slug) }}"
                   class="px-3 py-1 text-sm rounded-full {{ !$activeCategory ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">All</a>
                @foreach($categories as $cat)
                <a href="{{ route('authors.show', ['slug' => $author->slug, 'category' => $cat->slug]) }}"
                   class="px-3 py-1 text-sm rounded-full {{ $activeCategory === $cat->slug ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            @endif
        </div>

        @if($posts->count() === 0)
        <p class="text-gray-600 dark:text-gray-400">No articles yet in this category.</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
            <article class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 hover:shadow-lg transition">
                @if($post->featured_image)
                <a href="{{ route('posts.show', $post->slug) }}">
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover" loading="lazy">
                </a>
                @endif
                <div class="p-5">
                    @if($post->category)
                    <a href="{{ route('posts.show', $post->slug) }}" class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">{{ $post->category->name }}</a>
                    @endif
                    <h3 class="mt-2 text-lg font-bold text-gray-900 dark:text-white leading-tight">
                        <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $post->title }}</a>
                    </h3>
                    @if($post->excerpt)
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $post->excerpt }}</p>
                    @endif
                    <div class="mt-4 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <time>{{ $post->published_at?->format('M j, Y') }}</time>
                        <span>·</span>
                        <span>{{ $post->read_time }} min read</span>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
