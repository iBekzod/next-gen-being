@extends('layouts.app')

@section('title', 'Refund Policy - ' . setting('site_name'))
@section('description', 'Understand how refunds and cancellations work for your membership')
@section('canonical', route('refund'))
@section('share_image', setting('default_meta_image', setting('site_logo', asset('uploads/logo.png'))))
@section('author', setting('company_name', setting('site_name', config('app.name'))))
@section('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Refund Policy</h1>

        <div class="prose prose-lg dark:prose-invert max-w-none">
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Last updated: {{ now()->format('F j, Y') }}
            </p>

            <p>
                This Refund Policy explains how membership fees for {{ setting('company_name', 'NextGenBeing') }} ("{{ setting('site_name') }}", "we", "our"), a sole proprietorship, are handled. Because we deliver immediately accessible digital resources and benefits, we offer refunds as described below.
            </p>

            <h2>1. Free Trial</h2>
            <p>
                Every new subscription begins with a 7-day free trial. You can cancel at any time during the trial from your account portal and you will not be charged. If you do not cancel before the trial ends, the first billing cycle will start automatically.
            </p>

            <h2>2. 14-Day Money-Back Guarantee</h2>
            <p>
                All subscription plans (monthly and annual) are eligible for a full refund within 14 days of the initial purchase. Simply contact us at {{ 'support@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'https://nextgenbeing.com')) }} within 14 days of your purchase, and we will issue a complete refund to your original payment method.
            </p>

            <h2>3. After 14 Days</h2>
            <p>
                After the 14-day refund period, you may cancel your subscription at any time. You will retain access until the end of your current billing period, but no refunds will be provided for the remaining time. Your subscription will not automatically renew after cancellation.
            </p>

            <h2>4. Custom or Enterprise Arrangements</h2>
            <p>
                For custom subscription arrangements negotiated directly with our team, the refund terms will be specified in your agreement. Unless otherwise stated in writing, the standard 14-day refund policy applies.
            </p>

            <h2>5. Duplicate Charges or Billing Errors</h2>
            <p>
                If you believe you were incorrectly charged, email us within 30 days at {{ 'billing@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'https://nextgenbeing.com')) }} with a copy of your receipt. Verified duplicate charges or processing errors will be refunded in full to the original payment method.
            </p>

            <h2>6. How to Cancel</h2>
            <p>
                You can manage or cancel your subscription anytime from the <a href="{{ route('subscription.manage') }}">subscription management dashboard</a>. If you enrolled through a partner platform, please cancel through that platform to ensure the change is reflected correctly.
            </p>

            <h2>7. Contact</h2>
            <p>
                Questions about this policy can be sent to:
            </p>
            <ul>
                <li>Email: {{ 'support@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'https://nextgenbeing.com')) }}</li>
                <li>Address: {{ setting('company_address', '240 Bogishamol Street, Yunusabad District, Tashkent 100058, Uzbekistan') }}</li>
            </ul>
        </div>
    </div>
</div>
@endsection
