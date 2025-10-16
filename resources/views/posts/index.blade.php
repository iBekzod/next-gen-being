@extends('layouts.app')

@section('title', 'Library - ' . setting('site_name'))
@section('description', 'Browse every playbook, teardown, and workflow published on ' . setting('site_name'))

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Library</span>
                <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Curated intelligence for builders, operators, and creators.</h1>
                <p class="mt-4 text-base text-slate-300">Filter by category, explore premium drops, or search the archive to get exactly the blueprint you need.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 border border-white/10 text-slate-100">
                    <svg class="w-4 h-4 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M5 7h14M5 11h14M5 15h14M5 19h14"/></svg>
                    <span>Weekly drops, zero fluff</span>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 border border-white/10 text-slate-100">
                    <svg class="w-4 h-4 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                    <span>Read in under 10 minutes</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900">
    @livewire('post-list')
</div>
@endsection
