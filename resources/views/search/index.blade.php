@extends('layouts.app')

@section('title', 'Search - Find articles, tutorials, and content')
@section('description', 'Search and filter articles by category, tags, engagement, read time, and more.')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">🔍 Search Intelligence</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Find Your Next Read</h1>
        <p class="mt-4 text-base text-slate-300">Discover the best articles, tutorials, and insights from our community. Filter by category, tags, engagement, and more.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900 min-h-screen">
    <div class="py-8">
        @livewire('advanced-search')
    </div>
</section>
@endsection
