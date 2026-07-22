@extends('layouts.app')

@section('title', 'AI Writing Studio · ' . config('app.name'))
@section('description', 'Turn a topic into a polished, SEO-ready article — with images — in minutes. Bring your own keys free, or let us handle it.')

@section('content')
<div class="bg-white dark:bg-slate-950">

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-50 to-white dark:from-slate-900 dark:to-slate-950">
        <div class="max-w-5xl mx-auto px-6 py-20 text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1 mb-6 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/40 dark:text-blue-300">
                ✨ AI Writing Studio
            </span>
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                Publish-ready content,<br class="hidden sm:block"> generated in minutes
            </h1>
            <p class="max-w-2xl mx-auto mt-6 text-lg text-gray-600 dark:text-gray-300">
                Turn a topic into a polished, SEO-ready article — complete with images — right inside {{ config('app.name') }}.
                Start free with your own API keys, or let us run the whole engine for you.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 mt-8">
                @auth
                    <a href="#pricing" class="px-6 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">See plans</a>
                    <a href="{{ url('/blogger/ai-settings') }}" class="px-6 py-3 font-semibold text-gray-700 bg-gray-100 rounded-lg dark:bg-slate-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-slate-700 transition">Open the studio</a>
                @else
                    <a href="{{ route('register') }}" class="px-6 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Start free</a>
                    <a href="#pricing" class="px-6 py-3 font-semibold text-gray-700 bg-gray-100 rounded-lg dark:bg-slate-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-slate-700 transition">See plans</a>
                @endauth
            </div>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No credit card to start · 5 free posts every month</p>
        </div>
    </section>

    {{-- How it works --}}
    <section class="max-w-5xl mx-auto px-6 py-16">
        <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white">From idea to published in three steps</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
            @foreach([
                ['1', 'Pick a topic', 'Give it a title or a rough idea. Set the depth, tone, and length.'],
                ['2', 'AI drafts it', 'Get a structured, SEO-ready article with headings, code, and a matching image.'],
                ['3', 'Review & publish', 'Edit anything, then publish to your blog — or export it. You stay in control.'],
            ] as [$n, $title, $body])
            <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-slate-900 dark:border-slate-700">
                <div class="flex items-center justify-center w-10 h-10 mb-4 font-bold text-white bg-blue-600 rounded-full">{{ $n }}</div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $body }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Features --}}
    <section class="bg-gray-50 dark:bg-slate-900/50">
        <div class="max-w-5xl mx-auto px-6 py-16">
            <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white">Everything you need to publish consistently</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-10">
                @foreach([
                    ['📝', 'Long-form articles', 'Structured, on-topic drafts — not thin autocomplete.'],
                    ['🖼️', 'Matching images', 'Auto-generated or sourced visuals for every post.'],
                    ['🔍', 'SEO-ready', 'Headings, meta, and internal structure built in.'],
                    ['🔑', 'Bring your own keys', 'Start free using your own Groq / Unsplash keys.'],
                    ['⚡', 'GPT-4 + DALL·E 3', 'Premium plans unlock the strongest models.'],
                    ['🎛️', 'You stay in control', 'Every draft is editable before it goes live.'],
                ] as [$icon, $title, $body])
                <div class="flex gap-4">
                    <div class="text-2xl">{{ $icon }}</div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $body }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Pricing --}}
    <section id="pricing" class="max-w-6xl mx-auto px-6 py-20">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Simple, honest pricing</h2>
            <p class="mt-3 text-gray-600 dark:text-gray-400">Start free. Upgrade only when you're publishing more.</p>
        </div>

        @if(session('error'))
        <div class="max-w-xl mx-auto mt-6 p-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:text-red-300 dark:border-red-800">
            {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
            @foreach($tiers as $tier)
            @php $popular = $tier['key'] === 'premium'; @endphp
            <div class="relative flex flex-col p-6 bg-white rounded-2xl border transition
                        {{ $popular ? 'border-blue-500 shadow-xl dark:bg-slate-800' : 'border-gray-200 shadow-sm dark:bg-slate-900 dark:border-slate-700' }}">
                @if($popular)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 text-xs font-semibold text-white bg-blue-600 rounded-full">Most popular</span>
                @endif

                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $tier['name'] }}</h3>
                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-4xl font-extrabold text-gray-900 dark:text-white">${{ rtrim(rtrim(number_format($tier['price'], 2), '0'), '.') }}</span>
                    @if($tier['price'] > 0)<span class="text-sm text-gray-500 dark:text-gray-400">/mo</span>@endif
                </div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $tier['posts_label'] }} · {{ $tier['images_label'] }}</p>

                <ul class="mt-6 space-y-3 flex-1">
                    @foreach($tier['features'] as $feature)
                    <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>

                <div class="mt-8">
                    @if($tier['key'] === 'free')
                        @auth
                            <span class="block w-full py-3 text-center font-semibold text-gray-500 bg-gray-100 rounded-lg dark:bg-slate-800 dark:text-gray-400">Included free</span>
                        @else
                            <a href="{{ route('register') }}" class="block w-full py-3 text-center font-semibold text-white bg-gray-800 rounded-lg hover:bg-gray-900 dark:bg-slate-700 dark:hover:bg-slate-600 transition">Start free</a>
                        @endauth
                    @else
                        @auth
                            <form action="{{ route('ai-writer.upgrade', $tier['key']) }}" method="POST">
                                @csrf
                                <button type="submit" class="block w-full py-3 text-center font-semibold text-white rounded-lg transition {{ $popular ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-800 hover:bg-gray-900 dark:bg-slate-700 dark:hover:bg-slate-600' }}">
                                    Upgrade to {{ $tier['name'] }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('register') }}" class="block w-full py-3 text-center font-semibold text-white rounded-lg transition {{ $popular ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-800 hover:bg-gray-900 dark:bg-slate-700 dark:hover:bg-slate-600' }}">
                                Get {{ $tier['name'] }}
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <p class="mt-8 text-sm text-center text-gray-500 dark:text-gray-400">Cancel anytime · Secure checkout via Lemon Squeezy · Quotas reset monthly</p>
    </section>

    {{-- FAQ --}}
    <section class="bg-gray-50 dark:bg-slate-900/50">
        <div class="max-w-3xl mx-auto px-6 py-16">
            <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white">Questions</h2>
            <div class="mt-8 space-y-4">
                @foreach([
                    ['Is the free plan really free?', 'Yes — 5 posts and 10 images every month using your own Groq and Unsplash API keys. No card required.'],
                    ['What do paid plans add?', 'Higher (or unlimited) monthly quotas, and on Premium+ we run stronger models (GPT-4, DALL·E 3) so you don\'t need your own keys.'],
                    ['Do I own what I generate?', 'Yes. Every draft is yours to edit, publish, or export. The AI drafts; you decide what ships.'],
                    ['Can I cancel?', 'Anytime, from your account. Your plan stays active until the end of the billing period.'],
                ] as [$q, $a])
                <details class="p-5 bg-white border border-gray-200 rounded-xl dark:bg-slate-900 dark:border-slate-700">
                    <summary class="font-semibold text-gray-900 cursor-pointer dark:text-white">{{ $q }}</summary>
                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ $a }}</p>
                </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="max-w-4xl mx-auto px-6 py-20 text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Start publishing today</h2>
        <p class="mt-3 text-gray-600 dark:text-gray-400">Five free posts a month. No card required.</p>
        <div class="mt-8">
            @auth
                <a href="{{ url('/blogger/ai-settings') }}" class="inline-block px-8 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Open the studio</a>
            @else
                <a href="{{ route('register') }}" class="inline-block px-8 py-3 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Create your free account</a>
            @endauth
        </div>
    </section>

</div>
@endsection
