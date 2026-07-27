@extends('layouts.app')
@section('title', $listing->title.' — live demo & source')

@section('content')
@include('marketplace.partials.tokens')
<style>
  #ngb-market .win{ background:var(--surface); border:1px solid var(--line-strong); border-radius:16px; overflow:hidden;
    box-shadow:0 24px 60px oklch(0.2 0.03 264 / .18); }
  #ngb-market .win-bar{ display:flex; align-items:center; gap:12px; padding:11px 14px; background:var(--surface-2);
    border-bottom:1px solid var(--line); }
  #ngb-market .lights{ display:flex; gap:8px; } #ngb-market .lights span{ width:13px; height:13px; border-radius:50%; display:block; }
  #ngb-market .lights .r{ background:#ff5f57; } #ngb-market .lights .y{ background:#febc2e; } #ngb-market .lights .g{ background:#28c840; }
  #ngb-market .addr{ flex:1; text-align:center; font-family:ui-monospace,monospace; font-size:.78rem; color:var(--ink-soft); }
  #ngb-market .win-body{ height:460px; background:#0f1420; }
  #ngb-market .win-body iframe{ width:100%; height:100%; border:0; display:block; }
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
    <div class="win mb-3">
      <div class="win-bar">
        <div class="lights"><span class="r"></span><span class="y"></span><span class="g"></span></div>
        <div class="addr">🔒 {{ Str::slug($listing->title) }}-demo.nextgenbeing.com</div>
        <span class="m-live" style="color:var(--good);"><span class="blink" style="background:var(--good);"></span>LIVE</span>
      </div>
      <div class="win-body">
        @if($demoUrl)
          <iframe src="{{ $demoUrl }}" title="Live demo of {{ $listing->title }}" sandbox="allow-scripts allow-same-origin" loading="lazy"></iframe>
        @else
          <div style="height:100%; display:grid; place-items:center; color:#8593b5;">Live demo coming soon</div>
        @endif
      </div>
    </div>
    <p style="text-align:center; font-size:.78rem; color:var(--ink-faint); margin-bottom:28px;">This is the live product, not a screenshot. Click around before you buy.</p>

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
              <form method="POST" action="{{ route('digital-products.purchase', $tier) }}">
                @csrf
                <button type="submit" class="buy-btn">
                  {{ $tier->is_free ? 'Get '.($tier->tier ?? 'free') : 'Buy '.($tier->tier ?? 'now').' · $'.rtrim(rtrim(number_format($tier->price,2),'0'),'.') }}
                </button>
              </form>
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
@endsection
