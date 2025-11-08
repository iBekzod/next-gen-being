@extends('layouts.app')

@section('title', 'Create New Post - ' . setting('site_name'))
@section('description', 'Create a new blog post with AI assistance')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css">
<style>
[x-cloak] { display: none; }
.section-header {
    @apply flex items-center justify-between px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 border-b border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-blue-100 dark:hover:bg-gray-600 transition-colors;
}
.section-header h3 {
    @apply text-sm font-semibold text-gray-900 dark:text-white;
}
.section-content {
    @apply border-b border-gray-200 dark:border-gray-600;
}
.quota-badge {
    @apply inline-flex items-center px-2 py-1 rounded-full text-xs font-medium;
}
.quota-ok { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
.quota-warning { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; }
.quota-limited { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Create New Post</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Share your knowledge with AI-powered tools and advanced features</p>
        </div>

        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-0 bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            @csrf

            <!-- BASIC INFORMATION SECTION -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="section-header" onclick="toggleSection(event, 'basic-info')">
                    <h3>📝 Basic Information</h3>
                    <svg class="w-5 h-5 transform transition-transform" id="basic-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <div class="section-content p-6 space-y-6" id="basic-info">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title') }}"
                               required
                               placeholder="Enter your post title"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(isset($suggestion))
                        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-l-4 border-blue-500">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-1">
                                ✨ Creating from AI Suggestion
                            </h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Title suggestion: <strong>{{ $suggestion->title }}</strong>
                            </p>
                        </div>
                        <script>
                            if (!document.getElementById('title').value) {
                                document.getElementById('title').value = @json($suggestion->title);
                            }
                        </script>
                    @endif

                    <!-- Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Excerpt <span class="text-red-500">*</span>
                        </label>
                        <textarea name="excerpt"
                                  id="excerpt"
                                  rows="3"
                                  required
                                  placeholder="A brief summary of your post (max 500 characters)"
                                  maxlength="500"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('excerpt') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span id="excerpt-count">0</span>/500 characters
                        </p>
                        @error('excerpt')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <div class="border border-gray-300 rounded-lg dark:border-gray-600 overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 border-b border-gray-300 dark:border-gray-600 flex flex-wrap gap-1">
                                <button type="button" onclick="formatText('bold')" title="Bold" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 4a2 2 0 00-2 2v8a2 2 0 002 2h3.5a3.5 3.5 0 001.852-6.49A3.5 3.5 0 008.5 4H6zm2 9V7h.5a1.5 1.5 0 010 3H8zm0 0h1a1.5 1.5 0 010 3H8v-3z"/></svg>
                                </button>
                                <button type="button" onclick="formatText('italic')" title="Italic" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7 2a1 1 0 011 1v1h4V3a1 1 0 112 0v1h1a1 1 0 110 2h-1.003l-.8 10H14a1 1 0 110 2H6a1 1 0 110-2h1.003l.8-10H7a1 1 0 110-2h1V3a1 1 0 011-1zm2.197 4l-.8 10h3.406l.8-10H9.197z" clip-rule="evenodd"/></svg>
                                </button>
                                <button type="button" onclick="formatText('heading')" title="Heading" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded text-sm font-bold">H</button>
                                <button type="button" onclick="formatText('link')" title="Link" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                </button>
                                <button type="button" onclick="formatText('code')" title="Code" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                </button>
                                <button type="button" onclick="formatText('list')" title="List" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                </button>
                            </div>
                            <textarea name="content"
                                      id="content"
                                      rows="20"
                                      required
                                      placeholder="Write your post content here... (Markdown supported)"
                                      class="w-full px-4 py-3 border-0 dark:bg-gray-700 dark:text-white focus:ring-0 resize-none">{{ old('content') }}</textarea>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Supports Markdown. Read time: <span id="read-time">0</span> min | Words: <span id="word-count">0</span>
                        </p>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- AI ASSISTANT SECTION -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="section-header" onclick="toggleSection(event, 'ai-assistant')">
                    <div class="flex items-center gap-3">
                        <span>🤖 AI Assistant</span>
                        <span class="quota-badge quota-{{ $userAiQuota['can_generate'] ? 'ok' : 'limited' }}">
                            @if($userAiQuota['tier'] === 'free')
                                Free Tier
                            @elseif($userAiQuota['posts_limit'])
                                {{ $userAiQuota['posts_generated'] }}/{{ $userAiQuota['posts_limit'] }}
                            @else
                                Unlimited
                            @endif
                        </span>
                    </div>
                    <svg class="w-5 h-5 transform transition-transform" id="ai-assistant-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <div class="section-content p-6 space-y-6 hidden" id="ai-assistant">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            Use AI to generate content ideas, write full posts, or create images for your content.
                            @if(!$userAiQuota['can_generate'])
                                <a href="{{ route('subscription.plans') }}" class="font-semibold underline">Upgrade your plan</a> to unlock unlimited AI generation.
                            @endif
                        </p>
                    </div>

                    <!-- AI Content Generation -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Generate Content with AI</h4>
                        <div class="space-y-3">
                            <div>
                                <label for="ai_topic" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Topic or Keywords
                                </label>
                                <input type="text"
                                       id="ai_topic"
                                       placeholder="e.g., 'Building REST APIs with Laravel', 'Vue.js performance optimization'"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="button"
                                    id="ai_generate_btn"
                                    onclick="generateAIContent()"
                                    {{ !$userAiQuota['can_generate'] ? 'disabled' : '' }}
                                    class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg transition-colors font-medium">
                                ✨ Generate Content
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            AI will generate a complete post based on your topic. Remaining: {{ $userAiQuota['posts_limit'] ? ($userAiQuota['posts_limit'] - $userAiQuota['posts_generated']) : 'Unlimited' }}
                        </p>
                    </div>

                    <!-- AI Image Generation -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Generate Featured Image</h4>
                        <div class="space-y-3">
                            <div>
                                <label for="ai_image_prompt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Image Description
                                </label>
                                <textarea id="ai_image_prompt"
                                          rows="3"
                                          placeholder="Describe the image you want to generate (e.g., 'Modern developer working at laptop, sunset, warm colors')"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            <button type="button"
                                    id="ai_image_btn"
                                    onclick="generateAIImage()"
                                    {{ !$userAiQuota['can_generate_image'] ? 'disabled' : '' }}
                                    class="w-full py-2 px-4 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white rounded-lg transition-colors font-medium">
                                🎨 Generate Image
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Generate a unique image for your post. Remaining: {{ $userAiQuota['images_limit'] ? ($userAiQuota['images_limit'] - $userAiQuota['images_generated']) : 'Unlimited' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- WRITING ASSISTANT SECTION -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="section-header" onclick="toggleSection(event, 'writing-assistant')">
                    <h3>✍️ Writing Assistant</h3>
                    <svg class="w-5 h-5 transform transition-transform" id="writing-assistant-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <div class="section-content p-6 space-y-4 hidden" id="writing-assistant">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Get real-time suggestions for grammar, style, readability, tone, and more.
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="checkGrammar()" class="py-2 px-3 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-900 dark:text-amber-200 rounded-lg text-sm font-medium transition">
                            ✓ Check Grammar
                        </button>
                        <button type="button" onclick="analyzeStyle()" class="py-2 px-3 bg-cyan-100 dark:bg-cyan-900/30 hover:bg-cyan-200 dark:hover:bg-cyan-900/50 text-cyan-900 dark:text-cyan-200 rounded-lg text-sm font-medium transition">
                            📊 Style Tips
                        </button>
                        <button type="button" onclick="checkReadability()" class="py-2 px-3 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-900 dark:text-green-200 rounded-lg text-sm font-medium transition">
                            📖 Readability
                        </button>
                        <button type="button" onclick="analyzeTone()" class="py-2 px-3 bg-pink-100 dark:bg-pink-900/30 hover:bg-pink-200 dark:hover:bg-pink-900/50 text-pink-900 dark:text-pink-200 rounded-lg text-sm font-medium transition">
                            🎭 Tone Analysis
                        </button>
                    </div>
                    <div id="assistant-results" class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hidden">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2" id="results-title"></h4>
                        <div id="results-content" class="text-sm text-gray-700 dark:text-gray-300 space-y-2"></div>
                    </div>
                </div>
            </div>

            <!-- CONTENT ORGANIZATION -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="section-header" onclick="toggleSection(event, 'organization')">
                    <h3>📚 Content Organization</h3>
                    <svg class="w-5 h-5 transform transition-transform" id="organization-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <div class="section-content p-6 space-y-6" id="organization">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id"
                                    id="category_id"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Post Type -->
                        <div>
                            <label for="post_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Post Type
                            </label>
                            <select name="post_type"
                                    id="post_type"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="standard" {{ old('post_type', 'standard') == 'standard' ? 'selected' : '' }}>Standard Post</option>
                                <option value="tutorial" {{ old('post_type') == 'tutorial' ? 'selected' : '' }}>Tutorial</option>
                                <option value="guide" {{ old('post_type') == 'guide' ? 'selected' : '' }}>Guide</option>
                                <option value="review" {{ old('post_type') == 'review' ? 'selected' : '' }}>Review</option>
                                <option value="video" {{ old('post_type') == 'video' ? 'selected' : '' }}>Video Blog</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tags
                        </label>
                        <input name="tags"
                               id="tags"
                               placeholder="Add tags... (e.g., laravel, php, web-development)"
                               value="{{ old('tags') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Type to see suggestions or create new tags
                        </p>
                    </div>

                    <!-- Series Section -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center mb-4">
                            <input type="checkbox" id="use-series" class="rounded" onchange="toggleSeries()">
                            <label for="use-series" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Part of a Tutorial Series
                            </label>
                        </div>
                        <div id="series-fields" class="hidden space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="series_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Series Title
                                    </label>
                                    <input type="text"
                                           name="series_title"
                                           id="series_title"
                                           value="{{ old('series_title') }}"
                                           placeholder="e.g., 'Building a Blog with Laravel'"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="series_part" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Part Number
                                    </label>
                                    <input type="number"
                                           name="series_part"
                                           id="series_part"
                                           value="{{ old('series_part') }}"
                                           min="1"
                                           placeholder="e.g., 1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="series_total_parts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Total Parts
                                    </label>
                                    <input type="number"
                                           name="series_total_parts"
                                           id="series_total_parts"
                                           value="{{ old('series_total_parts') }}"
                                           min="1"
                                           placeholder="e.g., 5"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label for="series_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Series Description
                                </label>
                                <textarea name="series_description"
                                          id="series_description"
                                          rows="3"
                                          placeholder="What is this tutorial series about?"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('series_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FEATURED IMAGE SECTION -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="section-header" onclick="toggleSection(event, 'featured-image')">
                    <h3>🖼️ Featured Image</h3>
                    <svg class="w-5 h-5 transform transition-transform" id="featured-image-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <div class="section-content p-6 space-y-6" id="featured-image">
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 transition">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="featured_image" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload a file</span>
                                    <input id="featured_image" name="featured_image" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                    <div id="image-preview" class="hidden">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview:</p>
                        <img src="" alt="Preview" class="max-h-80 mx-auto rounded-lg shadow">
                        <div class="mt-3">
                            <label for="image_attribution" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Image Attribution <small class="text-gray-500">(Required for AI-generated or external images)</small>
                            </label>
                            <input type="text"
                                   name="image_attribution"
                                   id="image_attribution"
                                   value="{{ old('image_attribution') }}"
                                   placeholder="e.g., 'Photo by John Doe on Unsplash' or 'Generated with DALL-E'"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- MONETIZATION SECTION -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="section-header" onclick="toggleSection(event, 'monetization')">
                    <h3>💰 Monetization & Access</h3>
                    <svg class="w-5 h-5 transform transition-transform" id="monetization-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <div class="section-content p-6 space-y-6" id="monetization">
                    <div>
                        <label for="premium_selection" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                            Content Access Level
                        </label>
                        <div class="space-y-3">
                            <label class="flex items-start p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/10 transition">
                                <input type="radio" name="is_premium" value="0" {{ old('is_premium', '0') == '0' ? 'checked' : '' }} class="mt-1">
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900 dark:text-white">Public (Free)</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Anyone can read this post</p>
                                </div>
                            </label>
                            <label class="flex items-start p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/10 transition">
                                <input type="radio" name="is_premium" value="1" {{ old('is_premium') == '1' ? 'checked' : '' }} class="mt-1" onchange="showPremiumTiers()">
                                <div class="ml-3">
                                    <p class="font-medium text-gray-900 dark:text-white">Premium (Subscription Required)</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Only subscribers can read this post</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Premium Tiers (shown when is_premium is checked) -->
                    <div id="premium-tiers" class="hidden pt-4 border-t border-gray-200 dark:border-gray-600">
                        <label for="premium_tier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Minimum Subscription Tier
                        </label>
                        <select name="premium_tier" id="premium_tier" class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                            <option value="">Select tier...</option>
                            <option value="basic" {{ old('premium_tier') == 'basic' ? 'selected' : '' }}>Basic ($4.99/mo)</option>
                            <option value="pro" {{ old('premium_tier') == 'pro' ? 'selected' : '' }}>Pro ($9.99/mo)</option>
                            <option value="team" {{ old('premium_tier') == 'team' ? 'selected' : '' }}>Team ($29.99/mo)</option>
                        </select>
                    </div>

                    <!-- Comments -->
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-600">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="allow_comments"
                                   value="1"
                                   {{ old('allow_comments', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                Allow readers to comment on this post
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- PUBLISHING SECTION -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <div class="section-header" onclick="toggleSection(event, 'publishing')">
                    <h3>📤 Publishing Options</h3>
                    <svg class="w-5 h-5 transform transition-transform" id="publishing-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <div class="section-content p-6 space-y-6" id="publishing">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status"
                                    id="status"
                                    required
                                    onchange="toggleScheduledDate()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publish Now</option>
                                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Schedule for Later</option>
                            </select>
                        </div>

                        <!-- Publish Date (for scheduled status) -->
                        <div id="scheduled-date-field" class="hidden">
                            <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Publish Date & Time
                            </label>
                            <input type="datetime-local"
                                   name="published_at"
                                   id="published_at"
                                   value="{{ old('published_at') }}"
                                   min="{{ now()->addMinutes(10)->format('Y-m-d\TH:i') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-600">
                <a href="{{ route('dashboard.posts') }}"
                   class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium transition">
                    ← Cancel
                </a>
                <div class="flex space-x-3">
                    <button type="submit"
                            name="action"
                            value="save_draft"
                            class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors font-medium">
                        Save as Draft
                    </button>
                    <button type="submit"
                            name="action"
                            value="publish"
                            class="px-8 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg transition-colors font-medium shadow-sm">
                        ✨ Publish Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/@yaireo/tagify"></script>
<script>
// Section toggle functionality
function toggleSection(event, sectionId) {
    event.preventDefault();
    const section = document.getElementById(sectionId);
    const icon = document.getElementById(sectionId + '-icon');

    section.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

// Character count for excerpt
const excerptTextarea = document.getElementById('excerpt');
const excerptCount = document.getElementById('excerpt-count');
excerptTextarea.addEventListener('input', function() {
    excerptCount.textContent = this.value.length;
});
excerptCount.textContent = excerptTextarea.value.length;

// Read time and word count
const contentTextarea = document.getElementById('content');
const readTimeSpan = document.getElementById('read-time');
const wordCountSpan = document.getElementById('word-count');

function calculateReadTime() {
    const words = contentTextarea.value.trim().split(/\s+/).filter(w => w).length;
    const readTime = Math.max(1, Math.ceil(words / 200));
    readTimeSpan.textContent = readTime;
    wordCountSpan.textContent = words;
}

contentTextarea.addEventListener('input', calculateReadTime);
calculateReadTime();

// Show/hide scheduled date field
function toggleScheduledDate() {
    const statusSelect = document.getElementById('status');
    const scheduledDateField = document.getElementById('scheduled-date-field');

    if (statusSelect.value === 'scheduled') {
        scheduledDateField.classList.remove('hidden');
        document.getElementById('published_at').required = true;
    } else {
        scheduledDateField.classList.add('hidden');
        document.getElementById('published_at').required = false;
    }
}

// Image preview
function previewImage(input) {
    const preview = document.getElementById('image-preview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview:</p>
                <img src="${e.target.result}" alt="Preview" class="max-h-80 mx-auto rounded-lg shadow">
                <div class="mt-3">
                    <label for="image_attribution" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Image Attribution <small class="text-gray-500">(for external/AI images)</small>
                    </label>
                    <input type="text"
                           name="image_attribution"
                           id="image_attribution"
                           placeholder="e.g., 'Photo by John Doe on Unsplash'"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            `;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Tags input with Tagify
const tagsInput = document.querySelector('input[name=tags]');
const tagify = new Tagify(tagsInput, {
    whitelist: {!! json_encode($tags->pluck('name')->toArray()) !!},
    dropdown: {
        maxItems: 20,
        enabled: 0,
        closeOnSelect: false
    }
});

// Text formatting
function formatText(command) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    let replacement = '';

    switch(command) {
        case 'bold':
            replacement = `**${selectedText || 'bold text'}**`;
            break;
        case 'italic':
            replacement = `*${selectedText || 'italic text'}*`;
            break;
        case 'heading':
            replacement = `## ${selectedText || 'Heading'}`;
            break;
        case 'link':
            const url = prompt('Enter URL:');
            if (url) replacement = `[${selectedText || 'link'}](${url})`;
            else return;
            break;
        case 'code':
            if (selectedText.includes('\n')) {
                replacement = '```\n' + selectedText + '\n```';
            } else {
                replacement = '`' + selectedText + '`';
            }
            break;
        case 'list':
            const lines = selectedText.split('\n') || ['item'];
            replacement = lines.map(line => `- ${line}`).join('\n');
            break;
    }

    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    calculateReadTime();
    textarea.focus();
}

// Series toggle
function toggleSeries() {
    const useSeries = document.getElementById('use-series').checked;
    const seriesFields = document.getElementById('series-fields');

    if (useSeries) {
        seriesFields.classList.remove('hidden');
    } else {
        seriesFields.classList.add('hidden');
    }
}

// Premium tier toggle
function showPremiumTiers() {
    const isPremium = document.querySelector('input[name="is_premium"]:checked').value === '1';
    const tierContainer = document.getElementById('premium-tiers');

    if (isPremium) {
        tierContainer.classList.remove('hidden');
    } else {
        tierContainer.classList.add('hidden');
    }
}

// AI Content Generation (placeholder)
async function generateAIContent() {
    const topic = document.getElementById('ai_topic').value;
    if (!topic) {
        alert('Please enter a topic');
        return;
    }

    const btn = document.getElementById('ai_generate_btn');
    btn.disabled = true;
    btn.textContent = '⏳ Generating...';

    try {
        // This would call your AI API endpoint
        // For now, showing placeholder
        alert('AI content generation coming soon!');
    } finally {
        btn.disabled = false;
        btn.textContent = '✨ Generate Content';
    }
}

// AI Image Generation (placeholder)
async function generateAIImage() {
    const prompt = document.getElementById('ai_image_prompt').value;
    if (!prompt) {
        alert('Please describe the image');
        return;
    }

    const btn = document.getElementById('ai_image_btn');
    btn.disabled = true;
    btn.textContent = '⏳ Generating...';

    try {
        // This would call your AI image API
        alert('AI image generation coming soon!');
    } finally {
        btn.disabled = false;
        btn.textContent = '🎨 Generate Image';
    }
}

// Writing Assistant functions
async function checkGrammar() {
    showAssistantLoading('Checking grammar...');
    // API call here
    setTimeout(() => {
        showAssistantResults('Grammar Check', [
            '✓ No major grammar issues found',
            'Tip: Keep sentences under 20 words',
            'Consider using more active voice'
        ]);
    }, 1000);
}

async function analyzeStyle() {
    showAssistantLoading('Analyzing style...');
    setTimeout(() => {
        showAssistantResults('Style Analysis', [
            'Your writing style is clear and concise',
            'Try varying sentence structure more',
            'Good use of technical terminology'
        ]);
    }, 1000);
}

async function checkReadability() {
    showAssistantLoading('Checking readability...');
    setTimeout(() => {
        showAssistantResults('Readability Score', [
            'Flesch Reading Ease: 60/100 (Standard)',
            'Recommended for: College graduates',
            'Consider: Breaking into shorter sections'
        ]);
    }, 1000);
}

async function analyzeTone() {
    showAssistantLoading('Analyzing tone...');
    setTimeout(() => {
        showAssistantResults('Tone Analysis', [
            'Detected tone: Professional & Informative',
            'Emotion: Neutral to Positive',
            'Good for: Technical documentation'
        ]);
    }, 1000);
}

function showAssistantLoading(message) {
    const resultsDiv = document.getElementById('assistant-results');
    resultsDiv.classList.remove('hidden');
    document.getElementById('results-title').textContent = message;
    document.getElementById('results-content').innerHTML = '<p class="text-gray-500">Loading...</p>';
}

function showAssistantResults(title, results) {
    document.getElementById('results-title').textContent = title;
    document.getElementById('results-content').innerHTML = results
        .map(r => `<p>• ${r}</p>`)
        .join('');
}

// Form submission
document.querySelector('form').addEventListener('submit', function(e) {
    const action = e.submitter.value;
    const statusSelect = document.getElementById('status');

    if (action === 'save_draft') {
        statusSelect.value = 'draft';
    } else if (action === 'publish') {
        statusSelect.value = statusSelect.value === 'scheduled' ? 'scheduled' : 'published';
        // Show modal after successful publish
        setTimeout(() => {
            showPublishModal();
        }, 1500); // Wait for form to process and redirect back
    }
});

// Check if premium should show on load
window.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('input[name="is_premium"]:checked').value === '1') {
        showPremiumTiers();
    }
    if (document.getElementById('use-series').checked) {
        document.getElementById('series-fields').classList.remove('hidden');
    }
});
</script>
@endpush

<!-- Post Publish Actions Modal -->
@include('partials.post-publish-actions-modal')

@endsection
