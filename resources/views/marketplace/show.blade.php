@extends('layouts.app')
@section('title', $listing->title.' — live demo & source')
@section('description', Str::limit(strip_tags($listing->tagline ?: $listing->description), 155))
@section('og_type', 'product')

@section('content')
@php
    $__tierPrices = $listing->tiers->pluck('price')->map(fn ($p) => (float) $p);
    $__productLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $listing->title,
        'description' => Str::limit(strip_tags($listing->description ?: $listing->tagline), 300),
        'category' => $listing->category,
        'brand' => ['@type' => 'Brand', 'name' => 'NextGenBeing'],
        'offers' => $__tierPrices->isNotEmpty() ? [
            '@type' => 'AggregateOffer',
            'priceCurrency' => 'USD',
            'lowPrice' => number_format($__tierPrices->min(), 2, '.', ''),
            'highPrice' => number_format($__tierPrices->max(), 2, '.', ''),
            'offerCount' => $__tierPrices->count(),
            'availability' => 'https://schema.org/InStock',
            'url' => route('marketplace.show', $listing),
        ] : null,
        'aggregateRating' => $listing->reviews_count > 0 ? [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $listing->rating,
            'reviewCount' => (string) $listing->reviews_count,
        ] : null,
    ]);
@endphp
<script type="application/ld+json">{!! json_encode($__productLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@include('marketplace.partials.tokens')
<style>
  #ngb-market .win{ background:var(--surface); border:1px solid var(--line-strong); border-radius:16px; overflow:hidden;
    box-shadow:0 24px 60px oklch(0.2 0.03 264 / .18); }
  #ngb-market .win-bar{ display:flex; align-items:center; gap:12px; padding:11px 14px; background:var(--surface-2);
    border-bottom:1px solid var(--line); }
  #ngb-market .lights{ display:flex; gap:8px; } #ngb-market .lights span{ width:13px; height:13px; border-radius:50%; display:block; }
  #ngb-market .lights .r{ background:#ff5f57; } #ngb-market .lights .y{ background:#febc2e; } #ngb-market .lights .g{ background:#28c840; }
  #ngb-market .addr{ flex:1; text-align:center; font-family:ui-monospace,monospace; font-size:.78rem; color:var(--ink-soft); }
  #ngb-market .win-body{ height:460px; background:#0f1420; transition:height .35s cubic-bezier(.16,1,.3,1); }
  #ngb-market .win-body iframe{ width:100%; height:100%; border:0; display:block; }
  #ngb-market .lights span{ cursor:pointer; }
  #ngb-market .win-tools{ display:flex; gap:6px; }
  #ngb-market .win-tools button{ width:30px; height:28px; border-radius:8px; border:1px solid var(--line);
    background:var(--paper); color:var(--ink-soft); cursor:pointer; font-size:.9rem; line-height:1; display:grid; place-items:center; }
  #ngb-market .win-tools button:hover{ color:var(--signal-ink); border-color:var(--signal); }
  #ngb-market .win-tools button:focus-visible{ outline:2px solid var(--signal); outline-offset:2px; }
  #ngb-market .win.is-min .win-body{ height:0; }
  #ngb-market .win.is-full{ position:fixed; inset:3vh 3vw; z-index:60; margin:0; box-shadow:0 40px 120px oklch(0.1 0.03 264 / .6); }
  #ngb-market .win.is-full .win-body{ height:calc(100% - 51px); }
  #ngb-market .ngb-backdrop{ position:fixed; inset:0; background:oklch(0.14 0.03 264 / .55); backdrop-filter:blur(3px);
    z-index:59; opacity:0; pointer-events:none; transition:opacity .3s; }
  #ngb-market .ngb-backdrop.show{ opacity:1; pointer-events:auto; }
  @media (prefers-reduced-motion: reduce){
    #ngb-market .win-body{ transition:none; } #ngb-market .ngb-backdrop{ transition:none; }
  }
  #ngb-market .tier{ border:1.5px solid var(--line); border-radius:12px; padding:13px 15px; display:flex; justify-content:space-between; align-items:center; gap:10px; }
  #ngb-market .tier .t-price{ font-family:var(--font-display); font-weight:800; font-size:1.1rem; }
  #ngb-market .buy-btn{ width:100%; background:var(--signal); color:#fff; border:none; border-radius:10px; padding:11px; font-weight:700; cursor:pointer; }
  #ngb-market .buy-btn:hover{ filter:brightness(1.06); }
