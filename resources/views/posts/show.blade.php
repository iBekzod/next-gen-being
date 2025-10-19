@extends('layouts.app')

@section('title', $post->title . ' - ' . setting('site_name'))
@section('description', $post->excerpt)
@section('keywords', $post->tags->pluck('name')->implode(', '))
@section('og_type', 'article')

@php
    $shareImage = $post->featured_image ? url($post->featured_image) : setting('default_meta_image', setting('site_logo', asset('uploads/logo.png')));
    $logoPath = setting('site_logo', asset('uploads/logo.png'));
    $logoUrl = preg_match('/^https?:\/\//i', $logoPath) ? $logoPath : url($logoPath);
    $siteName = setting('site_name', config('app.name'));
    $companyName = setting('company_name', $siteName);
    $publishDate = optional($post->published_at)->toAtomString();
    $modifiedDate = optional($post->updated_at)->toAtomString();
    $wordCount = str_word_count(strip_tags($post->content));
    $tagNames = $post->tags->pluck('name')->filter()->values()->all();
    $articleSection = optional($post->category)->name;
@endphp

@section('canonical', route('posts.show', $post->slug))
@section('share_image', $shareImage)
@section('author', $post->author->name)
@section('robots', $post->is_premium ? 'index, follow, noarchive' : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')


@push('head')
<meta property="article:author" content="{{ $post->author->name }}">
<meta property="article:published_time" content="{{ optional($post->published_at)->toIso8601String() }}">
<meta property="article:modified_time" content="{{ optional($post->updated_at)->toIso8601String() }}">
<meta property="article:section" content="{{ $articleSection }}">
@foreach($post->tags as $tag)
<meta property="article:tag" content="{{ $tag->name }}">
@endforeach
@endpush

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post->title,
    'description' => $post->excerpt,
    'image' => [$shareImage],
    'wordCount' => $wordCount,
    'author' => [
        '@type' => 'Person',
        'name' => $post->author->name,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => $companyName,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
        ],
    ],
    'datePublished' => $publishDate,
    'dateModified' => $modifiedDate ?: $publishDate,
    'isAccessibleForFree' => ! $post->is_premium,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('posts.show', $post->slug),
    ],
    'articleSection' => $articleSection,
    'keywords' => $tagNames,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-6xl">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-blue-200 hover:text-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to discoveries
        </a>

        @if($post->isPartOfSeries())
        <div class="inline-flex items-center gap-2 px-3 py-1 mt-4 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
            </svg>
            Part {{ $post->series_part }} of {{ $post->series_total_parts }}
        </div>
        @endif

        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl max-w-3xl">{{ $post->title }}</h1>
        <p class="mt-4 text-base text-slate-300 max-w-2xl">{{ $post->excerpt }}</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-4 py-12 mx-auto max-w-6xl sm:px-6 lg:px-8">
        <!-- Series Navigation -->
        <x-post.series-navigation :post="$post" />

        @livewire('post-show', ['post' => $post])
    </div>
</section>
@endsection
