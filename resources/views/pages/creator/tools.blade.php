@extends('layouts.app')

@section('title', 'Creator Tools - Next-Gen Being')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-orange-600 to-orange-800 text-white rounded-lg p-8">
        <h1 class="text-4xl font-bold mb-2">🛠️ Creator Tools</h1>
        <p class="text-orange-100">AI-powered tools to level up your content</p>
    </div>

    <!-- Tools Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Content Ideas Tool -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                <h3 class="text-2xl font-bold">💡 Content Ideas</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Get AI-powered content ideas based on your niche and audience.</p>
                <a href="/dashboard/tools/ideas" class="inline-block px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Generate Ideas
                </a>
            </div>
        </div>

        <!-- SEO Analyzer Tool -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
                <h3 class="text-2xl font-bold">🔍 SEO Analyzer</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Optimize your content for search engines and boost visibility.</p>
                <a href="/dashboard/tools/seo" class="inline-block px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Analyze Content
                </a>
            </div>
        </div>

        <!-- Audience Insights Tool -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6">
                <h3 class="text-2xl font-bold">📊 Audience Insights</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Understand your audience better and create more engaging content.</p>
                <a href="/dashboard/tools/audience" class="inline-block px-6 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                    View Insights
                </a>
            </div>
        </div>
    </div>

    <!-- Content Ideas Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Your Ideas</h2>
        @livewire('content-idea-list')
    </div>
</div>
@endsection
