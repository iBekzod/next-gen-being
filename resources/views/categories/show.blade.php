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
