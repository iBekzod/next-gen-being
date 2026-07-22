@php
    $leadMagnet = $links['lead_magnet'] ?? null;
    $leadMagnetUrl = $leadMagnet
        ? (\Illuminate\Support\Str::startsWith($leadMagnet, 'http') ? $leadMagnet : $appUrl . $leadMagnet)
        : null;
@endphp
@extends('emails.newsletter.layouts.base')

@section('content')
<h1>You're in. Welcome 👋</h1>

<p>Thanks for confirming — you're now on the {{ config('app.name') }} list. I write for developers and builders who want to use AI to ship real things, not chase hype.</p>

<p>As promised, here's your free resource to kick things off:</p>

@if($leadMagnetUrl)
<table role="presentation" cellspacing="0" cellpadding="0" style="margin: 24px 0;">
    <tr>
        <td>
            <a href="{{ $leadMagnetUrl }}" class="button button-large">📘 Get the AI-Assisted Developer's Field Guide</a>
        </td>
    </tr>
</table>
<p style="font-size: 14px; color: #718096;">A practical, no-fluff guide: the workflow, the prompts, and the tools I use to build faster with AI.</p>
@endif

<div class="divider"></div>

<h3>What to expect from me</h3>
<ul style="margin: 10px 0; padding-left: 20px;">
    <li style="margin-bottom: 8px;">Deep, tested tutorials — not thin AI filler.</li>
    <li style="margin-bottom: 8px;">The tools and workflows I actually use (and pay for).</li>
    <li style="margin-bottom: 8px;">Occasionally, something you can buy that saves you real time.</li>
</ul>

<p>Over the next few days I'll send a short series to get you the most out of this. Keep an eye out for the next one.</p>

<p>One quick favour: <strong>reply and tell me what you're building right now.</strong> I read every reply, and it shapes what I write next.</p>

<p>— The {{ config('app.name') }} team</p>
@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    You're receiving this because you confirmed your subscription.
    <a href="{{ $unsubscribeUrl }}" style="color: #a0aec0;">Unsubscribe</a>
</p>
@endsection
