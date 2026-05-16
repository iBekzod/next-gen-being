@extends('layouts.app')

@section('title', $post->getSeoTitle() . ' - ' . setting('site_name'))
@section('description', $post->getSeoDescription())
@section('keywords', $post->getSeoKeywords())
@section('og_type', 'article')

@php
    $shareImage = $post->featured_image ? url($post->featured_image) : setting('default_meta_image', setting('site_logo', asset('uploads/logo.png')));
    $logoPath = setting('site_logo', asset('uploads/logo.png'));
    $logoUrl = preg_match('/^https?:\/\//i', $logoPath) ? $logoPath : url($logoPath);
    $siteName = setting('site_name', config('app.name'));
    $companyName = setting('company_name', $siteName);
    $publishDate = optional($post->published_at)->toAtomString();
    $modifiedDate = optional($post->updated_at)->toAtomString();
    $wordCount = str_word_count(strip_tags($post->content));
    $tagNames = $post->tags->pluck('name')->filter()->values()->all();
    $articleSection = optional($post->category)->name;
@endphp

@section('canonical', route('posts.show', $post->slug))
@section('share_image', $shareImage)
@section('author', $post->author->name)
@section('robots', $post->is_premium ? 'index, follow, noarchive' : 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')


@push('head')
<meta property="article:author" content="{{ $post->author->name }}">
<meta property="article:published_time" content="{{ optional($post->published_at)->toIso8601String() }}">
<meta property="article:modified_time" content="{{ optional($post->updated_at)->toIso8601String() }}">
<meta property="article:section" content="{{ $articleSection }}">
@foreach($post->tags as $tag)
<meta property="article:tag" content="{{ $tag->name }}">
@endforeach
@endpush

@php
// Build breadcrumb structure
$breadcrumbItems = [
    [
        '@type' => 'ListItem',
        'position' => 1,
        'name' => 'Home',
        'item' => route('home'),
    ],
    [
        '@type' => 'ListItem',
        'position' => 2,
        'name' => 'Articles',
        'item' => route('posts.index'),
    ],
];

if ($post->category) {
    $breadcrumbItems[] = [
        '@type' => 'ListItem',
        'position' => 3,
        'name' => $post->category->name,
        'item' => route('categories.show', $post->category->slug),
    ];
}

$breadcrumbItems[] = [
    '@type' => 'ListItem',
    'position' => count($breadcrumbItems) + 1,
    'name' => $post->title,
    'item' => route('posts.show', $post->slug),
];
@endphp

@push('structured-data')
<!-- Article Schema -->
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post->title,
    'description' => $post->excerpt,
    'image' => [$shareImage],
    'wordCount' => $wordCount,
    'author' => [
        '@type' => 'Person',
        'name' => $post->author->name,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => $companyName,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
        ],
    ],
    'datePublished' => $publishDate,
    'dateModified' => $modifiedDate ?: $publishDate,
    'isAccessibleForFree' => ! $post->is_premium,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('posts.show', $post->slug),
    ],
    'articleSection' => $articleSection,
    'keywords' => implode(', ', $tagNames),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

<!-- Breadcrumb Schema -->
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbItems,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

@if($post->post_type === 'video_blog' && $post->video_url)
<!-- Video Object Schema -->
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'VideoObject',
    'name' => $post->title,
    'description' => $post->excerpt,
    'thumbnailUrl' => $post->video_thumbnail ?? $shareImage,
    'uploadDate' => $publishDate,
    'duration' => $post->video_duration ? 'PT' . $post->video_duration . 'M' : 'PT5M',
    'contentUrl' => $post->video_url,
    'url' => route('posts.show', $post->slug),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

@if($post->series_slug && $post->post_type === 'article')
<!-- HowTo Schema for Tutorial Series (uses real H2 headings as steps for rich snippets) -->
@php $howToSteps = $post->how_to_steps; @endphp
@if(!empty($howToSteps))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'HowTo',
    'name' => $post->title,
    'description' => $post->clean_excerpt,
    'totalTime' => 'PT' . ($post->read_time ?? 5) . 'M',
    'image' => [$shareImage],
    'step' => $howToSteps,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
