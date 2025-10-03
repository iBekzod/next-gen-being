@extends('layouts.app')

@section('title', 'NextGenBeing - Explore the tech that evolves you')
@section('description', 'Curated insights, tool breakdowns, and operating frameworks for ambitious builders and creators.')

@section('content')
<div x-data="{
        showSubscribeModal: {{ $errors->any() ? 'true' : 'false' }},
        openModal() { this.showSubscribeModal = true; },
        closeModal() { this.showSubscribeModal = false; }
    }" x-on:keydown.escape.window="closeModal()">
    <section id="product-overview" class="relative overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,_#3b82f6_0,_transparent_45%)]"></div>
        <div class="relative px-6 pt-20 pb-24 mx-auto max-w-7xl lg:flex lg:items-center lg:gap-16">
            <div class="max-w-2xl">
                <div class="inline-flex items-center px-3 py-1 mb-6 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200 ring-1 ring-inset ring-blue-400/60">
                    Level up faster with faceless, high-signal reviews
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                    Explore the tech that evolves the way you think, work, and live.
                </h1>
                <p class="mt-6 text-lg leading-8 text-slate-300">
                    NextGenBeing surfaces the workflows, AI tools, and systems that top performers quietly rely on.
                    Each drop distills hours of research into actionable playbooks you can apply today.
                </p>

                @if(session('success'))
                    <div class="flex items-start gap-3 p-4 mt-8 text-sm font-medium text-emerald-100 border border-emerald-500/40 rounded-xl bg-emerald-500/10">
                        <span class="inline-flex items-center justify-center flex-shrink-0 w-6 h-6 text-emerald-200 bg-emerald-500/30 rounded-full">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span class="leading-6">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-4 mt-10">
                    <button type="button" @click="openModal()" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold transition rounded-xl bg-blue-500 hover:bg-blue-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500">
                        Get the Drops
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @if(setting('enable_subscriptions'))
                    <a href="{{ route('subscription.plans') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold transition border rounded-xl border-white/20 text-slate-200 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white/40">
                        Unlock premium intelligence
                    </a>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3 mt-6 text-xs text-slate-400">
                    <span class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> No spam. Cancel any time.</span>
                    <span class="hidden w-px h-4 bg-white/10 sm:block"></span>
                    <span>Delivered every Tuesday.</span>
                </div>
            </div>
            <div class="relative mt-16 lg:mt-0">
                <div class="absolute inset-y-0 -left-24 hidden w-px bg-gradient-to-b from-transparent via-blue-500/60 to-transparent lg:block"></div>
                <div class="relative p-6 bg-white/5 border border-white/10 rounded-3xl shadow-xl backdrop-blur">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-sm font-semibold text-blue-200">This week's drop</span>
                        <span class="text-xs text-slate-400">Issue {{ now()->format('W') }}</span>
                    </div>
                    <div class="space-y-5">
                        <div class="p-5 rounded-2xl bg-slate-900/50 border border-white/10">
                            <p class="text-xs font-semibold tracking-wide text-blue-300 uppercase">Capability Stack</p>
                            <h3 class="mt-2 text-lg font-semibold">Build a personal AI research analyst</h3>
                            <p class="mt-2 text-sm text-slate-300">Prompt frameworks, toolchain wiring, and review tactics to turn raw knowledge into decisions in minutes.</p>
                        </div>
                        <div class="p-5 rounded-2xl bg-slate-900/50 border border-white/10">
                            <p class="text-xs font-semibold tracking-wide text-orange-300 uppercase">Operator Playbook</p>
                            <h3 class="mt-2 text-lg font-semibold">A zero-lag weekly reset</h3>
                            <p class="mt-2 text-sm text-slate-300">The Monday ritual combining Notion, Cron, and Readwise to compress inputs and plan the week in 30 minutes.</p>
                        </div>
                        <div class="flex items-center justify-between p-4 text-sm rounded-xl bg-slate-900/40 border border-white/5">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 text-blue-200 bg-blue-500/20 rounded-full">98%</span>
                                <div>
                                    <p class="font-medium">Readers recommend the drop</p>
                                    <p class="text-xs text-slate-400">Rolling 90 day satisfaction score</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">3,127</p>
                                <p class="text-xs text-slate-400">Operators subscribed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-white dark:bg-slate-950">
        <div class="px-6 mx-auto max-w-7xl">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold tracking-wide text-blue-600 uppercase">Why readers stay</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">Signal-rich briefings, shipped weekly.</h2>
                <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">
                    Each edition packages deep research, vetted frameworks, and tested playbooks into a frictionless read.
                    Drop in for the intelligence you need, skip the noise.
                </p>
            </div>
            <div class="grid gap-8 mt-12 md:grid-cols-2 lg:grid-cols-3">
                <div class="p-6 transition bg-white rounded-2xl border border-slate-200/60 shadow-sm hover:-translate-y-1 hover:shadow-lg dark:bg-slate-900/60 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-10 h-10 text-blue-600 bg-blue-100 rounded-xl">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">Curated in the open</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Transparent breakdowns of the stack we use to build, test, and launch products faster.</p>
                </div>
                <div class="p-6 transition bg-white rounded-2xl border border-slate-200/60 shadow-sm hover:-translate-y-1 hover:shadow-lg dark:bg-slate-900/60 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-10 h-10 text-purple-600 bg-purple-100 rounded-xl">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5S13.657 14 12 14s-3 .672-3 1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v1m0 12v1m7-7h1M4 12H3m15.364 5.364l.707.707M5.929 6.343l-.707-.707m12.142 0l.707-.707M5.929 17.657l-.707.707"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">Actionable, not theoretical</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Immediate prompts, automations, and agendas you can drop into your operating system within minutes.</p>
                </div>
                <div class="p-6 transition bg-white rounded-2xl border border-slate-200/60 shadow-sm hover:-translate-y-1 hover:shadow-lg dark:bg-slate-900/60 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-10 h-10 text-emerald-600 bg-emerald-100 rounded-xl">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">Designed for momentum</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Save hours of scattered research and unlock a cadence that keeps your team compounding.</p>
                </div>
                <div class="p-6 transition bg-white rounded-2xl border border-slate-200/60 shadow-sm hover:-translate-y-1 hover:shadow-lg dark:bg-slate-900/60 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-10 h-10 text-amber-600 bg-amber-100 rounded-xl">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/><circle cx="12" cy="12" r="9" stroke-width="2"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">Time to value in minutes</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Read in under ten minutes and ship upgrades the same day.</p>
                </div>
                <div class="p-6 transition bg-white rounded-2xl border border-slate-200/60 shadow-sm hover:-translate-y-1 hover:shadow-lg dark:bg-slate-900/60 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-10 h-10 text-rose-600 bg-rose-100 rounded-xl">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-3-3H8a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v6a2 2 0 01-2 2h-3l-3 3z"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">Community sourced insight</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Powered by founders, analysts, and systems thinkers behind breakout products.</p>
                </div>
                <div class="p-6 transition bg-white rounded-2xl border border-slate-200/60 shadow-sm hover:-translate-y-1 hover:shadow-lg dark:bg-slate-900/60 dark:border-slate-700">
                    <div class="inline-flex items-center justify-center w-10 h-10 text-slate-600 bg-slate-100 rounded-xl">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2z"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">No noise, ever</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">We respect your attention. One drop, once a week, crafted to create leverage.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="membership-details" class="py-20 bg-white dark:bg-slate-950 border-y border-slate-200/60 dark:border-slate-800">
        <div class="px-6 mx-auto space-y-16 max-w-6xl">
            <div class="grid gap-12 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold tracking-wide text-blue-500 uppercase">Product overview</p>
                    <h2 class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">A membership for builders who need verified operating intelligence.</h2>
                    <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">NextGenBeing is a digital subscription that delivers weekly research-backed playbooks, curated tooling analysis, and implementation templates designed to help founders, operators, and product teams ship faster with confidence.</p>
                    <ul class="mt-6 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-3"><span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-500/10 text-blue-500"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span><span><strong>Weekly NextGen Playbook.</strong> 1,200+ word deep dive covering a core system, including step-by-step workflows, operating heuristics, and annotated screenshots.</span></li>
                        <li class="flex items-start gap-3"><span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-500/10 text-blue-500"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span><span><strong>Downloadable templates &amp; automations.</strong> Implementation checklists, SOPs, and Notion/CSV templates that accompany every drop so teams can deploy the ideas immediately.</span></li>
                        <li class="flex items-start gap-3"><span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-500/10 text-blue-500"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span><span><strong>Members-only research library.</strong> Unlimited access to our searchable archive of tooling breakdowns, benchmarks, and implementation notes.</span></li>
                        <li class="flex items-start gap-3"><span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-500/10 text-blue-500"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span><span><strong>Priority support.</strong> Direct line to our research team for clarifications, source requests, and custom teardown suggestions.</span></li>
                    </ul>
                </div>
                <div class="space-y-6">
                    <p class="text-sm font-semibold tracking-wide text-blue-500 uppercase">Billing &amp; access</p>
                    <div class="rounded-2xl border border-slate-200 bg-white/60 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/60">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Transparent pricing for every stage</h3>
                        <ul class="mt-4 space-y-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            <li><strong>Basic — $9.99/mo.</strong> Premium articles, ad-free experience, and baseline analytics.</li>
                            <li><strong>Pro — $19.99/mo.</strong> Everything in Basic plus early access drops, exclusive webinars, and downloadable PDF packs.</li>
                            <li><strong>Enterprise — $49.99/mo.</strong> Team seats, API access, dedicated success manager, and custom analytics.</li>
                        </ul>
                        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">Every plan starts with a 7-day free trial. You can upgrade, pause, or cancel anytime inside your account dashboard. Annual billing options are available on request.</p>
                        <p class="mt-4 text-sm leading-6 text-slate-600 dark:text-slate-300">Need a quote for procurement? Download the enterprise pricing overview or email us at {{ 'sales@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'https://nextgenbeing.com')) }}.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <a href="{{ route('subscription.plans') }}" class="flex items-center justify-between rounded-2xl border border-blue-500/40 bg-blue-500/10 px-4 py-3 text-sm font-semibold text-blue-500 transition hover:bg-blue-500/20">
                            View detailed pricing
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ asset('downloads/nextgenbeing-enterprise-pricing.pdf') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 dark:border-slate-700 dark:text-slate-200 dark:hover:border-slate-500">
                            Download enterprise sheet
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16M4 12h16M4 20h16"/></svg>
                        </a>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 text-sm leading-6 text-slate-600 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300">
                        <p><strong>Policies at a glance:</strong> <a class="text-blue-600 hover:underline" href="{{ route('terms') }}">Terms &amp; Conditions</a>, <a class="text-blue-600 hover:underline" href="{{ route('privacy') }}">Privacy Policy</a>, and <a class="text-blue-600 hover:underline" href="{{ route('refund') }}">Refund Policy</a> outline how we handle subscriptions, cancellations, and data.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-16 bg-slate-900">
        <div class="px-6 mx-auto max-w-7xl">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold tracking-wide text-blue-300 uppercase">Stay sharp</p>
                    <h2 class="mt-3 text-3xl font-bold text-white">Ready to dive deeper?</h2>
                    <p class="mt-4 text-base leading-7 text-slate-300">Browse our in-depth analysis, curated toolkits, and operating frameworks across the most requested topics.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('posts.index') }}" class="inline-flex items-center px-4 py-3 text-sm font-semibold text-slate-900 bg-white rounded-xl shadow-sm hover:-translate-y-0.5 hover:shadow-lg transition">
                        Browse the library
                        <svg class="w-4 h-4 ml-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('search') }}" class="inline-flex items-center px-4 py-3 text-sm font-semibold text-white border border-white/30 rounded-xl hover:bg-white/10 transition">
                        Search intelligence
                    </a>
                </div>
            </div>
            <div id="features" class="grid gap-6 mt-12 lg:grid-cols-3">
                <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
                    <p class="text-xs font-semibold tracking-wide text-blue-200 uppercase">Workflow architecture</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">Operating systems that compound</h3>
                    <p class="mt-3 text-sm text-slate-300">Weekly agenda setups, async documentation stacks, and focus rituals used by elite operators.</p>
                </div>
                <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
                    <p class="text-xs font-semibold tracking-wide text-blue-200 uppercase">Tooling reviews</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">Objective, faceless breakdowns</h3>
                    <p class="mt-3 text-sm text-slate-300">See the tradeoffs across AI copilots, research assistants, and automation suites before you invest.</p>
                </div>
                <div class="p-6 bg-white/5 border border-white/10 rounded-2xl">
                    <p class="text-xs font-semibold tracking-wide text-blue-200 uppercase">Playbooks</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">From idea to execution faster</h3>
                    <p class="mt-3 text-sm text-slate-300">Replicate the launch cadence and growth loops powering the next wave of internet-first companies.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="policies" class="py-16 bg-slate-100 dark:bg-slate-900/40">
        <div class="px-6 mx-auto max-w-6xl">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold tracking-wide text-blue-500 uppercase">Trust &amp; compliance</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900 dark:text-white">Clear policies and secure billing.</h2>
                <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">All purchases are processed over HTTPS with Paddle. Review our policies any time or reach the team directly at {{ 'support@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'https://nextgenbeing.com')) }}.</p>
            </div>
            <div class="grid gap-6 mt-10 md:grid-cols-3">
                <a href="{{ route('terms') }}" class="group block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-blue-500 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Terms &amp; Conditions</h3>
                        <svg class="h-5 w-5 text-blue-500 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">Understand usage rights, subscription terms, and account responsibilities.</p>
                </a>
                <a href="{{ route('privacy') }}" class="group block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-blue-500 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Privacy Policy</h3>
                        <svg class="h-5 w-5 text-blue-500 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">See how we handle personal data, security controls, and GDPR compliance.</p>
                </a>
                <a href="{{ route('refund') }}" class="group block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-blue-500 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Refund Policy</h3>
                        <svg class="h-5 w-5 text-blue-500 transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">Review trial periods, cancellation windows, and how to request refunds.</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Subscribe Modal -->
    <div x-cloak x-show="showSubscribeModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-10 bg-black/60 backdrop-blur-sm">
        <div x-show="showSubscribeModal" x-transition class="w-full max-w-md p-6 bg-white rounded-2xl shadow-2xl dark:bg-slate-900 border border-slate-200/70 dark:border-slate-700" @click.away="closeModal()">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Get the weekly drop</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">One email, once a week. High-signal rundowns, zero noise.</p>
                </div>
                <button type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" @click="closeModal()">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('landing.subscribe') }}" class="mt-6" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <div class="space-y-3">
                    <label for="modal-email" class="text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                    <input
                        id="modal-email"
                        type="email"
                        name="email"
                        required
                        autocomplete="email"
                        value="{{ old('email') }}"
                        placeholder="you@domain.com"
                        class="w-full px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700"
                    >
                    @error('email')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full flex items-center justify-center gap-2 mt-6 px-4 py-3 text-sm font-semibold text-white transition rounded-xl bg-blue-500 hover:bg-blue-400 disabled:opacity-70" :disabled="loading">
                    <span x-show="!loading">Subscribe</span>
                    <span x-show="loading" class="inline-flex items-center gap-2" x-cloak>
                        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path></svg>
                        Sending
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

