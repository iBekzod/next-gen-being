@extends('layouts.app')
@php $__is3d = request('category') === '3d-scenes'; @endphp
@if($__is3d)
@section('title', '3D Scenes for websites — three.js hero backgrounds with prompt and source')
@section('description', 'Original three.js 3D hero scenes with live demos: particle galaxy, liquid glass blob, wireframe terrain. Copy the Claude prompt, get the full source. $4 each, one free.')
@else
@section('title', 'Marketplace — live products you can try before you buy')
@section('description', 'Buy working web products with real, interactive live demos: SaaS dashboards, landing pages, 3D scenes and full projects. Try each one live, then get the prompt plan, design, or full code.')
@endif

@section('content')
@include('marketplace.partials.tokens')
<div id="ngb-market">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    @php
      $__feat = $listings->getCollection()->first(fn ($l) => $l->category !== '3d-scenes' && in_array($l->demo_type, ['static', 'external'], true));
      $__featSrc = $__feat ? ($__feat->demo_type === 'static' ? asset('storage/'.$__feat->demo_path) : $__feat->demo_url) : null;
    @endphp
    <header class="m-hero mb-10">
      <div>
        <p style="color:var(--signal-ink); font-weight:600; letter-spacing:.14em; text-transform:uppercase; font-size:.72rem;">Live product marketplace</p>
        <h1 class="m-display" style="font-size:clamp(2.1rem,5vw,3.4rem); line-height:1.02; margin:.4rem 0 .8rem;">Try it live.<br>Then buy.</h1>
        <p style="color:var(--ink-soft); max-width:52ch; font-size:1.02rem; line-height:1.6;">Every product is a real, running app, not a screenshot. Open the demo, click around, then buy the prompt plan, the design, or the full code.</p>
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:20px;">
          <a href="#m-grid" class="m-cta">Browse {{ $listings->total() }} live demos</a>
          @if($__feat)<a href="{{ route('marketplace.show', $__feat) }}" class="m-cta m-cta-ghost">Open {{ Str::before($__feat->title, ' - ') }}</a>@endif
        </div>
      </div>
      @if($__featSrc)
        <a href="{{ route('marketplace.show', $__feat) }}" class="m-frame" aria-label="Live preview: {{ $__feat->title }}">
          <span class="m-frame-bar"><i></i><i></i><i></i><span class="m-live" style="margin-left:auto;"><span class="blink"></span>LIVE</span></span>
          <span class="m-frame-body"><iframe src="{{ $__featSrc }}" title="Live preview of {{ $__feat->title }}" sandbox="allow-scripts allow-same-origin" loading="lazy" tabindex="-1" aria-hidden="true"></iframe></span>
        </a>
      @endif
    </header>

    <ol class="m-steps">
      <li><b>1</b><span><strong>Try it live</strong>Open any demo and click around, no signup.</span></li>
      <li><b>2</b><span><strong>Pick what you need</strong>The prompt plan, the design, or the full code.</span></li>
      <li><b>3</b><span><strong>Make it yours</strong>Paste the prompt into Claude or ship the source.</span></li>
    </ol>

    @if($__is3d)
      <div style="margin:-8px 0 22px; padding:14px 16px; border:1px solid var(--line-strong); border-radius:12px; background:var(--surface); color:var(--ink-soft); font-size:.9rem; line-height:1.6; max-width:78ch;">
        <b style="color:var(--ink);">3D Scenes</b> are original three.js hero backgrounds. Each one has a live demo on its page, a master prompt you can paste into Claude to recreate or customise it, and the full single-file source. They pause off screen, respect reduced motion, and fall back gracefully without WebGL.
      </div>
    @endif

    <div class="mb-6 flex flex-wrap gap-2">
      @php $__active = request('category'); @endphp
      <a href="{{ route('marketplace.index') }}" @if(! $__active) aria-current="page" @endif style="border:1px solid {{ $__active ? 'var(--line-strong)' : 'var(--signal)' }}; border-radius:999px; padding:6px 14px; font-size:.82rem; color:{{ $__active ? 'var(--ink-soft)' : 'var(--signal-ink)' }}; font-weight:{{ $__active ? 400 : 700 }};">All</a>
      @foreach($categories as $cat)
        <a href="{{ route('marketplace.index', ['category' => $cat]) }}" @if($__active === $cat) aria-current="page" @endif style="border:1px solid {{ $__active === $cat ? 'var(--signal)' : 'var(--line-strong)' }}; border-radius:999px; padding:6px 14px; font-size:.82rem; color:{{ $__active === $cat ? 'var(--signal-ink)' : 'var(--ink-soft)' }}; font-weight:{{ $__active === $cat ? 700 : 400 }};">{{ \App\Models\MarketplaceListing::categoryLabel($cat) }}</a>
      @endforeach
    </div>

    @if($listings->isEmpty())
      <p style="color:var(--ink-faint);">No products yet. Check back soon.</p>
    @else
      <div id="m-grid" class="grid gap-5" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); scroll-margin-top:90px;">
        @foreach($listings as $listing)
          @php
            $poster = $listing->posterUrl();
            $cardDemo = $listing->category === '3d-scenes' ? null : ($listing->demo_type === 'static'
              ? asset('storage/'.$listing->demo_path)
              : ($listing->demo_type === 'external' ? $listing->demo_url : null));
          @endphp
          <a href="{{ route('marketplace.show', $listing) }}" class="m-card" style="display:block; text-decoration:none;">
            <div class="m-thumb">
              <span class="m-live" style="position:absolute; top:10px; left:10px;"><span class="blink"></span>LIVE</span>
              @if($poster)
                {{-- Poster shown in grid; hover shows live preview for non-3D --}}
                <img class="m-poster" src="{{ $poster }}" alt="Preview of {{ $listing->title }}" width="600" height="375" loading="lazy" decoding="async">
              @else
                <span class="m-display m-title">{{ Str::limit($listing->title, 18) }}</span>
              @endif
              @if($cardDemo)
                <iframe class="m-demo" data-src="{{ $cardDemo }}" title="Live preview of {{ $listing->title }}" sandbox="allow-scripts allow-same-origin" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
                <span class="m-hint">Hover = live · click to open</span>
              @elseif($poster)
                <span class="m-hint" style="opacity:1;">{{ $listing->category === '3d-scenes' ? '3D · ' : '' }}open for live demo</span>
              @endif
            </div>
            <div style="padding:13px 15px;">
              <div style="font-weight:700; color:var(--ink);">{{ $listing->title }}</div>
              <div style="font-size:.8rem; color:var(--ink-faint); margin-top:2px;">{{ Str::limit($listing->tagline, 48) }}</div>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                <span class="m-display" style="color:var(--ink);">{{ $listing->cheapestPrice() <= 0 ? 'Free' : ($listing->tiers->where('status', 'published')->count() > 1 ? 'from ' : '').'$'.rtrim(rtrim(number_format($listing->cheapestPrice(), 2), '0'), '.') }}</span>
                @php($__proof = array_filter([$listing->hasReviews() ? '★ '.number_format($listing->rating, 1) : null, $listing->hasSales() ? $listing->sales_count.' '.Str::plural('sale', $listing->sales_count) : null]))
                <span style="font-size:.75rem; color:var(--ink-faint);">{{ $__proof ? implode(' · ', $__proof) : 'New' }}</span>
              </div>
            </div>
          </a>
        @endforeach
      </div>
      <div class="mt-8">{{ $listings->links() }}</div>
    @endif
  </div>