@endif
@endpush

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-6xl">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-blue-200 hover:text-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to discoveries
        </a>

        @if($post->isPartOfSeries())
        <div class="inline-flex items-center gap-2 px-3 py-1 mt-4 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
            </svg>
            Part {{ $post->series_part }} of {{ $post->series_total_parts }}
        </div>
        @endif

        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl max-w-3xl">{{ $post->title }}</h1>
        <p class="mt-4 text-base text-slate-300 max-w-2xl">{{ $post->excerpt }}</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-4 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
        @if($post->isPartOfSeries())
        <!-- Two-column layout for series posts -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content (Left) -->
            <div class="flex-1 min-w-0 lg:max-w-3xl">
                @livewire('post-show', ['post' => $post])
            </div>

            <!-- Sticky Series Navigation (Right) -->
            <div class="lg:w-80 xl:w-96">
                <x-post.series-navigation-sidebar :post="$post" />
            </div>
        </div>
        @else
        <!-- Single post layout -->
        @livewire('post-show', ['post' => $post])
        @endif
    </div>
</section>
@endsection

@push('scripts')
<style>
.prose pre { position: relative; }
.copy-code-btn {
    position: absolute; top: 8px; right: 8px;
    padding: 4px 10px; font-size: 11px; font-weight: 600;
    border-radius: 6px; cursor: pointer;
    background: rgba(255,255,255,0.1); color: #cbd5e1;
    border: 1px solid rgba(255,255,255,0.15);
    opacity: 0; transition: opacity 0.15s, background 0.15s;
    font-family: ui-sans-serif, system-ui, sans-serif;
}
.prose pre:hover .copy-code-btn { opacity: 1; }
.copy-code-btn:hover { background: rgba(255,255,255,0.2); color: #fff; }
.copy-code-btn.copied { background: rgba(34,197,94,0.25); color: #86efac; border-color: rgba(34,197,94,0.4); }
.reading-progress {
    position: fixed; top: 0; left: 0; height: 3px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    z-index: 100; width: 0%;
    transition: width 80ms linear;
}
</style>
<div class="reading-progress" id="rp-bar"></div>
<script>
// Code-block copy buttons
document.querySelectorAll('article pre, .prose pre, main pre').forEach(pre => {
    if (pre.querySelector('.copy-code-btn')) return;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'copy-code-btn';
    btn.textContent = 'Copy';
    btn.addEventListener('click', async () => {
        const code = pre.querySelector('code')?.textContent || pre.textContent;
        try {
            await navigator.clipboard.writeText(code.replace(/Copy$/, '').trim());
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
            setTimeout(() => { btn.textContent = 'Copy'; btn.classList.remove('copied'); }, 1500);
        } catch (e) {
            btn.textContent = 'Press Ctrl+C';
        }
    });
    pre.appendChild(btn);
});

// Reading-progress bar
(function(){
    const bar = document.getElementById('rp-bar');
    if (!bar) return;
    const article = document.querySelector('article, main .prose, .preview-prose');
    function tick() {
        const target = article || document.body;
        const start = target.offsetTop;
        const end = start + target.scrollHeight - window.innerHeight;
        const scrolled = window.scrollY - start;
        const pct = Math.max(0, Math.min(100, (scrolled / (end - start)) * 100));
        bar.style.width = pct + '%';
    }
    document.addEventListener('scroll', tick, { passive: true });
    tick();
})();
</script>

<!-- Exit-intent newsletter popup + inline-CTA injection (added by enhance tier 2) -->
<style>
#exit-popup-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9998; display: none; align-items: center; justify-content: center; }
#exit-popup { background: #fff; max-width: 460px; width: 90%; border-radius: 16px; padding: 32px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
.dark #exit-popup { background: #1e293b; color: #f1f5f9; }
#exit-popup .close { position: absolute; top: 12px; right: 12px; background: transparent; border: 0; color: #94a3b8; cursor: pointer; font-size: 24px; line-height: 1; padding: 4px; }
#exit-popup .close:hover { color: #334155; }
.dark #exit-popup .close:hover { color: #f1f5f9; }
#exit-popup h2 { font-size: 22px; font-weight: 700; margin: 0 0 8px; line-height: 1.2; }
#exit-popup p { font-size: 14px; color: #64748b; margin: 0 0 20px; }
.dark #exit-popup p { color: #cbd5e1; }
#exit-popup input { width: 100%; padding: 12px 16px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 15px; margin-bottom: 12px; }
.dark #exit-popup input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
#exit-popup button[type=submit] { width: 100%; padding: 12px; border-radius: 10px; background: #2563eb; color: #fff; font-weight: 600; border: 0; cursor: pointer; font-size: 15px; }
#exit-popup button[type=submit]:hover { background: #1d4ed8; }
#exit-popup .skip { display: block; text-align: center; margin-top: 12px; font-size: 12px; color: #94a3b8; cursor: pointer; background: transparent; border: 0; width: 100%; }

.inline-newsletter-cta { margin: 32px 0; padding: 24px; border-radius: 12px; background: linear-gradient(135deg, #eff6ff, #f3e8ff); border: 1px solid #dbeafe; }
.dark .inline-newsletter-cta { background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(139,92,246,0.1)); border-color: rgba(59,130,246,0.2); }
.inline-newsletter-cta h3 { margin: 0 0 6px; font-size: 18px; font-weight: 700; color: #1e293b; }
.dark .inline-newsletter-cta h3 { color: #f1f5f9; }
.inline-newsletter-cta p { margin: 0 0 16px; font-size: 14px; color: #475569; }
.dark .inline-newsletter-cta p { color: #cbd5e1; }
.inline-newsletter-cta form { display: flex; gap: 8px; }
.inline-newsletter-cta input { flex: 1; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; }
.dark .inline-newsletter-cta input { background: rgba(15,23,42,0.5); border-color: #334155; color: #f1f5f9; }
.inline-newsletter-cta button { padding: 10px 18px; border-radius: 8px; background: #2563eb; color: #fff; font-weight: 600; border: 0; cursor: pointer; font-size: 14px; white-space: nowrap; }
@media (max-width: 480px) { .inline-newsletter-cta form { flex-direction: column; } }
</style>

<div id="exit-popup-backdrop">
    <div id="exit-popup">
        <button type="button" class="close" aria-label="Close">&times;</button>
        <h2>Don't miss the next deep dive</h2>
        <p>Get one well-researched tutorial in your inbox each week. No spam, unsubscribe anytime.</p>
        <form id="exit-popup-form">
            <input type="email" name="email" placeholder="your@email.com" required autocomplete="email">
            <button type="submit">Subscribe — it's free</button>
        </form>
        <button type="button" class="skip">No thanks</button>
    </div>
</div>

<script>
(function() {
    const STORAGE_KEY = 'ngb_exit_popup_dismissed_at';
    const DISMISS_DAYS = 7;

    function shouldShow() {
        try {
            const dismissed = localStorage.getItem(STORAGE_KEY);
            if (!dismissed) return true;
            return (Date.now() - parseInt(dismissed)) > DISMISS_DAYS * 24 * 60 * 60 * 1000;
        } catch (e) { return true; }
    }

    function dismiss() {
        try { localStorage.setItem(STORAGE_KEY, String(Date.now())); } catch (e) {}
        document.getElementById('exit-popup-backdrop').style.display = 'none';
    }

    function show() {
        if (!shouldShow()) return;
        document.getElementById('exit-popup-backdrop').style.display = 'flex';
    }

    // Trigger 1: desktop exit intent — mouse leaves viewport at top
    let shown = false;
    document.addEventListener('mouseout', e => {
        if (shown || e.clientY > 0 || e.relatedTarget) return;
        shown = true;
        show();
    });

    // Trigger 2: mobile fallback — scrolled >60% of page, after 30s
    setTimeout(() => {
        if (shown) return;
        const pct = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
        if (pct > 60) { shown = true; show(); }
    }, 30000);

    document.querySelector('#exit-popup .close')?.addEventListener('click', dismiss);
    document.querySelector('#exit-popup .skip')?.addEventListener('click', dismiss);
    document.getElementById('exit-popup-backdrop')?.addEventListener('click', e => {
        if (e.target.id === 'exit-popup-backdrop') dismiss();
    });

    // Submit handler — POST to /newsletter route if it exists
    document.getElementById('exit-popup-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = e.target.email.value;
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        try {
            const res = await fetch('/newsletter/quick-subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ email }),
            });
            const btn = e.target.querySelector('button[type=submit]');
            if (res.ok) {
                btn.textContent = 'Check your inbox!';
                btn.style.background = '#16a34a';
                setTimeout(dismiss, 2000);
            } else {
                btn.textContent = 'Try again';
            }
        } catch (err) {
            window.location.href = '/?subscribe=' + encodeURIComponent(email);
        }
    });

    // Inline mid-article CTA: inject after the 3rd H2 in the article content
    const article = document.querySelector('.prose, article .preview-prose, main .prose');
    if (article) {
        const h2s = article.querySelectorAll('h2');
        if (h2s.length >= 4) {
            const after = h2s[2]; // after the 3rd H2 = before the 4th section
            const cta = document.createElement('div');
            cta.className = 'inline-newsletter-cta not-prose';
            cta.innerHTML = `
                <h3>Enjoying this? Get the next one in your inbox.</h3>
                <p>One in-depth article every Monday. Unsubscribe anytime.</p>
                <form>
                    <input type="email" name="email" placeholder="your@email.com" required autocomplete="email">
                    <button type="submit">Subscribe</button>
                </form>
            `;
            after.parentNode.insertBefore(cta, after.nextSibling);
            cta.querySelector('form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = e.target.email.value;
                const btn = e.target.querySelector('button');
                btn.textContent = 'Subscribing...';
                const csrf = document.querySelector('meta[name=csrf-token]')?.content;
                try {
                    const res = await fetch('/newsletter/quick-subscribe', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ email }),
                    });
                    btn.textContent = res.ok ? 'Check your inbox!' : 'Try again';
                    if (res.ok) btn.style.background = '#16a34a';
                } catch (err) { btn.textContent = 'Try again'; }
            });
        }
    }
})();
</script>


<!-- Prism.js syntax highlighting (added by enhance tier 5) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css">
<style>
/* Polish the Prism output so it matches the rest of the site */
.prose pre[class*=language-], .preview-prose pre[class*=language-] {
    background: #0f172a !important;
    border-radius: 10px !important;
    padding: 1.1em 1.2em !important;
    margin: 1.2em 0 !important;
    font-size: 0.86em !important;
    line-height: 1.55 !important;
    overflow-x: auto;
    border: 1px solid #1e293b;
    position: relative;
}
.prose pre[class*=language-] code, .preview-prose pre[class*=language-] code {
    background: transparent !important;
    padding: 0 !important;
    border-radius: 0 !important;
    color: #e2e8f0 !important;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
}
/* Inline code stays distinct */
.prose code:not([class*=language-]), .preview-prose code:not([class*=language-]) {
    background: #f1f5f9;
    color: #475569;
    padding: 0.15em 0.4em;
    border-radius: 4px;
    font-size: 0.9em;
    font-family: ui-monospace, monospace;
}
.dark .prose code:not([class*=language-]), .dark .preview-prose code:not([class*=language-]) {
    background: rgba(148, 163, 184, 0.15);
    color: #cbd5e1;
}
/* Prism Tomorrow theme tokens tweaks for our background */
.token.comment, .token.prolog, .token.doctype, .token.cdata { color: #64748b; }
.token.string, .token.attr-value { color: #86efac; }
.token.keyword, .token.boolean { color: #c084fc; font-weight: 600; }
.token.function { color: #93c5fd; }
.token.number { color: #fbbf24; }
.token.tag, .token.selector { color: #f472b6; }
.token.property, .token.constant, .token.symbol { color: #f87171; }
.token.punctuation { color: #94a3b8; }
.token.operator { color: #fb923c; }
/* Make copy button visible on Prism-highlighted blocks */
.prose pre[class*=language-] .copy-code-btn,
.preview-prose pre[class*=language-] .copy-code-btn {
    background: rgba(255,255,255,0.08);
    color: #cbd5e1;
    border: 1px solid rgba(255,255,255,0.12);
    z-index: 5;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Prism === 'undefined') {
        // Try again shortly if Prism not yet loaded
        setTimeout(() => { if (typeof Prism !== 'undefined') Prism.highlightAll(); }, 500);
        return;
    }
    Prism.plugins.autoloader.languages_path = 'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/';
    Prism.highlightAll();
});
</script>

@endpush
