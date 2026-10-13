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
    <header class="mb-8">
      <p style="color:var(--signal-ink); font-weight:600; letter-spacing:.14em; text-transform:uppercase; font-size:.72rem;">Live product marketplace</p>
      <h1 class="m-display" style="font-size:clamp(1.9rem,4vw,2.7rem); line-height:1.05; margin:.3rem 0;">Try it live. Then buy.</h1>
      <p style="color:var(--ink-soft); max-width:60ch;">Every product is a real, running app, not a screenshot. Open the demo, click around, then buy the prompt plan, the design, or the full code.</p>
    </header>

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
      <div class="grid gap-5" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
        @foreach($listings as $listing)
          @php
            $poster = $listing->posterUrl();
            $cardDemo = $poster ? null : ($listing->demo_type === 'static'
              ? asset('storage/'.$listing->demo_path)
              : ($listing->demo_type === 'external' ? $listing->demo_url : null));
          @endphp
          <a href="{{ route('marketplace.show', $listing) }}" class="m-card" style="display:block; text-decoration:none;">
            <div class="m-thumb">
              <span class="m-live" style="position:absolute; top:10px; left:10px;"><span class="blink"></span>LIVE</span>
              @if($poster)
                {{-- WebGL scenes show a static poster in the grid; the real scene only loads on the item page --}}
                <img class="m-poster" src="{{ $poster }}" alt="Preview of {{ $listing->title }}" width="600" height="375" loading="lazy" decoding="async">
                <span class="m-hint" style="opacity:1;">3D · open for live demo</span>
              @else
              <span class="m-display m-title">{{ Str::limit($listing->title, 18) }}</span>
              @endif
              @if($cardDemo)
                <iframe class="m-demo" data-src="{{ $cardDemo }}" title="Live preview of {{ $listing->title }}" sandbox="allow-scripts allow-same-origin" loading="lazy" tabindex="-1" aria-hidden="true"></iframe>
                <span class="m-hint">Hover = live · click to open</span>
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
