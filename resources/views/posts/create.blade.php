@extends('layouts.app')

@section('title', 'Create New Post - ' . setting('site_name'))
@section('description', 'Create a new blog post with AI assistance')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2"></script>
<script src="https://cdn.jsdelivr.net/npm/tippy.js@6"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/themes/light.css">
<style>
[x-cloak] { display: none; }

.tooltip-trigger {
    cursor: help;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.input-group-label {
    @apply flex items-center gap-2 mb-2;
}

.input-help-text {
    @apply text-xs text-gray-500 dark:text-gray-400 mt-1;
}

.ai-button {
    @apply inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-medium text-sm hover:shadow-lg transition-all;
}

.ai-button:hover {
    @apply from-blue-700 to-indigo-700;
}

.section-card {
    @apply bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6;
}

.form-group {
    @apply mb-6;
}

.modal-backdrop {
    @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50;
    display: none;
}

.modal-backdrop.active {
    display: flex;
}

.modal-content {
    @apply bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4;
}

.button-primary {
    @apply px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition;
}

.button-secondary {
    @apply px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition;
}

.loading-spinner {
    @apply inline-block animate-spin;
}

.success-message {
    @apply p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded text-green-800 dark:text-green-200 text-sm;
}

.info-message {
    @apply p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded text-blue-800 dark:text-blue-200 text-sm;
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Post</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Share your knowledge and grow your audience</p>
        </div>

        <!-- Form -->
        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="section-card">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Post Information</h2>

                <!-- Title -->
                <div class="form-group">
                    <div class="input-group-label">
                        <label for="title" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Title
                        </label>
                        <span class="text-red-500">*</span>
                        <div class="tooltip-trigger" data-tooltip="Give your post a clear, descriptive title. Good titles help readers find your post and improve search visibility.">
                            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <input type="text"
                           name="title"
                           id="title"
                           value="{{ old('title') }}"
                           required
                           placeholder="e.g., How to Build a Web Application"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="input-help-text">40-70 characters recommended for SEO</p>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Excerpt -->
                <div class="form-group">
                    <div class="input-group-label">
                        <label for="excerpt" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <span class="text-red-500">*</span>
                        <div class="tooltip-trigger" data-tooltip="A short summary of your post. This appears in search results and when people share your post.">
                            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <textarea name="excerpt"
                              id="excerpt"
                              rows="3"
                              required
                              placeholder="Write a brief summary of your post content..."
                              maxlength="500"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('excerpt') }}</textarea>
                    <p class="input-help-text">
                        <span id="excerpt-count">0</span>/500 characters
                    </p>
                    @error('excerpt')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div class="form-group">
                    <div class="input-group-label">
                        <label for="category_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Category
                        </label>
                        <span class="text-red-500">*</span>
                        <div class="tooltip-trigger" data-tooltip="Choose the topic that best describes your post. This helps organize content and helps readers find related posts.">
                            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <select name="category_id" id="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
            </div>

            <!-- Content Section -->
            <div class="section-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Post Content</h2>
                    <button type="button" onclick="openContentModal()" class="ai-button">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generate with AI
                    </button>
                </div>

                <!-- Content Editor -->
                <div class="form-group">
                    <label for="content" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 block">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content"
                              id="content"
                              rows="12"
                              required
                              placeholder="Write your post content here. You can format it with **bold**, *italic*, and line breaks. Start with an engaging introduction."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm">{{ old('content') }}</textarea>
                    <p class="input-help-text">Use markdown formatting: **bold**, *italic*, [link text](URL)</p>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Media Section -->
            <div class="section-card">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Featured Image</h2>
                    <button type="button" onclick="openImageModal()" class="ai-button">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Generate Image
                    </button>
                </div>

                <!-- Image Upload -->
                <div class="form-group">
                    <label class="input-group-label">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Featured Image</span>
                        <div class="tooltip-trigger" data-tooltip="An eye-catching image makes your post stand out. Upload a high-quality image (JPG or PNG, max 2MB).">
                            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition"
                         onclick="document.getElementById('featured_image').click()">
                        <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Click to upload or drag and drop</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG up to 2MB</p>
                        <input type="file"
                               name="featured_image"
                               id="featured_image"
                               accept="image/*"
                               class="hidden"
                               onchange="updateImagePreview(this)">
                    </div>
                    <div id="image-preview" class="mt-4 hidden">
                        <img id="preview-img" src="" alt="Preview" class="max-h-48 rounded-lg">
                    </div>
                    @error('featured_image')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Attribution -->
                <div class="form-group">
                    <label for="image_attribution" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                        Image Credit
                    </label>
                    <input type="text"
                           name="image_attribution"
                           id="image_attribution"
                           value="{{ old('image_attribution') }}"
                           placeholder="e.g., Photo by John Doe on Unsplash"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="input-help-text">Give credit to the photographer or designer</p>
                </div>
            </div>

            <!-- Publishing Section -->
            <div class="section-card">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Publishing Settings</h2>

                <!-- Status -->
                <div class="form-group">
                    <label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                        Status
                    </label>
                    <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="draft" selected>Draft (save for later)</option>
                        <option value="published">Publish Now</option>
                    </select>
                </div>

                <!-- Premium Content -->
                <div class="form-group">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="is_premium" id="is_premium" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Premium Content</span>
                        <div class="tooltip-trigger" data-tooltip="Make this post exclusive to premium subscribers. Only they can read the full content.">
                            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                </div>

                <!-- Premium Tier -->
                <div class="form-group" id="premium-tier-group" style="display: none;">
                    <label for="premium_tier" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                        Tier
                    </label>
                    <select name="premium_tier" id="premium_tier" class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="basic">Basic</option>
                        <option value="pro">Pro</option>
                        <option value="team">Team</option>
                    </select>
                </div>

                <!-- Allow Comments -->
                <div class="form-group">
                    <label class="flex items-center gap-3">
                        <input type="checkbox" name="allow_comments" value="1" checked class="w-4 h-4 rounded border-gray-300 text-blue-600">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Allow Comments</span>
                        <div class="tooltip-trigger" data-tooltip="Let readers discuss your post in the comments section.">
                            <svg class="w-4 h-4 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="button-primary flex-1 py-3 text-center">
                    Publish Post
                </button>
                <a href="{{ route('posts.index') }}" class="button-secondary flex-1 py-3 text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- AI Content Generation Modal -->
