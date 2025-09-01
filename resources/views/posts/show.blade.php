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
<div class="min-h-screen bg-white dark:bg-gray-900">
    @livewire('post-show', ['post' => $post])
</div>
@endsection
