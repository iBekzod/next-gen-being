@extends('layouts.app')

@section('title', 'Search - ' . setting('site_name'))
@section('description', 'Search across playbooks, tool reviews, and makers on ' . setting('site_name'))
@section('canonical', route('search'))
@section('share_image', setting('default_meta_image', setting('site_logo', asset('uploads/logo.png'))))
@section('robots', 'noindex, nofollow')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">
                Search the intelligence stack
            </span>
            <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Find the frameworks, tools, and operators you need.</h1>
            <p class="mt-4 text-base text-slate-300">Use the universal search to surface deep dives, high-signal comparisons, and the makers pushing the frontier forward.</p>
        </div>
    </div>
</section>

<section class="py-12 bg-gray-50 dark:bg-slate-900">
    <div class="px-6 mx-auto max-w-6xl">
        @livewire('search-results', ['query' => $query, 'searchType' => $searchType])
    </div>
</section>
@endsection