<div id="content-modal" class="modal-backdrop">
    <div class="modal-content">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Generate Post Content</h3>

        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                    What is your post about?
                </label>
                <input type="text"
                       id="content-topic"
                       placeholder="e.g., Building Web Applications with Laravel"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                    Key topics to cover (optional)
                </label>
                <input type="text"
                       id="content-keywords"
                       placeholder="e.g., MVC, Database, REST API"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="generatePostContent()" class="button-primary flex-1">
                    Generate
                </button>
                <button type="button" onclick="closeContentModal()" class="button-secondary flex-1">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- AI Image Generation Modal -->
<div id="image-modal" class="modal-backdrop">
    <div class="modal-content">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Generate Featured Image</h3>

        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                    Describe your ideal image
                </label>
                <textarea id="image-description"
                          rows="3"
                          placeholder="e.g., A modern laptop with code on the screen, professional office setting, bright lighting"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="generatePostImage()" class="button-primary flex-1">
                    Generate
                </button>
                <button type="button" onclick="closeImageModal()" class="button-secondary flex-1">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize tooltips
document.querySelectorAll('.tooltip-trigger').forEach(el => {
    tippy(el, {
        content: el.getAttribute('data-tooltip'),
        theme: 'light',
        placement: 'right',
    });
});

// Excerpt counter
document.getElementById('excerpt').addEventListener('input', function() {
    document.getElementById('excerpt-count').textContent = this.value.length;
});

// Premium tier visibility
document.getElementById('is_premium').addEventListener('change', function() {
    document.getElementById('premium-tier-group').style.display = this.checked ? 'block' : 'none';
});

// Image preview
function updateImagePreview(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Content Modal Functions
function openContentModal() {
    document.getElementById('content-modal').classList.add('active');
}

function closeContentModal() {
    document.getElementById('content-modal').classList.remove('active');
}

function generatePostContent() {
    const topic = document.getElementById('content-topic').value;
    const keywords = document.getElementById('content-keywords').value;

    if (!topic.trim()) {
        alert('Please enter a topic for your post');
        return;
    }

    // Show loading state
    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 loading-spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Generating...';

    // Simulate AI generation (replace with actual API call)
    setTimeout(() => {
        const content = `# ${topic}\n\nYour AI-generated content will appear here. Edit and customize it to match your unique voice and perspective.\n\n## Getting Started\n\nBegin by introducing your topic and why it matters to your readers.\n\n## Main Points\n\nDevelop your key ideas with examples and explanations.\n\n## Conclusion\n\nSummarize the main takeaways and provide actionable next steps for your readers.`;

        document.getElementById('content').value = content;
        closeContentModal();
        btn.textContent = originalText;
        btn.disabled = false;
    }, 2000);
}

// Image Modal Functions
function openImageModal() {
    document.getElementById('image-modal').classList.add('active');
}

function closeImageModal() {
    document.getElementById('image-modal').classList.remove('active');
}

function generatePostImage() {
    const description = document.getElementById('image-description').value;

    if (!description.trim()) {
        alert('Please describe the image you want to generate');
        return;
    }

    // Show loading state
    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 loading-spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Generating...';

    // Simulate image generation (replace with actual API call)
    setTimeout(() => {
        // Use a placeholder image
        const placeholderUrl = 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1200&q=80';

        // Create a temporary file-like object (you'd normally get this from your API)
        document.getElementById('preview-img').src = placeholderUrl;
        document.getElementById('image-preview').classList.remove('hidden');
        document.getElementById('image_attribution').value = 'Generated with AI';

        closeImageModal();
        btn.textContent = originalText;
        btn.disabled = false;
    }, 2000);
}

// Close modals on backdrop click
document.getElementById('content-modal').addEventListener('click', function(e) {
    if (e.target === this) closeContentModal();
});

document.getElementById('image-modal').addEventListener('click', function(e) {
    if (e.target === this) closeImageModal();
});
</script>
@endpush
