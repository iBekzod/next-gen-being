@extends('layouts.app')

@section('title', 'Terms of Service - ' . setting('site_name'))
@section('description', 'Terms and conditions for using our service')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Terms of Service</h1>

        <div class="prose prose-lg dark:prose-invert max-w-none">
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Last updated: {{ now()->format('F j, Y') }}
            </p>

            <h2>1. Acceptance of Terms</h2>
            <p>
                By accessing and using {{ setting('site_name') }}, you accept and agree to be bound by
                the terms and provision of this agreement. If you do not agree to these terms, please
                do not use our service.
            </p>

            <h2>2. Use License</h2>
            <p>
                Permission is granted to temporarily access the materials on {{ setting('site_name') }}
                for personal, non-commercial transitory viewing only. This is the grant of a license,
                not a transfer of title, and under this license you may not:
            </p>
            <ul>
                <li>modify or copy the materials</li>
                <li>use the materials for any commercial purpose or for any public display</li>
                <li>attempt to reverse engineer any software contained on our website</li>
                <li>remove any copyright or other proprietary notations from the materials</li>
            </ul>

            <h2>3. User Content</h2>
            <p>
                Our service allows you to post, link, store, share and otherwise make available certain
                information, text, graphics, or other material ("Content"). You are responsible for Content
                that you post on or through the service, including its legality, reliability, and appropriateness.
            </p>
            <p>By posting Content on or through the service, you represent and warrant that:</p>
            <ul>
                <li>Content is yours (you own it) and/or you have the right to use it</li>
                <li>Your use of the Content does not infringe, violate or misappropriate the rights of any third party</li>
                <li>Content does not contain viruses or other malicious code</li>
            </ul>

            <h2>4. Subscriptions</h2>
            <p>
                Some parts of the service are billed on a subscription basis. You will be billed in
                advance on a recurring and periodic basis. Billing cycles are set on a monthly or
                annual basis, depending on the subscription plan you select.
            </p>
            <p>
                Your subscription will automatically renew unless you cancel it before the renewal date.
                You may cancel your subscription at any time through your account settings.
            </p>

            <h2>5. Prohibited Uses</h2>
            <p>You may not use our service:</p>
            <ul>
                <li>For any unlawful purpose</li>
                <li>To solicit others to perform unlawful acts</li>
                <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                <li>To submit false or misleading information</li>
                <li>To upload or transmit viruses or any other type of malicious code</li>
                <li>To spam, phish, pharm, pretext, spider, crawl, or scrape</li>
            </ul>

            <h2>6. Intellectual Property</h2>
            <p>
                The service and its original content (excluding Content provided by users), features, and
                functionality are and will remain the exclusive property of {{ setting('site_name') }} and
                its licensors. The service is protected by copyright, trademark, and other laws. Our
                trademarks and trade dress may not be used in connection with any product or service
                without our prior written consent.
            </p>

            <h2>7. Termination</h2>
            <p>
                We may terminate or suspend your account and bar access to the service immediately,
                without prior notice or liability, under our sole discretion, for any reason whatsoever
                and without limitation, including but not limited to a breach of the Terms.
            </p>

            <h2>8. Disclaimer</h2>
            <p>
                Your use of our service is at your sole risk. The service is provided on an "AS IS" and
                "AS AVAILABLE" basis. The service is provided without warranties of any kind, whether
                express or implied, including, but not limited to, implied warranties of merchantability,
                fitness for a particular purpose, non-infringement, or course of performance.
            </p>

            <h2>9. Limitation of Liability</h2>
            <p>
                In no event shall {{ setting('site_name') }}, nor its directors, employees, partners,
                agents, suppliers, or affiliates, be liable for any indirect, incidental, special,
                consequential, or punitive damages, including without limitation, loss of profits,
                data, use, goodwill, or other intangible losses.
            </p>

            <h2>10. Governing Law</h2>
            <p>
                These Terms shall be governed and construed in accordance with the laws of
                {{ setting('company_country', 'your jurisdiction') }}, without regard to its conflict
                of law provisions. Our failure to enforce any right or provision of these Terms will
                not be considered a waiver of those rights.
            </p>

            <h2>11. Changes to Terms</h2>
            <p>
                We reserve the right, at our sole discretion, to modify or replace these Terms at any
                time. If a revision is material, we will provide at least 30 days notice prior to any
                new terms taking effect.
            </p>

            <h2>12. Contact Information</h2>
            <p>
                If you have any questions about these Terms, please contact us at:
            </p>
            <ul>
                <li>Email: legal@{{ str_replace(['http://', 'https://', 'www.'], '', config('app.url')) }}</li>
                <li>Address: {{ setting('company_address', 'Your Company Address') }}</li>
            </ul>
        </div>
    </div>
</div>
@endsection
