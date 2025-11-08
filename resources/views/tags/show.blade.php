@extends('layouts.app')

@section('title', $tag->getMetaTitle())
@section('description', $tag->getMetaDescription())
@section('keywords', $tag->getMetaKeywords())
@section('og_type', 'website')

@php
    $canonicalUrl = route('tags.show', $tag->slug);
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
        'name' => $tag->name,
        'item' => $canonicalUrl,
    ]
];

$tagPosts = $tag->publishedPosts()->latest()->limit(10)->get();
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

<!-- CollectionPage Schema for Tag -->
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $tag->name,
    'description' => 'Articles tagged with ' . $tag->name,
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
    ], $tagPosts->all()),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<!-- Tag Header -->
<section class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <span class="inline-block px-3 py-1 mb-4 text-sm font-semibold tracking-wide uppercase rounded-full bg-white/20">Tag</span>
        <h1 class="text-4xl font-bold">{{ $tag->name }}</h1>
        <p class="mt-4 text-lg text-emerald-100">{{ $tagPosts->count() }} {{ $tagPosts->count() === 1 ? 'article' : 'articles' }} tagged with <strong>#{{ $tag->name }}</strong></p>
    </div>
</section>

<!-- Posts by Tag -->
<section class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-slate-900">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($tagPosts as $post)
                <article class="bg-white dark:bg-slate-800 rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden">
                    @if($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @endif
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <a href="{{ route('categories.show', $post->category->slug) }}" class="inline-block px-2 py-1 text-xs font-semibold tracking-wide uppercase rounded bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-100 hover:bg-emerald-200 dark:hover:bg-emerald-800 transition-colors">
                                {{ $post->category->name }}
                            </a>
                            @if($post->read_time)
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $post->read_time }} min read</span>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">
                            <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                {{ $post->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($post->tags->take(3) as $postTag)
                                <a href="{{ route('tags.show', $postTag->slug) }}" class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-emerald-100 dark:hover:bg-emerald-900 transition-colors">
                                    #{{ $postTag->name }}
                                </a>
                            @endforeach
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ optional($post->published_at)->format('M d, Y') }}
                            </div>
                            <a href="{{ route('posts.show', $post->slug) }}" class="inline-flex items-center text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 font-semibold transition-colors">
                                Read more →
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-600 dark:text-gray-400 text-lg">No articles found with this tag yet.</p>
                </div>
            @endforelse
        </div>

        <!-- View All Button -->
        @if($tagPosts->count() > 0)
            <div class="text-center mt-12">
                <a href="{{ route('posts.index') }}?tag={{ $tag->slug }}" class="inline-flex items-center px-8 py-3 text-lg font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                    View All #{{ $tag->name }} Articles
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</section>

<!-- Related Tags -->
<section class="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-slate-800">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">Related Tags</h2>
        <div class="flex flex-wrap gap-3">
            @forelse(\App\Models\Tag::active()->popular(20)->where('id', '!=', $tag->id)->get() as $relatedTag)
                <a href="{{ route('tags.show', $relatedTag->slug) }}" class="inline-block px-4 py-2 rounded-full border-2 border-gray-200 dark:border-slate-700 hover:border-emerald-600 dark:hover:border-emerald-400 text-gray-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors font-semibold">
                    #{{ $relatedTag->name }}
                </a>
            @empty
            @endforelse
        </div>
    </div>
</section>
@endsection
