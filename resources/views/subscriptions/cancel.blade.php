@extends('layouts.app')

@section('title', 'Subscription Cancelled - ' . setting('site_name'))
@section('description', 'Your subscription process was cancelled')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto text-center max-w-3xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-amber-500/20 text-amber-200">Heads up</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Subscription not completed.</h1>
        <p class="mt-4 text-base text-slate-300">No worries, nothing was charged. Explore more articles, or restart your membership when the timing aligns.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-3xl">
        <div class="p-8 text-center bg-white border border-slate-200 rounded-3xl shadow-xl dark:bg-slate-900/70 dark:border-slate-700">
            <div class="flex justify-center mb-6">
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900/40">
                    <svg class="w-8 h-8 text-amber-600 dark:text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M12 5a7 7 0 00-7 7v2a3 3 0 003 3h8a3 3 0 003-3v-2a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Stay close to the signal</h2>
            <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">You still have access to our free briefings, saved content, and community discussions. Premium workflows will be here when you are ready.</p>
            <ul class="mt-6 space-y-3 text-left">
                <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300"><svg class="flex-shrink-0 w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Keep exploring free insights and teardown archives</li>
                <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300"><svg class="flex-shrink-0 w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Bookmark what matters and build your queue</li>
                <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300"><svg class="flex-shrink-0 w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Join the conversation with fellow operators</li>
            </ul>
            <div class="flex flex-col gap-4 mt-10 sm:flex-row sm:justify-center">
                <a href="{{ route('posts.index') }}" class="inline-flex items-center justify-center px-5 py-3 text-sm font-semibold text-white transition rounded-xl bg-blue-600 hover:bg-blue-700">Browse free content</a>
                <a href="{{ route('subscription.plans') }}" class="inline-flex items-center justify-center px-5 py-3 text-sm font-semibold text-slate-700 transition border rounded-xl border-slate-200 hover:bg-slate-100 dark:text-slate-200 dark:border-slate-600 dark:hover:bg-slate-800">View plans again</a>
            </div>
            <p class="mt-8 text-xs text-slate-500 dark:text-slate-400">Ready later? Restart your subscription from the pricing page anytime.</p>
        </div>
    </div>
</section>
@endsection

