@php
    $productUrl = $links['flagship_product'] ?? null;
    $productFull = $productUrl
        ? (\Illuminate\Support\Str::startsWith($productUrl, 'http') ? $productUrl : $appUrl . $productUrl)
        : null;
@endphp
@extends('emails.newsletter.layouts.base')

@section('content')
<h1>A quick question</h1>

<p>You've been on the list for a few days now — thank you for reading. Before anything else, the most useful thing you can do (and the most useful thing for me) is simple:</p>

<p style="font-size: 18px; color: #2d3748;"><strong>What are you trying to build or learn right now?</strong></p>

<p>Hit reply with one line. No wrong answers. I read every single one, and it directly shapes what I create next.</p>

@if($productFull)
<div class="divider"></div>

<div class="post-card post-card-featured">
    <h3>And if you want a shortcut…</h3>
    <p>I packaged the exact system I use into something you can grab and apply today — no waiting, no guesswork.</p>
    <a href="{{ $productFull }}" class="button">Take a look</a>
</div>
@else
<div class="divider"></div>

<p>I'm building a small library of practical, paid resources — the systems and templates I use, packaged so you can apply them in an afternoon. Reply "keep me posted" and you'll be first to know when the first one drops.</p>

<table role="presentation" cellspacing="0" cellpadding="0" style="margin: 16px 0;">
    <tr>
        <td>
            <a href="{{ $appUrl }}/resources" class="button">Browse resources</a>
        </td>
    </tr>
</table>
@endif

<p>Either way — glad you're here. Talk soon.</p>

<p>— The {{ config('app.name') }} team</p>
@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    <a href="{{ $unsubscribeUrl }}" style="color: #a0aec0;">Unsubscribe</a>
</p>
@endsection
