@extends('layouts.app')

@section('title', $category->getMetaTitle())
@section('description', $category->getMetaDescription())
@section('keywords', $category->getMetaKeywords())
@section('og_type', 'website')

@php
    $canonicalUrl = route('categories.show', $category->slug);
    $defaultImage = setting('default_meta_image', setting('site_logo', asset('uploads/logo.png')));
@endphp

@section('canonical', $canonicalUrl)
@section('share_image', $defaultImage)

@push('head')
<meta property="og:url" content="{{ $canonicalUrl }}">
@endpush

@php
// Build breadcrumb items
$breadcrumbItems = [
    [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => route('home'),
    ],
    [
        '@type' => 'ListItem',
        'position' => 2,
        'name' => 'Articles',
        'item' => route('posts.index'),
    ],
    [
        '@type' => 'ListItem',
        'position' => 3,
        'name' => $category->name,
        'item' => $canonicalUrl,
    ]
];

$categoryPosts = $category->publishedPosts()->limit(10)->get();
@endphp

@push('structured-data')
<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbItems,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

<!-- CollectionPage Schema for Category -->
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $category->name,
    'description' => $category->description ?? $category->name . ' articles',
    'url' => $canonicalUrl,
    'mainEntity' => array_map(fn($post) => [
        '@type' => 'Article',
        'headline' => $post->title,
        'description' => $post->excerpt,
        'url' => route('posts.show', $post->slug),
        'image' => $post->featured_image ?? setting('default_meta_image'),
        'datePublished' => optional($post->published_at)->toIso8601String(),
        'dateModified' => optional($post->updated_at)->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $post->author->name,
        ],
    ], $categoryPosts->all()),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<!-- Category Header -->
<section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-4">
            @if($category->icon)
                <div class="text-4xl">{{ $category->icon }}</div>
            @endif
            <div class="flex-1">
                <span class="inline-block px-3 py-1 mb-4 text-sm font-semibold tracking-wide uppercase rounded-full bg-white/20">Category</span>
                <h1 class="text-4xl font-bold">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="mt-2 text-lg text-indigo-100">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Posts by Category -->
<section class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-slate-900">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($categoryPosts as $post)
                <article class="bg-white dark:bg-slate-800 rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
                    @if($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-block px-2 py-1 text-xs font-semibold tracking-wide uppercase rounded bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-100">
                                {{ $post->category->name }}
                            </span>
                            @if($post->read_time)
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $post->read_time }} min read</span>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">
                            <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ optional($post->published_at)->format('M d, Y') }}
                            </div>
                            <a href="{{ route('posts.show', $post->slug) }}" class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-semibold transition-colors">
                                Read more →
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-600 dark:text-gray-400 text-lg">No articles found in this category yet.</p>
                </div>
            @endforelse
        </div>

        <!-- View All Button -->
        @if($categoryPosts->count() > 0)
            <div class="text-center mt-12">
                <a href="{{ route('posts.index') }}?cat={{ $category->slug }}" class="inline-flex items-center px-8 py-3 text-lg font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                    View All {{ $category->name }} Articles
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</section>

<!-- Top authors + popular tags in this category -->
@php
    $topAuthors = \App\Models\User::whereHas('posts', fn($q) => $q->where('category_id', $category->id)->where('status','published'))
        ->withCount(['posts' => fn($q) => $q->where('category_id', $category->id)->where('status','published')])
        ->orderByDesc('posts_count')
        ->limit(4)
        ->get();

    $popularTags = \Illuminate\Support\Facades\DB::table('tags')
        ->join('post_tags', 'tags.id', '=', 'post_tags.tag_id')
        ->join('posts', 'post_tags.post_id', '=', 'posts.id')
        ->where('posts.category_id', $category->id)
        ->where('posts.status', 'published')
        ->select('tags.id', 'tags.name', 'tags.slug')
        ->selectRaw('COUNT(*) as post_count')
        ->groupBy('tags.id', 'tags.name', 'tags.slug')
        ->orderByDesc('post_count')
        ->limit(10)
        ->get();
@endphp
@if($topAuthors->isNotEmpty() || $popularTags->isNotEmpty())
<section class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-100 dark:bg-slate-900/50">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8">
        @if($topAuthors->isNotEmpty())
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Top authors in {{ $category->name }}</h2>
            <div class="space-y-3">
                @foreach($topAuthors as $author)
                <a href="{{ $author->slug ? route('authors.show', $author->slug) : '#' }}" class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
                    <img src="{{ $author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($author->name) . '&background=2563eb&color=fff' }}" alt="{{ $author->name }}" class="w-10 h-10 rounded-full" loading="lazy">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $author->name }}</p>
                        <p class="text-xs text-gray-500">{{ $author->posts_count }} {{ \Illuminate\Support\Str::plural('article', $author->posts_count) }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($popularTags->isNotEmpty())
        <div>
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Popular tags in {{ $category->name }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($popularTags as $tag)
                <a href="{{ route('tags.show', $tag->slug) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-sm text-gray-700 dark:text-gray-300 hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    <span>#{{ $tag->name }}</span>
                    <span class="text-xs text-gray-400">{{ $tag->post_count }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endif

<!-- Related Categories -->
<section class="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-slate-800">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Explore Other Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse(\App\Models\Category::active()->ordered()->where('id', '!=', $category->id)->limit(8)->get() as $relatedCategory)
                <a href="{{ route('categories.show', $relatedCategory->slug) }}" class="p-6 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-indigo-600 dark:hover:border-indigo-400 transition-colors text-center">
                    @if($relatedCategory->icon)
                        <div class="text-3xl mb-2">{{ $relatedCategory->icon }}</div>
                    @endif
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $relatedCategory->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $relatedCategory->publishedPosts()->count() }} articles</p>
                </a>
            @empty
            @endforelse
        </div>
    </div>
</section>
@endsection
