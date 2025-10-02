@extends('layouts.app')

@section('title', 'Privacy Policy - ' . setting('site_name'))
@section('description', 'Privacy policy and data protection information')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Privacy Policy</h1>

        <div class="prose prose-lg dark:prose-invert max-w-none">
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Last updated: {{ now()->format('F j, Y') }}
            </p>

            <h2>1. Information We Collect</h2>
            <p>
                We collect information you provide directly to us, such as when you create an account,
                subscribe to our service, post content, or contact us. This may include:
            </p>
            <ul>
                <li>Name, email address, and password</li>
                <li>Profile information (bio, avatar, social media links)</li>
                <li>Payment information (processed securely through our payment providers)</li>
                <li>Content you create (posts, comments, interactions)</li>
                <li>Communications with us</li>
            </ul>

            <h2>2. How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Provide, maintain, and improve our services</li>
                <li>Process transactions and send related information</li>
                <li>Send technical notices, updates, security alerts, and support messages</li>
                <li>Respond to your comments, questions, and provide customer service</li>
                <li>Monitor and analyze trends, usage, and activities</li>
                <li>Personalize and improve your experience</li>
            </ul>

            <h2>3. Information Sharing</h2>
            <p>
                We do not sell, trade, or otherwise transfer your personal information to third parties.
                We may share information:
            </p>
            <ul>
                <li>With your consent</li>
                <li>To comply with legal obligations</li>
                <li>To protect our rights, privacy, safety, or property</li>
                <li>With service providers who assist in our operations</li>
            </ul>

            <h2>4. Data Security</h2>
            <p>
                We implement appropriate technical and organizational measures to protect your personal
                information against unauthorized access, alteration, disclosure, or destruction.
            </p>

            <h2>5. Your Rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Correct inaccurate data</li>
                <li>Request deletion of your data</li>
                <li>Object to processing of your data</li>
                <li>Data portability</li>
            </ul>

            <h2>6. Cookies</h2>
            <p>
                We use cookies and similar tracking technologies to track activity on our service and
                hold certain information. You can instruct your browser to refuse all cookies or to
                indicate when a cookie is being sent.
            </p>

            <h2>7. Children's Privacy</h2>
            <p>
                Our service is not intended for children under 13 years of age. We do not knowingly
                collect personal information from children under 13.
            </p>

            <h2>8. Changes to This Policy</h2>
            <p>
                We may update our Privacy Policy from time to time. We will notify you of any changes
                by posting the new Privacy Policy on this page and updating the "Last updated" date.
            </p>

            <h2>9. Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy, please contact us at:
            </p>
            <ul>
                <li>Email: {{ 'privacy@' . str_replace(['http://', 'https://', 'www.'], '', config('app.url', 'https://nextgenbeing.com')) }}</li>
                <li>Address: {{ setting('company_address', '240 Bogishamol Street, Yunusabad District, Tashkent 100058, Uzbekistan') }}</li>
            </ul>
        </div>
    </div>
</div>
@endsection
