@extends('emails.newsletter.layouts.base')

@section('content')
<h1>Start here</h1>

<p>Yesterday you grabbed the Field Guide. Today, three quick things to get value fast.</p>

<h3>1. Browse the deep-dives</h3>
<p>Most of what I publish is long-form, tested tutorials — the kind you keep open in a tab while you build.</p>
<table role="presentation" cellspacing="0" cellpadding="0" style="margin: 16px 0;">
    <tr>
        <td>
            <a href="{{ $appUrl }}/posts" class="button">Browse the latest guides</a>
        </td>
    </tr>
</table>

<h3>2. Save this email</h3>
<p>Newsletters get buried. Drag this into your primary inbox (or star it) so the next ones actually reach you — that's the single biggest thing that keeps you getting value.</p>

<h3>3. Tell me your #1 challenge</h3>
<p>Just hit reply with the one thing slowing you down right now. I use these to decide what to write and build next — and you'll often get a direct answer back.</p>

<div class="divider"></div>

<p>Tomorrow: the small set of AI tools I actually pay for out of my own pocket, and exactly what I use each one for.</p>

<p>— The {{ config('app.name') }} team</p>
@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    <a href="{{ $unsubscribeUrl }}" style="color: #a0aec0;">Unsubscribe</a>
</p>
@endsection