</div>

<style>
  #ngb-market .m-thumb .m-poster{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .5s cubic-bezier(.16,1,.3,1); }
  #ngb-market .m-card:hover .m-thumb .m-poster{ transform:scale(1.04); }
  #ngb-market .m-thumb:has(.m-poster){ height:168px; background:#0b0f1c; }
  @media (prefers-reduced-motion: reduce){ #ngb-market .m-thumb .m-poster{ transition:none; } }

  #ngb-market .m-hero{ display:grid; grid-template-columns:1fr; gap:28px; align-items:center; }
  @media (min-width:900px){ #ngb-market .m-hero{ grid-template-columns:1.05fr 1fr; gap:48px; } }
  #ngb-market .m-cta{ display:inline-flex; align-items:center; padding:11px 20px; border-radius:999px; background:var(--signal); color:#fff; font-weight:700; font-size:.92rem; text-decoration:none; border:1px solid var(--signal); transition:transform .15s, box-shadow .15s; }
  #ngb-market .m-cta:hover{ transform:translateY(-2px); box-shadow:0 12px 30px oklch(0.4 0.15 255 / .3); }
  #ngb-market .m-cta-ghost{ background:transparent; color:var(--ink); border-color:var(--line-strong); }
  #ngb-market .m-frame{ display:block; border:1px solid var(--line-strong); border-radius:14px; overflow:hidden; background:#0b1020; box-shadow:0 30px 80px oklch(0.2 0.03 264 / .22); text-decoration:none; }
  #ngb-market .m-frame-bar{ display:flex; align-items:center; gap:6px; padding:9px 12px; background:oklch(0.22 0.03 264); }
  #ngb-market .m-frame-bar i{ width:9px; height:9px; border-radius:50%; background:oklch(0.5 0.03 264); }
  #ngb-market .m-frame-body{ display:block; position:relative; aspect-ratio:16/10; overflow:hidden; }
  #ngb-market .m-frame-body iframe{ position:absolute; top:0; left:0; width:calc(250% + 30px); height:250%; border:0; transform:scale(.4); transform-origin:top left; pointer-events:none; background:#0b1020; }
  #ngb-market .m-steps{ list-style:none; margin:0 0 34px; padding:0; display:grid; grid-template-columns:1fr; gap:12px; }
  @media (min-width:700px){ #ngb-market .m-steps{ grid-template-columns:repeat(3,1fr); } }
  #ngb-market .m-steps li{ display:flex; gap:12px; align-items:flex-start; padding:14px 16px; border:1px solid var(--line); border-radius:12px; background:var(--surface); }
  #ngb-market .m-steps b{ flex:0 0 28px; height:28px; border-radius:50%; display:grid; place-items:center; background:var(--signal-wash); color:var(--signal-ink); font-size:.82rem; }
  #ngb-market .m-steps strong{ display:block; color:var(--ink); font-size:.92rem; }
  #ngb-market .m-steps span span, #ngb-market .m-steps li > span{ color:var(--ink-soft); font-size:.84rem; line-height:1.45; }
  #ngb-market .m-steps li > span strong{ margin-bottom:2px; }
</style>
<script>
(function () {
  // Lazy-load each card's live demo the first time it is hovered/focused.
  function load(card) {
    var f = card.querySelector('.m-demo');
    if (f && !f.getAttribute('src') && f.dataset.src) { f.setAttribute('src', f.dataset.src); }
  }
  document.querySelectorAll('#ngb-market .m-card').forEach(function (card) {
    card.addEventListener('mouseenter', function () { load(card); }, { once: true });
    card.addEventListener('focusin', function () { load(card); }, { once: true });
  });
})();
</script>
@endsection
