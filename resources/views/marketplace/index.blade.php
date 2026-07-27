@extends('layouts.app')
@section('title', 'Marketplace — live products you can try before you buy')

@section('content')
@include('marketplace.partials.tokens')
<div id="ngb-market">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <header class="mb-8">
      <p style="color:var(--signal-ink); font-weight:600; letter-spacing:.14em; text-transform:uppercase; font-size:.72rem;">Live product marketplace</p>
      <h1 class="m-display" style="font-size:clamp(1.9rem,4vw,2.7rem); line-height:1.05; margin:.3rem 0;">Try it live. Then buy.</h1>
      <p style="color:var(--ink-soft); max-width:60ch;">Every product is a real, running app, not a screenshot. Open the demo, click around, then buy the prompt plan, the design, or the full code.</p>
    </header>

    <div class="mb-6 flex flex-wrap gap-2">
      <a href="{{ route('marketplace.index') }}" style="border:1px solid var(--line-strong); border-radius:999px; padding:6px 14px; font-size:.82rem; color:var(--ink-soft);">All</a>
      @foreach($categories as $cat)
        <a href="{{ route('marketplace.index', ['category' => $cat]) }}" style="border:1px solid var(--line-strong); border-radius:999px; padding:6px 14px; font-size:.82rem; color:var(--ink-soft);">{{ ucfirst($cat) }}</a>
      @endforeach
    </div>

    @if($listings->isEmpty())
      <p style="color:var(--ink-faint);">No products yet. Check back soon.</p>
    @else
      <div class="grid gap-5" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr));">
        @foreach($listings as $listing)
          <a href="{{ route('marketplace.show', $listing) }}" class="m-card" style="display:block; text-decoration:none;">
            <div style="height:140px; position:relative; display:grid; place-items:center; background:linear-gradient(135deg, oklch(0.35 0.13 264), var(--signal));">
              <span class="m-live" style="position:absolute; top:10px; left:10px;"><span class="blink"></span>LIVE</span>
              <span class="m-display" style="color:#fff; font-size:1.35rem;">{{ Str::limit($listing->title, 18) }}</span>
            </div>
            <div style="padding:13px 15px;">
              <div style="font-weight:700; color:var(--ink);">{{ $listing->title }}</div>
              <div style="font-size:.8rem; color:var(--ink-faint); margin-top:2px;">{{ Str::limit($listing->tagline, 48) }}</div>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                <span class="m-display" style="color:var(--ink);">from ${{ rtrim(rtrim(number_format($listing->cheapestPrice(), 2), '0'), '.') }}</span>
                <span style="font-size:.75rem; color:var(--ink-faint);">★ {{ number_format($listing->rating, 1) }} · {{ $listing->sales_count }} sales</span>
              </div>
            </div>
          </a>
        @endforeach
      </div>
      <div class="mt-8">{{ $listings->links() }}</div>
    @endif
  </div>
</div>
@endsection
