@extends('layouts.app')

@section('title', 'About NextGenBeing - AI Learning & Tutorials Platform')
@section('description', 'Learn about NextGenBeing, our mission to democratize AI education, and our expert team of AI educators and engineers.')
@section('keywords', 'about us, AI education, artificial intelligence learning, expert team, mission')

<main class="min-h-screen bg-white dark:bg-gray-900">
    <!-- Hero Section -->
    <div class="px-4 py-16 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                About NextGenBeing
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                We're on a mission to democratize AI education and help developers, entrepreneurs, and creators master artificial intelligence through comprehensive, production-grade tutorials.
            </p>
        </div>

        <!-- Mission Section -->
        <section class="mb-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        Our Mission
                    </h2>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-4">
                        NextGenBeing exists to solve a critical problem in tech education: most AI learning resources are either too shallow or too theoretical. We create production-grade tutorials that bridge the gap between "Hello World" and "Ship It."
                    </p>
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-4">
                        Our content is built by engineers who've shipped real AI products, for developers who want to build real AI solutions. Every tutorial includes working code, architectural patterns, performance considerations, and real-world lessons learned.
                    </p>
                    <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">Expert-led, Community-driven, Always Improving</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-700 rounded-lg p-8">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">156+</h3>
                            <p class="text-gray-600 dark:text-gray-300">Production-grade tutorials annually</p>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">72,000+</h3>
                            <p class="text-gray-600 dark:text-gray-300">Words of AI education content weekly</p>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">8-Part Series</h3>
                            <p class="text-gray-600 dark:text-gray-300">Each tutorial provides complete coverage</p>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-2">100% Code</h3>
                            <p class="text-gray-600 dark:text-gray-300">Real, working implementations included</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Expertise Section -->
        <section class="mb-20 bg-gray-50 dark:bg-gray-800 rounded-lg p-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-12 text-center">
                Our Expertise
            </h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">AI & Machine Learning</h3>
                    <p class="text-gray-600 dark:text-gray-300">LLMs, RAG, fine-tuning, prompt engineering, and advanced AI workflows</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Software Architecture</h3>
                    <p class="text-gray-600 dark:text-gray-300">Production patterns, scalability, performance optimization, and system design</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Real-World Implementation</h3>
                    <p class="text-gray-600 dark:text-gray-300">Lessons from shipping AI products, debugging, and solving production challenges</p>
                </div>
            </div>
        </section>

        <!-- Why Trust Us Section -->
        <section class="mb-20">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-12">
                Why Trust NextGenBeing?
            </h2>
            <div class="space-y-6">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Expert Authors</h3>
                        <p class="text-gray-600 dark:text-gray-300">Our tutorials are created by engineers with 10+ years of experience building and shipping AI products. We don't teach theory—we teach what actually works.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Verified & Tested Content</h3>
                        <p class="text-gray-600 dark:text-gray-300">Every code example is tested and verified before publishing. We include error handling, edge cases, and real-world configurations.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Comprehensive Series</h3>
                        <p class="text-gray-600 dark:text-gray-300">Each topic is covered in depth with 8-part series. From fundamentals to advanced patterns, we leave no stone unturned.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Always Up-to-Date</h3>
                        <p class="text-gray-600 dark:text-gray-300">AI moves fast. We continuously update our content to reflect the latest best practices, tools, and techniques.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Quality Assurance</h3>
                        <p class="text-gray-600 dark:text-gray-300">Automatic quality evaluation ensures only high-quality, substantive content is published. Poor content is never served to our readers.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Philosophy -->
        <section class="mb-20 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg p-12">
            <h2 class="text-3xl font-bold mb-6">Our Content Philosophy</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">What We DO</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="text-green-300 font-bold">✓</span>
                            <span>Production-grade code examples</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-300 font-bold">✓</span>
                            <span>Real-world problem solving</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-300 font-bold">✓</span>
                            <span>Performance & security considerations</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-300 font-bold">✓</span>
                            <span>Architectural patterns & best practices</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">What We DON'T</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="text-red-300 font-bold">✗</span>
                            <span>Shallow tutorials or "magic" explanations</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-red-300 font-bold">✗</span>
                            <span>Outdated or deprecated information</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-red-300 font-bold">✗</span>
                            <span>AI-generated fluff content</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-red-300 font-bold">✗</span>
                            <span>Content that doesn't solve real problems</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                Ready to Master AI?
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                Explore our comprehensive tutorials, learn production-grade AI development, and join thousands of developers building the future.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('tutorials.index') }}" class="inline-block px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Browse Tutorials
                </a>
                <a href="{{ route('posts.index') }}" class="inline-block px-8 py-3 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    Read Articles
                </a>
            </div>
        </section>
    </div>
</main>

@push('structured-data')
@php
$socialLinks = setting('social_links', []);
$sameAs = [];
if (isset($socialLinks['twitter']) && !empty($socialLinks['twitter'])) {
    $sameAs[] = $socialLinks['twitter'];
}
if (isset($socialLinks['linkedin']) && !empty($socialLinks['linkedin'])) {
    $sameAs[] = $socialLinks['linkedin'];
}
if (isset($socialLinks['github']) && !empty($socialLinks['github'])) {
    $sameAs[] = $socialLinks['github'];
}
$schemaData = [
    "@context" => "https://schema.org",
    "@type" => "AboutPage",
    "mainEntity" => [
        "@type" => "Organization",
        "name" => setting('site_name', 'NextGenBeing'),
        "url" => config('app.url'),
        "logo" => asset('uploads/logo.png'),
        "description" => "AI Learning & Tutorials Platform - Production-grade tutorials for developers, entrepreneurs, and creators",
        "foundingDate" => "2025",
        "contactPoint" => [
            "@type" => "ContactPoint",
            "contactType" => "Customer Service",
            "email" => setting('support_email', 'support@nextgenbeing.com'),
        ],
        "sameAs" => $sameAs,
    ],
];
@endphp
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
