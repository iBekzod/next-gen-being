@extends('layouts.app')

@section('title', $post->title . ' - ' . setting('site_name'))
@section('description', $post->excerpt)
@section('keywords', $post->tags->pluck('name')->implode(', '))
@section('og_type', 'article')

@push('head')
<meta property="og:title" content="{{ $post->title }}">
<meta property="og:description" content="{{ $post->excerpt }}">
@if($post->featured_image)
<meta property="og:image" content="{{ url($post->featured_image) }}">
@endif
<meta property="article:author" content="{{ $post->author->name }}">
<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
<meta property="article:section" content="{{ $post->category->name }}">
@foreach($post->tags as $tag)
<meta property="article:tag" content="{{ $tag->name }}">
@endforeach

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $post->title }}">
<meta name="twitter:description" content="{{ $post->excerpt }}">
@if($post->featured_image)
<meta name="twitter:image" content="{{ url($post->featured_image) }}">
@endif

<link rel="canonical" href="{{ route('posts.show', $post->slug) }}">
@endpush

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-6xl">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-blue-200 hover:text-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to discoveries
        </a>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl max-w-3xl">{{ $post->title }}</h1>
        <p class="mt-4 text-base text-slate-300 max-w-2xl">{{ $post->excerpt }}</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-4 py-12 mx-auto max-w-6xl sm:px-6 lg:px-8">
        @livewire('post-show', ['post' => $post])
    </div>
</section>
@endsection
