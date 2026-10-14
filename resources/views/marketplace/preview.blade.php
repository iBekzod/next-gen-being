@php
    $price = $listing->cheapestPrice();
    $priceLabel = $price > 0 ? 'from $'.rtrim(rtrim(number_format($price, 2), '0'), '.') : 'Free';
    $listingUrl = route('marketplace.show', $listing->slug);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $listing->title }} · Live preview · NextGenBeing</title>
    <meta name="robots" content="noindex">
    <link rel="canonical" href="{{ $listingUrl }}">
    <style>
        :root { --bar: 56px; }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; background: #0b0f1a; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; }
        .bar { height: var(--bar); display: flex; align-items: center; gap: 12px; padding: 0 14px;
               background: #0f1524; border-bottom: 1px solid rgba(255,255,255,.08); color: #e7ecf7; }
        .bar a { color: inherit; text-decoration: none; }
        .back { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 9px;
                border: 1px solid rgba(255,255,255,.14); font-size: .86rem; white-space: nowrap; }
        .back:hover { background: rgba(255,255,255,.06); }
        .brand { font-weight: 700; font-size: .9rem; opacity: .85; white-space: nowrap; }
        .title { flex: 1; min-width: 0; font-size: .9rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .price { font-size: .82rem; color: #9fb0d4; white-space: nowrap; }
        .cta { padding: 8px 14px; border-radius: 9px; background: #2f6bff; color: #fff !important; font-weight: 700; font-size: .86rem; white-space: nowrap; }
        .cta:hover { filter: brightness(1.08); }
        .rm { font-size: .8rem; color: #9fb0d4 !important; white-space: nowrap; }
        iframe { display: block; width: 100%; height: calc(100% - var(--bar)); border: 0; background: #fff; }
        @media (max-width: 640px) {
            .bar { gap: 8px; padding: 0 10px; }
            .brand, .rm, .price { display: none; }
            .back span { display: none; }
            .title { font-size: .82rem; }
            .cta { padding: 7px 11px; font-size: .8rem; }
        }
    </style>
</head>
<body>
    <div class="bar">
        <a class="back" href="{{ $listingUrl }}" aria-label="Back to {{ $listing->title }}">&#8592; <span>Back</span></a>
        <a class="brand" href="{{ route('marketplace.index') }}">NextGenBeing</a>
        <div class="title" title="{{ $listing->title }}">{{ $listing->title }}</div>
        <div class="price">{{ $priceLabel }}</div>
        <a class="cta" href="{{ $listingUrl }}#tiers">{{ $price > 0 ? 'Buy now' : 'Get it free' }}</a>
        <a class="rm" href="{{ $rawUrl }}" title="Open without this bar">Remove frame &#215;</a>
    </div>
    <iframe src="{{ $frameUrl }}" title="Live preview of {{ $listing->title }}" allow="fullscreen"></iframe>
</body>
</html>
