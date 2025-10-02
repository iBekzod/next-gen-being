@extends('layouts.app')

@section('title', 'My Posts - Dashboard')
@section('description', 'Draft, publish, and refine your articles')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Publishing</span>
            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Manage your posts and insights.</h1>
            <p class="mt-4 text-base text-slate-300">Edit drafts, ship new drops, and track performance all from one command center.</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'content_manager', 'blogger']))
        <a href="{{ route('posts.create') }}" class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-slate-900 transition rounded-xl bg-white shadow-sm hover:-translate-y-0.5 hover:shadow-lg">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Post
        </a>
        @endif
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl">
        @livewire('user-dashboard', ['activeTab' => 'posts', 'postsFilter' => $filter])
    </div>
</section>
@endsection
