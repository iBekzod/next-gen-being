@php
    $toolUrl = $links['featured_tool'] ?? null;
    $toolName = $links['featured_tool_name'] ?? null;
@endphp
@extends('emails.newsletter.layouts.base')

@section('content')
<h1>The AI tools I actually pay for</h1>

<p>There are a thousand AI tools. I pay for a handful. Here's how I think about it: a tool earns its subscription only if it saves me more time (or makes me more money) than it costs. Everything else gets cancelled.</p>

@if($toolUrl && $toolName)
<div class="post-card post-card-featured">
    <h3>My current pick: {{ $toolName }}</h3>
    <p>This is the one I reach for most often right now. It's paid its way many times over.</p>
    <a href="{{ $toolUrl }}" class="button">Try {{ $toolName }}</a>
    <p style="font-size: 12px; color: #a0aec0; margin-top: 12px;">This is an affiliate link — if you sign up, I may earn a commission at no extra cost to you. I only recommend tools I actually use.</p>
</div>
@else
<p>I'm putting together a short, honest breakdown of my current stack — what each tool does, what it costs, and where it's worth it. It's coming soon on the blog.</p>
<table role="presentation" cellspacing="0" cellpadding="0" style="margin: 16px 0;">
    <tr>
        <td>
            <a href="{{ $appUrl }}/posts" class="button">See the latest on the blog</a>
        </td>
    </tr>
</table>
@endif

<div class="divider"></div>

<p>Rule of thumb: don't collect tools, collect <em>workflows</em>. One tool you've mastered beats ten you've dabbled in.</p>

<p>What's in your stack? Reply and let me know — I'm always looking for the next one worth paying for.</p>

<p>— The {{ config('app.name') }} team</p>
@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    <a href="{{ $unsubscribeUrl }}" style="color: #a0aec0;">Unsubscribe</a>
</p>
@endsection
