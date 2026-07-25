@extends('layouts.app')

@section('title', 'NextGenBeing - Explore the tech that evolves you')
@section('description', 'Curated insights, tool breakdowns, and operating frameworks for ambitious builders and creators.')


@php
    $shareImageLanding = setting('default_meta_image', setting('site_logo', asset('uploads/logo.png')));
    $shareImageLanding = preg_match('/^https?:\/\//i', $shareImageLanding) ? $shareImageLanding : url($shareImageLanding);
    $siteName = setting('site_name', config('app.name'));
    $companyName = setting('company_name', $siteName);
    $supportEmail = setting('support_email', 'support@' . request()->getHost());
    $pricingSheet = asset('downloads/nextgenbeing-enterprise-pricing.pdf');
@endphp

@section('canonical', route('home'))
@section('share_image', $shareImageLanding)
@section('author', $companyName)
@section('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $siteName,
    'description' => 'Weekly research-backed operating playbooks, tooling analysis, and premium community content from NextGenBeing.',
    'image' => [$shareImageLanding],
    'url' => route('home'),
    'brand' => [
        '@type' => 'Brand',
        'name' => $siteName,
    ],
    'offers' => [
        [
            '@type' => 'Offer',
            'price' => '9.99',
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'category' => 'Basic',
            'description' => 'Monthly membership with premium articles, ad-free reading, and analytics.',
            'url' => route('subscription.plans'),
        ],
        [
            '@type' => 'Offer',
            'price' => '19.99',
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'category' => 'Pro',
            'description' => 'Includes early access drops, exclusive webinars, and downloads.',
            'url' => route('subscription.plans'),
        ],
        [
            '@type' => 'Offer',
            'price' => '49.99',
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'category' => 'Enterprise',
            'description' => 'Team seats, API access, and dedicated success support.',
            'url' => route('subscription.plans'),
            'additionalProperty' => [
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Enterprise Pricing Sheet',
                    'value' => $pricingSheet,
                ],
            ],
        ],
    ],
    'audience' => [
        '@type' => 'Audience',
        'audienceType' => 'Founders, operators, product teams',
    ],
    'seller' => [
        '@type' => 'Organization',
        'name' => $companyName,
        'url' => url('/'),
        'contactPoint' => [
            [
                '@type' => 'ContactPoint',
                'email' => $supportEmail,
                'contactType' => 'customer support',
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div id="ngb-landing" x-data="{
        showSubscribeModal: {{ $errors->any() ? 'true' : 'false' }},
        openModal() { this.showSubscribeModal = true; },
        closeModal() { this.showSubscribeModal = false; }
    }" x-on:keydown.escape.window="closeModal()">
    {{-- ============ HERO (redesigned): deep indigo + electric-blue signal, kinetic canvas ============ --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&display=swap" rel="stylesheet">
    <style>
        .ngb-hero{
            --ink: oklch(0.16 0.038 264);
            --ink-2: oklch(0.24 0.055 264);
            --fg: oklch(0.97 0.012 250);
            --muted: oklch(0.75 0.038 255);
            --signal: oklch(0.66 0.18 250);
            --line: oklch(1 0 0 / 0.08);
            background: radial-gradient(120% 90% at 82% 8%, var(--ink-2), var(--ink) 60%);
            color: var(--fg);
        }
        .ngb-display{ font-family:"Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif; font-weight:800; letter-spacing:-0.03em; line-height:0.98; }
        .ngb-hero h1{ font-size: clamp(2.7rem, 7.2vw, 5.75rem); }
        .ngb-grid-lines{
            background-image: linear-gradient(var(--line) 1px, transparent 1px), linear-gradient(90deg, var(--line) 1px, transparent 1px);
            background-size: 34px 34px; mask-image: radial-gradient(120% 80% at 50% 0%, #000 40%, transparent 78%);
        }
        .ngb-kicker{ font-family:"Bricolage Grotesque", sans-serif; }
        .ngb-cta{ background: var(--signal); color: oklch(0.99 0.012 250); box-shadow: 0 8px 30px oklch(0.66 0.18 250 / 0.35); transition: transform .25s cubic-bezier(.2,.9,.2,1), box-shadow .25s, filter .25s; }
        .ngb-cta:hover{ transform: translateY(-2px); box-shadow: 0 14px 44px oklch(0.66 0.18 250 / 0.5); filter: brightness(1.05); }
        .ngb-ghost{ transition: background .25s, transform .25s cubic-bezier(.2,.9,.2,1); }
        .ngb-ghost:hover{ background: oklch(1 0 0 / 0.07); transform: translateY(-2px); }
        .ngb-word{ color: var(--signal); position: relative; white-space: nowrap; }
        .ngb-reveal{ opacity:0; transform: translateY(18px); animation: ngbRise .9s cubic-bezier(.16,1,.3,1) forwards; }
        .ngb-d1{animation-delay:.05s} .ngb-d2{animation-delay:.16s} .ngb-d3{animation-delay:.28s} .ngb-d4{animation-delay:.40s} .ngb-d5{animation-delay:.52s}
        @keyframes ngbRise{ to{ opacity:1; transform: none; } }
        @media (prefers-reduced-motion: reduce){ .ngb-reveal{ animation:none; opacity:1; transform:none; } }
        .ngb-metric b{ font-family:"Bricolage Grotesque", sans-serif; font-weight:800; letter-spacing:-0.02em; }
        /* ---- site-wide landing cohesion ---- */
        #ngb-landing h2{ font-family:"Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif; letter-spacing:-0.025em; font-weight:800; }
        .ngb-eyebrow{ font-family:"Bricolage Grotesque", sans-serif; font-weight:700; letter-spacing:.16em; color:oklch(0.6 0.2 252); }
        .dark .ngb-eyebrow{ color:oklch(0.72 0.16 252); }
        .ngb-io{ opacity:0; transform:translateY(26px); transition:opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1); }
        .ngb-io.ngb-in{ opacity:1; transform:none; }
        .ngb-io-2{ transition-delay:.09s } .ngb-io-3{ transition-delay:.18s }
        @media (prefers-reduced-motion: reduce){ .ngb-io{ opacity:1; transform:none; transition:none; } }
    </style>

    <section id="product-overview" class="ngb-hero relative overflow-hidden">
        <div class="absolute inset-0 ngb-grid-lines pointer-events-none"></div>
        <canvas id="ngb-hero-canvas" class="absolute inset-0 w-full h-full pointer-events-none" aria-hidden="true"></canvas>
        <div class="absolute inset-x-0 bottom-0 h-40 pointer-events-none" style="background:linear-gradient(to bottom, transparent, oklch(0.16 0.038 264))"></div>

        <div class="relative px-6 mx-auto max-w-7xl pt-24 pb-28 lg:pt-28 lg:pb-36">
            <div class="max-w-4xl">
                <div class="ngb-reveal ngb-d1 inline-flex items-center gap-2.5 mb-8 text-[11px] font-semibold tracking-[0.18em] uppercase" style="color:var(--muted)">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex w-full h-full rounded-full animate-ping" style="background:var(--signal);opacity:.65"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full" style="background:var(--signal)"></span>
                    </span>
                    <span class="ngb-kicker">New drop every Tuesday</span>
                    <span class="w-8 h-px" style="background:var(--line)"></span>
                    <span>for people who ship</span>
                </div>

                <h1 class="ngb-display ngb-reveal ngb-d2">
                    Everything you need<br>to ship. <span class="ngb-word">Nothing</span> you don't.
                </h1>

                <p class="ngb-reveal ngb-d3 max-w-xl mt-8 text-lg leading-8" style="color:var(--muted)">
                    The AI tools, workflows, and operating systems the best builders quietly rely on, cut down to what actually works and written to be run today.
                </p>

                @if(session('success'))
                    <div class="ngb-reveal ngb-d3 flex items-start gap-3 p-4 mt-8 max-w-xl text-sm font-medium rounded-xl" style="color:oklch(0.92 0.06 150);background:oklch(0.5 0.12 150 / 0.14);border:1px solid oklch(0.6 0.12 150 / 0.35)">
                        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="leading-6">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="ngb-reveal ngb-d4 flex flex-wrap items-center gap-4 mt-11">
                    <button type="button" @click="openModal()" class="ngb-cta ngb-display inline-flex items-center justify-center gap-2 px-7 py-3.5 text-base rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-amber-500" style="--tw-ring-offset-color:var(--ink)">
                        Get the drops
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                    <a href="{{ route('posts.index') }}" class="ngb-ghost inline-flex items-center gap-2 px-6 py-3.5 text-base font-semibold rounded-full" style="color:var(--fg);border:1px solid var(--line)">
                        Browse the work
                    </a>
                </div>

                <div class="ngb-reveal ngb-d5 flex flex-wrap items-center gap-x-8 gap-y-3 mt-12 ngb-metric" style="color:var(--muted)">
                    <span class="flex items-baseline gap-2 text-sm"><b class="text-xl" style="color:var(--fg)">{{ number_format(max(\App\Models\Post::published()->count(), 1)) }}</b> deep-dives published</span>
                    <span class="hidden w-px h-5 sm:block" style="background:var(--line)"></span>
                    <span class="text-sm">No spam. No fluff. Cancel any time.</span>
                </div>
            </div>
        </div>
    </section>

    <script>
    (function(){
        var c = document.getElementById('ngb-hero-canvas');
        if(!c || !c.getContext) return;
        var ctx = c.getContext('2d'), reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
        var w=0,h=0,dpr=1,dots=[],mx=-1e4,my=-1e4,t=0,GAP=34;
        function resize(){
            dpr=Math.min(window.devicePixelRatio||1,2);
            var r=c.getBoundingClientRect(); w=r.width; h=r.height;
            c.width=w*dpr; c.height=h*dpr; ctx.setTransform(dpr,0,0,dpr,0,0);
            dots=[];
            for(var y=GAP/2;y<h;y+=GAP){ for(var x=GAP/2;x<w;x+=GAP){ dots.push({x:x,y:y}); } }
        }
        function frame(){
            t+=0.016; ctx.clearRect(0,0,w,h);
            for(var i=0;i<dots.length;i++){
                var d=dots[i], dx=d.x-mx, dy=d.y-my, dist=Math.sqrt(dx*dx+dy*dy);
                var push=Math.max(0,140-dist)/140;
                var ox=d.x+(dx/(dist||1))*push*16, oy=d.y+(dy/(dist||1))*push*16;
                ctx.fillStyle='rgba(96,162,255,'+(0.06+push*0.55)+')';
                ctx.beginPath(); ctx.arc(ox,oy,0.8+push*1.9,0,6.2832); ctx.fill();
            }
            ctx.beginPath();
            for(var x=0;x<=w;x+=6){
                var yy=h*0.62 + Math.sin(x*0.011+t)*20*Math.sin(t*0.35) + Math.sin(x*0.028-t*1.4)*9;
                x===0?ctx.moveTo(x,yy):ctx.lineTo(x,yy);
            }
            ctx.strokeStyle='rgba(120,175,255,0.55)'; ctx.lineWidth=1.5; ctx.stroke();
            raf=requestAnimationFrame(frame);
        }
        var raf;
        window.addEventListener('resize',resize);
        window.addEventListener('pointermove',function(e){ var r=c.getBoundingClientRect(); mx=e.clientX-r.left; my=e.clientY-r.top; });
        resize();
        if(reduce){
            for(var i=0;i<dots.length;i++){ ctx.fillStyle='rgba(96,162,255,0.10)'; ctx.beginPath(); ctx.arc(dots[i].x,dots[i].y,1,0,6.2832); ctx.fill(); }
        } else { frame(); }
    })();
    </script>

    <script>
    (function(){
        function reveal(){
            var els = document.querySelectorAll('#ngb-landing .ngb-io');
            if(!els.length) return;
            if(!('IntersectionObserver' in window) || matchMedia('(prefers-reduced-motion: reduce)').matches){
                for(var i=0;i<els.length;i++){ els[i].classList.add('ngb-in'); } return;
            }
            var io = new IntersectionObserver(function(entries){
                entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('ngb-in'); io.unobserve(e.target); } });
            }, { threshold: 0.12, rootMargin: '0px 0px -7% 0px' });
            for(var j=0;j<els.length;j++){ io.observe(els[j]); }
        }
        // The .ngb-io sections live BELOW this inline script, so wait for the full
        // DOM before querying (otherwise they never register and stay invisible).
        if(document.readyState === 'loading'){ document.addEventListener('DOMContentLoaded', reveal); }
        else { reveal(); }
        // Safety net: never let the reveal leave content hidden.
        window.addEventListener('load', function(){
            setTimeout(function(){
                var hidden = document.querySelectorAll('#ngb-landing .ngb-io:not(.ngb-in)');
                for(var k=0;k<hidden.length;k++){
                    var r = hidden[k].getBoundingClientRect();
                    if(r.top < window.innerHeight){ hidden[k].classList.add('ngb-in'); }
                }
            }, 400);
        });
    })();
    </script>

    <!-- Featured Articles for SEO -->
    @php
        // Get most viewed posts from last 2 months to keep homepage fresh
        $twoMonthsAgo = now()->subMonths(2);

        $featuredPost = \App\Models\Post::published()
            ->where('published_at', '>', $twoMonthsAgo)
            ->with(['category', 'author'])
            ->orderByDesc('views_count')
            ->first();

        $topHeadlines = \App\Models\Post::published()
            ->where('published_at', '>', $twoMonthsAgo)
            ->with(['category', 'author'])
            ->orderByDesc('views_count')
            ->limit(4)
            ->get();

        $featuredCategories = \App\Models\Category::active()
            ->with(['publishedPosts' => function($q) use ($twoMonthsAgo) {
                $q->where('published_at', '>', $twoMonthsAgo);
            }])
            ->orderBy('sort_order')
            ->limit(3)
            ->get();
    @endphp

    @if($featuredPost)
    <section class="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-slate-900">
        <div class="max-w-7xl mx-auto">
            <!-- Featured Hero Article -->
            <div class="mb-12 ngb-io">
                <p class="ngb-eyebrow text-xs uppercase">Featured</p>
                <h2 class="mt-3 text-4xl text-slate-900 dark:text-white">Latest research &amp; insights</h2>
            </div>

            <div class="grid lg:grid-cols-3 gap-8 ngb-io ngb-io-2">
                <!-- Hero Featured Post -->
                <div class="lg:col-span-2">
                    <a href="{{ route('posts.show', $featuredPost->slug) }}" class="group block relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all h-96">
                        @if($featuredPost->featured_image)
                            <img src="{{ $featuredPost->featured_image }}" alt="Featured article: {{ $featuredPost->title }}" title="{{ $featuredPost->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-blue-400 via-blue-500 to-indigo-600" role="img" aria-label="Featured article placeholder"></div>
                        @endif
                        <!-- Overlay Gradient - Adaptive darkness for better text visibility -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent dark:from-black/95 dark:via-black/50 z-10"></div>

                        <!-- Content Overlay -->
                        <div class="absolute inset-0 p-8 flex flex-col justify-end z-20">
                            <div class="flex items-center gap-2 mb-4">
                                @if($featuredPost->category)
                                    <span class="inline-block px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500 text-white shadow-lg">
                                        {{ $featuredPost->category->name }}
                                    </span>
                                @endif
                                @if($featuredPost->read_time)
                                    <span class="text-sm text-gray-100">{{ $featuredPost->read_time }} min read</span>
                                @endif
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-3 line-clamp-3">
                                {{ $featuredPost->title }}
                            </h3>
                            <p class="text-gray-100 text-base mb-4 line-clamp-2">{{ Str::limit($featuredPost->excerpt, 150) }}</p>
                            <div class="flex items-center justify-between pt-4 border-t border-white/20">
                                <span class="text-sm text-gray-200">{{ $featuredPost->published_at->format('M d, Y') }}</span>
                                <span class="inline-flex items-center text-white font-semibold group-hover:gap-2 gap-1 transition-all">
                                    Read full article
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Top Headlines -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Top Headlines</h3>
                    @foreach($topHeadlines->skip(1)->take(3) as $post)
                        <article class="p-4 bg-white dark:bg-slate-800 rounded-lg shadow hover:shadow-lg transition-shadow border dark:border-slate-700">
                            <div class="flex items-start gap-3">
                                @if($post->featured_image)
                                    <img src="{{ $post->featured_image }}" alt="Article preview: {{ $post->title }}" title="{{ $post->title }}" class="w-16 h-16 object-cover rounded flex-shrink-0">
                                @endif
                                <div class="flex-1">
                                    @if($post->category)
                                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-300 uppercase" title="{{ $post->category->name }}">{{ $post->category->name }}</span>
                                    @endif
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white mt-1 line-clamp-2">
                                        <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-300 transition" title="Read article: {{ $post->title }}">
                                            {{ $post->title }}
                                        </a>
                                    </h4>
                                    <span class="text-xs text-gray-600 dark:text-gray-300 mt-2 block">{{ $post->published_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <!-- Featured Categories -->
            @if($featuredCategories->count() > 0)
            <div class="mt-20 pt-14 border-t border-gray-200 dark:border-slate-800 ngb-io">
                <p class="ngb-eyebrow text-xs uppercase">Topics</p>
                <h2 class="mt-3 text-4xl text-slate-900 dark:text-white mb-3">Explore by topic</h2>
                <p class="text-gray-700 dark:text-gray-400 mb-10 max-w-2xl text-lg">Curated collections, organized by what you're actually building. Find the thread and pull.</p>
                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($featuredCategories as $category)
                        @php $categoryPosts = $category->publishedPosts()->limit(3)->get(); @endphp
                        @if($categoryPosts->count() > 0)
                        <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-slate-800 dark:to-slate-900 rounded-xl shadow-md border border-gray-300 dark:border-slate-700 hover:shadow-lg hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-300 overflow-hidden">
                            <!-- Category Header with Icon -->
                            <div class="px-6 pt-6 pb-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-800 border-b border-gray-200 dark:border-slate-700">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        @if($category->icon)
                                            <div class="text-3xl">{{ $category->icon }}</div>
                                        @else
                                            <div class="w-10 h-10 bg-blue-200 dark:bg-blue-900/40 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                                <a href="{{ route('categories.show', $category->slug) }}">
                                                    {{ $category->name }}
                                                </a>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                @if($category->description)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $category->description }}</p>
                                @endif
                            </div>

                            <!-- Posts List -->
                            <div class="px-6 py-6 space-y-4">
                                @foreach($categoryPosts as $post)
                                    <article class="pb-4 border-b border-gray-200 dark:border-slate-700 last:pb-0 last:border-0">
                                        <h4 class="text-sm font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition line-clamp-2 leading-snug">
                                            <a href="{{ route('posts.show', $post->slug) }}">
                                                {{ $post->title }}
                                            </a>
                                        </h4>
                                        <div class="flex items-center gap-3 mt-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                            <span class="inline-block px-2 py-1 bg-gray-100 dark:bg-slate-700 rounded">{{ $post->published_at->format('M d, Y') }}</span>
                                            @if($post->read_time)
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $post->read_time }} min
                                                </span>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <!-- CTA Button -->
                            <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-white dark:from-slate-800 dark:to-slate-900 border-t border-gray-200 dark:border-slate-700">
                                <a href="{{ route('categories.show', $category->slug) }}" class="inline-flex items-center justify-center w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg transition-colors duration-200">
                                    View all {{ $category->name }} articles
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>
    @endif

    <section class="py-24 bg-white dark:bg-slate-950">
        <div class="px-6 mx-auto max-w-6xl">
            <div class="max-w-3xl ngb-io">
                <p class="ngb-eyebrow text-xs uppercase">Why readers stay</p>
                <h2 class="mt-4 text-4xl sm:text-5xl text-slate-900 dark:text-white">Signal-rich briefings,<br>shipped weekly.</h2>
                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-400">
                    Each edition packages deep research, vetted frameworks, and tested playbooks into a frictionless read. Drop in for the intelligence you need, skip the noise.
                </p>
            </div>
            <div class="grid mt-16 sm:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-12 ngb-io ngb-io-2">
                @foreach([
                    ['Curated in the open','Transparent breakdowns of the stack we use to build, test, and launch products faster.'],
                    ['Actionable, not theoretical','Immediate prompts, automations, and agendas you can drop into your operating system within minutes.'],
                    ['Designed for momentum','Save hours of scattered research and unlock a cadence that keeps your team compounding.'],
                    ['Time to value in minutes','Read in under ten minutes and ship upgrades the same day.'],
                    ['Community sourced insight','Powered by founders, analysts, and systems thinkers behind breakout products.'],
                    ['No noise, ever','We respect your attention. One drop, once a week, crafted to create leverage.'],
                ] as $i => $item)
                <div class="group relative pb-1">
                    <div class="flex items-baseline gap-4">
                        <span class="text-2xl font-extrabold tabular-nums" style="font-family:'Bricolage Grotesque',sans-serif;color:oklch(0.62 0.2 252)">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $item[0] }}</h3>
                    </div>
                    <p class="mt-3 pl-10 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $item[1] }}</p>
                    <div class="mt-6 h-px w-full bg-slate-200 dark:bg-slate-800"></div>
                    <div class="absolute bottom-0 left-0 h-px w-full origin-left scale-x-0 transition-transform duration-500 ease-out group-hover:scale-x-100" style="background:oklch(0.62 0.2 252)"></div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="membership-details" class="py-20 bg-white dark:bg-slate-950 border-y border-slate-200/60 dark:border-slate-800">
        <div class="px-6 mx-auto space-y-16 max-w-6xl">
            <div class="grid gap-12 lg:grid-cols-2 ngb-io">
                <div>
                    <p class="ngb-eyebrow text-xs uppercase">Product overview</p>
                    <h2 class="mt-3 text-4xl text-slate-900 dark:text-white">A membership for builders who need verified operating intelligence.</h2>
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
            <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between ngb-io">
                <div class="max-w-2xl">
                    <p class="ngb-eyebrow text-xs uppercase" style="color:oklch(0.74 0.15 252)">Premium intelligence platform</p>
                    <h2 class="mt-3 text-4xl text-white">What sets NextGenBeing apart</h2>
                    <p class="mt-4 text-base leading-7 text-slate-300">
                        Unlike general tech news, we specialize in <strong>actionable intelligence</strong>.
                        Every drop includes frameworks you can implement, tools you can test, and strategies you can replicate.
                    </p>
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
            <div id="features" class="grid gap-6 mt-12 lg:grid-cols-3 ngb-io">
                <!-- Feature 1: Systems & Workflows -->
                <div class="group p-6 bg-white/5 border border-white/10 hover:border-blue-500/50 rounded-2xl transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-500/20 text-blue-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l-2 2M9 6l2-2m0 0l2 2m-2-2v13m4-8l-2-2m2 2l2-2m0 0V8m0 0l-2 2m0-2v13"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-blue-300/60 uppercase tracking-wider">Workflow architecture</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Operating systems that compound</h3>
                    <p class="mt-2 text-sm text-slate-300 mb-4">
                        Discover the weekly agenda setups, async documentation stacks, and focus rituals that elite operators use to maintain clarity and momentum.
                    </p>
                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Time-blocking frameworks proven by founders</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Documentation templates for team alignment</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Decision-making protocols that scale</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 2: Tooling Reviews -->
                <div class="group p-6 bg-white/5 border border-white/10 hover:border-blue-500/50 rounded-2xl transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-500/20 text-blue-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-blue-300/60 uppercase tracking-wider">Tooling reviews</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Objective, faceless breakdowns</h3>
                    <p class="mt-2 text-sm text-slate-300 mb-4">
                        Compare AI copilots, research assistants, and automation platforms with unbiased analysis. No sponsored reviews. Pure tradeoffs.
                    </p>
                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Hands-on testing across 50+ tools monthly</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Cost analysis and ROI calculations</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Integration guides and workflow patterns</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 3: Playbooks -->
                <div class="group p-6 bg-white/5 border border-white/10 hover:border-blue-500/50 rounded-2xl transition">
                    <div class="flex items-center justify-between mb-4">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-500/20 text-blue-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-blue-300/60 uppercase tracking-wider">Playbooks</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white">From idea to execution faster</h3>
                    <p class="mt-2 text-sm text-slate-300 mb-4">
                        Replicate the launch cadence, growth loops, and go-to-market strategies that power the next wave of internet-first companies.
                    </p>
                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Step-by-step execution frameworks</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Metrics to track and milestones to hit</span>
                        </li>
                        <li class="flex items-start gap-2 text-xs text-slate-400">
                            <span class="text-blue-400 mt-0.5">✓</span>
                            <span>Real case studies from 100+ launches</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Trust & Authority Section -->
            <div class="mt-16 pt-12 border-t border-white/10">
                <div class="grid gap-12 md:grid-cols-3">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/20 text-blue-300 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-white">Hands-on research</h4>
                        <p class="mt-2 text-xs text-slate-400">
                            Every recommendation is tested and validated before publication. No speculation, no sponsored content.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/20 text-blue-300 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 6a3 3 0 11-6 0 3 3 0 016 0zM16 16a5 5 0 01-8 0"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-white">Built by operators</h4>
                        <p class="mt-2 text-xs text-slate-400">
                            Written by founders, product leaders, and engineers who've shipped at scale. Real experience, real insights.
                        </p>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/20 text-blue-300 mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-white">Constantly updated</h4>
                        <p class="mt-2 text-xs text-slate-400">
                            New drops every week. Playbooks refresh quarterly. Tools tracked in real-time as they evolve.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="policies" class="py-16 bg-slate-100 dark:bg-slate-900/40">
        <div class="px-6 mx-auto max-w-6xl">
            <div class="max-w-3xl ngb-io">
                <p class="ngb-eyebrow text-xs uppercase">Trust &amp; compliance</p>
                <h2 class="mt-3 text-4xl text-slate-900 dark:text-white">Clear policies and secure billing.</h2>
                <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">All purchases are processed securely over HTTPS with LemonSqueezy. Review our policies any time or reach the team directly at {{ 'support@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'https://nextgenbeing.com')) }}.</p>
            </div>
            <div class="grid gap-6 mt-10 md:grid-cols-3 ngb-io ngb-io-2">
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