</style>
<div id="ngb-market">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <p style="font-size:.78rem; color:var(--ink-faint); margin-bottom:12px;">
      <a href="{{ route('marketplace.index') }}" style="color:var(--signal-ink);">Marketplace</a> / {{ ucfirst($listing->category) }} / {{ $listing->title }}
    </p>
    <h1 class="m-display" style="font-size:clamp(1.6rem,3.5vw,2.3rem); line-height:1.05; margin:0 0 6px;">{{ $listing->title }}</h1>
    <p style="color:var(--ink-soft); margin:0 0 20px;">{{ $listing->tagline }} · by <b style="color:var(--ink);">{{ $listing->seller->name }}</b></p>

    {{-- LIVE DEMO WINDOW --}}
    <div class="ngb-backdrop" data-ngb-backdrop></div>
    <div class="win mb-3" data-ngb-win>
      <div class="win-bar">
        <div class="lights">
          <span class="r" data-ngb-min title="Minimize" role="button" aria-label="Minimize demo"></span>
          <span class="y" data-ngb-min title="Minimize" role="button" aria-label="Minimize demo"></span>
          <span class="g" data-ngb-full title="Fullscreen" role="button" aria-label="Fullscreen demo"></span>
        </div>
        <div class="addr">🔒 {{ Str::slug($listing->title) }}-demo.nextgenbeing.com</div>
        <span class="m-live" style="color:var(--good);"><span class="blink" style="background:var(--good);"></span>LIVE</span>
        @if($demoUrl)
          <div class="win-tools">
            <button type="button" data-ngb-min aria-label="Minimize demo" title="Minimize">&#x2015;</button>
            <button type="button" data-ngb-full aria-label="Toggle fullscreen" title="Fullscreen">&#x26F6;</button>
            <a href="{{ $demoUrl }}" target="_blank" rel="noopener" role="button" aria-label="Open demo in new tab" title="Open in new tab"
               style="text-decoration:none; width:30px; height:28px; border-radius:8px; border:1px solid var(--line); background:var(--paper); color:var(--ink-soft); display:grid; place-items:center; font-size:.9rem;">&#x2197;</a>
          </div>
        @endif
      </div>
      <div class="win-body">
        @if($demoUrl)
          <iframe src="{{ $demoUrl }}" title="Live demo of {{ $listing->title }}" sandbox="allow-scripts allow-same-origin" loading="lazy"></iframe>
        @else
          <div style="height:100%; display:grid; place-items:center; color:#8593b5;">Live demo coming soon</div>
        @endif
      </div>
    </div>
    <p style="text-align:center; font-size:.78rem; color:var(--ink-faint); margin-bottom:28px;">This is the live product, not a screenshot. Click around, or hit fullscreen &#x26F6;, before you buy.</p>

    <div class="grid gap-8" style="grid-template-columns:1.5fr 1fr;">
      <div>
        <h2 style="font-size:1.2rem; font-weight:700; margin:0 0 10px;">What you get</h2>
        <div style="color:var(--ink-soft); line-height:1.7;">{!! nl2br(e($listing->description)) !!}</div>
      </div>

      {{-- TIER PURCHASE PANEL --}}
      <div>
        <div style="background:var(--surface); border:1px solid var(--line-strong); border-radius:14px; padding:16px;">
          <p style="font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--ink-faint); margin:0 0 12px;">Choose your tier</p>
          <div style="display:flex; flex-direction:column; gap:10px;">
            @forelse($listing->tiers as $tier)
              <div class="tier">
                <div>
                  <div style="font-weight:700; text-transform:capitalize;">{{ $tier->tier ?? $tier->type }}</div>
                  <div style="font-size:.78rem; color:var(--ink-faint);">{{ Str::limit($tier->short_description ?? $tier->description, 42) }}</div>
                </div>
                <div style="text-align:right;">
                  <div class="t-price">{{ $tier->is_free ? 'Free' : '$'.rtrim(rtrim(number_format($tier->price,2),'0'),'.') }}</div>
                </div>
              </div>
              @if($tier->is_free || $tier->file_path)
                <form method="POST" action="{{ route('digital-products.purchase', $tier) }}">
                  @csrf
                  <button type="submit" class="buy-btn">
                    {{ $tier->is_free ? 'Get '.($tier->tier ?? 'free') : 'Buy '.($tier->tier ?? 'now').' · $'.rtrim(rtrim(number_format($tier->price,2),'0'),'.') }}
                  </button>
                </form>
              @else
                <button type="button" class="buy-btn" disabled
                  style="opacity:.5; cursor:not-allowed; background:var(--line-strong);">Coming soon</button>
              @endif
            @empty
              <p style="color:var(--ink-faint);">Tiers coming soon.</p>
            @endforelse
          </div>
          <p style="text-align:center; font-size:.74rem; color:var(--ink-faint); margin-top:12px;">🔒 Secure checkout · instant download</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var win = document.querySelector('[data-ngb-win]');
  if (!win) return;
  var backdrop = document.querySelector('[data-ngb-backdrop]');

  function minimize() { win.classList.remove('is-full'); if (backdrop) backdrop.classList.remove('show'); win.classList.toggle('is-min'); }
  function fullscreen() {
    win.classList.remove('is-min');
    var on = win.classList.toggle('is-full');
    if (backdrop) backdrop.classList.toggle('show', on);
    document.body.style.overflow = on ? 'hidden' : '';
  }
  function exitFull() {
    if (!win.classList.contains('is-full')) return;
    win.classList.remove('is-full');
    if (backdrop) backdrop.classList.remove('show');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-ngb-min]').forEach(function (el) {
    el.addEventListener('click', function (e) { e.preventDefault(); minimize(); });
  });
  document.querySelectorAll('[data-ngb-full]').forEach(function (el) {
    el.addEventListener('click', function (e) { e.preventDefault(); fullscreen(); });
  });
  if (backdrop) backdrop.addEventListener('click', exitFull);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') exitFull(); });
})();
</script>
@endsection
