@extends('layouts.app')

@section('title', 'My Bookmarks - Dashboard')
@section('description', 'Review the articles you saved for a deep dive')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Reading queue</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Your saved articles, ready when you are.</h1>
        <p class="mt-4 text-base text-slate-300 max-w-2xl">Revisit the plays, breakdowns, and frameworks you bookmarked to stay in flow.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        @livewire('user-dashboard', ['activeTab' => 'bookmarks'])
    </div>
</section>
@endsection
