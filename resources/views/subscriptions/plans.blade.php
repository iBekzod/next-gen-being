@extends('layouts.app')

@section('title', 'Pricing Plans - ' . setting('site_name'))
@section('description', 'Choose the plan that unlocks your momentum')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto text-center max-w-4xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Membership</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Unlock the full operating stack.</h1>
        <p class="mt-4 text-base text-slate-300">Pick the plan that matches your cadence. Every tier includes deep dives, templates, and drops engineered for compounding leverage.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-6xl sm:px-6 lg:px-8">
        @livewire('subscription-plans')
    </div>
</section>
@endsection
