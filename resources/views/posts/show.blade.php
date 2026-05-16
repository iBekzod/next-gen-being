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
@endpush
