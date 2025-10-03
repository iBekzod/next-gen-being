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
                This Refund Policy explains how membership fees for {{ setting('company_name', 'NextGen Being LLC') }} ("{{ setting('site_name') }}", "we", "our") are handled. Because we deliver immediately accessible digital research, resources, and community benefits, refunds are limited to the scenarios described below.
            </p>

            <h2>1. Free Trial</h2>
            <p>
                Every new subscription begins with a 7-day free trial. You can cancel at any time during the trial from your account portal and you will not be charged. If you do not cancel before the trial ends, the first billing cycle will start automatically.
            </p>

            <h2>2. Monthly Memberships</h2>
            <p>
                Monthly plans are billed in advance. When you cancel, you will retain premium access until the end of the current billing period. Because all premium issues, playbooks, and downloads become available instantly, we do not pro-rate or refund the remainder of the month once the billing cycle has started.
            </p>

            <h2>3. Annual Memberships</h2>
            <p>
                Annual subscriptions are eligible for a full refund within the first 14 days of the initial charge if less than three premium issues have been downloaded. After 14 days, cancellations will stop future renewals and you will continue to have access until the end of the paid term. Please reach out to us to initiate an annual refund so we can verify eligibility.
            </p>

            <h2>4. Enterprise Plans</h2>
            <p>
                Enterprise agreements follow the custom terms documented in the signed order form or enterprise pricing sheet. Unless otherwise agreed in writing, enterprise invoices are non-refundable once onboarding has begun. If you need adjustments to seat counts or billing cadence, contact your account manager before the next renewal date.
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
