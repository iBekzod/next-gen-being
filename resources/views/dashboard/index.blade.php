@extends('layouts.app')

@section('title', 'Dashboard - ' . setting('site_name'))
@section('description', 'Manage your account, posts, and settings')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Control center</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Welcome back, {{ auth()->user()->name }}.</h1>
        <p class="mt-4 text-base text-slate-300 max-w-2xl">Review your recent activity, update your workspace, and keep your publishing cadence on track.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @livewire('user-dashboard')
    </div>
</section>
@endsection
