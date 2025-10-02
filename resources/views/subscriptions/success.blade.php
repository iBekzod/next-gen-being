@extends('layouts.app')

@section('title', 'Subscription Successful - ' . setting('site_name'))
@section('description', 'Thank you for subscribing!')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto text-center max-w-3xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-emerald-500/20 text-emerald-200">Success</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Welcome to premium intelligence.</h1>
        <p class="mt-4 text-base text-slate-300">Your subscription is active. You now have unrestricted access to every deep dive, drop, and framework.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-3xl">
        <div class="p-8 text-center bg-white border border-slate-200 rounded-3xl shadow-xl dark:bg-slate-900/70 dark:border-slate-700">
            <div class="flex justify-center mb-6">
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Here is what is unlocked for you</h2>
            <ul class="mt-6 space-y-4 text-left">
                <li class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-6 h-6 text-emerald-500 bg-emerald-100 rounded-full dark:bg-emerald-900/40"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                    <span class="text-sm text-slate-600 dark:text-slate-300">Unlimited access to premium briefings and breakdowns</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-6 h-6 text-emerald-500 bg-emerald-100 rounded-full dark:bg-emerald-900/40"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                    <span class="text-sm text-slate-600 dark:text-slate-300">Early access to new drops, prompts, and playbooks</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-6 h-6 text-emerald-500 bg-emerald-100 rounded-full dark:bg-emerald-900/40"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                    <span class="text-sm text-slate-600 dark:text-slate-300">Downloadable templates and systems snapshots</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-6 h-6 text-emerald-500 bg-emerald-100 rounded-full dark:bg-emerald-900/40"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                    <span class="text-sm text-slate-600 dark:text-slate-300">Priority assistance from the NextGenBeing team</span>
                </li>
            </ul>
            <div class="flex flex-col gap-4 mt-10 sm:flex-row sm:justify-center">
                <a href="{{ route('posts.index') }}" class="inline-flex items-center justify-center px-5 py-3 text-sm font-semibold text-white transition rounded-xl bg-blue-600 hover:bg-blue-700">
                    Dive into the library
                </a>
                <a href="{{ route('subscription.manage') }}" class="inline-flex items-center justify-center px-5 py-3 text-sm font-semibold text-slate-700 transition border rounded-xl border-slate-200 hover:bg-slate-100 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-800">
                    Manage subscription
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

