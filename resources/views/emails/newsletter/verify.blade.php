@extends('emails.newsletter.layouts.base')

@section('content')
<h1>Confirm Your Subscription</h1>

<p>Hi there!</p>

<p>Thanks for subscribing to {{ config('app.name') }}'s newsletter. We're excited to share our best content with you!</p>

<p>To complete your subscription, please confirm your email address by clicking the button below:</p>

<table role="presentation" cellspacing="0" cellpadding="0" style="margin: 30px 0;">
    <tr>
        <td>
            <a href="{{ $verifyUrl }}" class="button button-large">
                Confirm Subscription
            </a>
        </td>
    </tr>
</table>

<div style="background-color: #f7fafc; border-left: 4px solid #667eea; padding: 20px; border-radius: 6px; margin: 30px 0;">
    <h3 style="margin-top: 0;">What you'll receive:</h3>
    <ul style="margin: 10px 0; padding-left: 20px;">
        <li style="margin-bottom: 10px;">Weekly digest of our best articles</li>
        <li style="margin-bottom: 10px;">Curated content based on your interests</li>
        <li style="margin-bottom: 10px;">Exclusive insights and early access to new content</li>
        <li style="margin-bottom: 10px;">Tips, tutorials, and industry trends</li>
    </ul>
</div>

<p style="font-size: 14px; color: #718096;">
    <strong>Frequency:</strong> You'll receive emails {{ $subscription->frequency }}.
</p>

<p style="font-size: 14px; color: #a0aec0; margin-top: 30px;">
    If you didn't subscribe to this newsletter, you can safely ignore this email.
</p>

<div class="divider"></div>

<p style="font-size: 13px; color: #a0aec0; text-align: center;">
    Having trouble with the button? Copy and paste this link into your browser:<br>
    <a href="{{ $verifyUrl }}" style="color: #667eea; word-break: break-all;">{{ $verifyUrl }}</a>
</p>
@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    This verification link will expire in 24 hours.
</p>
@endsection
