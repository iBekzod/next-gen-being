@extends('layouts.app')

@section('title', 'Edit Post - ' . setting('site_name'))
@section('description', 'Edit your blog post')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css">
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Post</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Update your post content and settings</p>
        </div>

        <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $post->title) }}"
                       required
                       placeholder="Enter your post title"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('title')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            @if(isset($suggestion))
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                        Creating from AI Suggestion
                    </h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Title suggestion: {{ $suggestion->title }}
                    </p>
                </div>
                <script>
                    // Pre-fill title if empty
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
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('excerpt', $post->excerpt) }}</textarea>
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
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                        <div class="flex space-x-2">
                            <button type="button" onclick="formatText('bold')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 4a2 2 0 00-2 2v8a2 2 0 002 2h3.5a3.5 3.5 0 001.852-6.49A3.5 3.5 0 008.5 4H6zm2 9V7h.5a1.5 1.5 0 010 3H8zm0 0h1a1.5 1.5 0 010 3H8v-3z"/>
                                </svg>
                            </button>
                            <button type="button" onclick="formatText('italic')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7 2a1 1 0 011 1v1h4V3a1 1 0 112 0v1h1a1 1 0 110 2h-1.003l-.8 10H14a1 1 0 110 2H6a1 1 0 110-2h1.003l.8-10H7a1 1 0 110-2h1V3a1 1 0 011-1zm2.197 4l-.8 10h3.406l.8-10H9.197z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            <button type="button" onclick="formatText('heading')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                <span class="font-bold text-sm">H</span>
                            </button>
                            <button type="button" onclick="formatText('link')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </button>
                            <button type="button" onclick="formatText('code')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <textarea name="content"
                              id="content"
                              rows="20"
                              required
                              placeholder="Write your post content here... (Markdown supported)"
                              class="w-full px-4 py-3 border-0 dark:bg-gray-700 dark:text-white focus:ring-0">{{ old('content', $post->content) }}</textarea>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Supports Markdown formatting. Estimated read time: <span id="read-time">0</span> min
                </p>
                @error('content')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id"
                            id="category_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                            id="status"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="scheduled" {{ old('status', $post->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Tags
                </label>
                <input name="tags"
                       id="tags"
                       placeholder="Add tags..."
                       value="{{ old('tags') ? implode(',', old('tags')) : $post->tags->pluck('name')->implode(',') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Start typing to see suggestions or create new tags
                </p>
                @error('tags')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Featured Image -->
            <div>
                <label for="featured_image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Featured Image
                </label>

                @if($post->featured_image)
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Current image:</p>
                    <img src="{{ $post->featured_image }}" alt="Current featured image" class="max-h-48 rounded-lg">
                </div>
                @endif

                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg dark:border-gray-600">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                            <label for="featured_image" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload a new file</span>
                                <input id="featured_image" name="featured_image" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF up to 2MB</p>
                    </div>
                </div>
                <div id="image-preview" class="mt-4 hidden">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">New image preview:</p>
                    <img src="" alt="Preview" class="max-h-64 mx-auto rounded-lg">
                </div>
                @error('featured_image')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Scheduled Date (shown only when status is scheduled) -->
            <div id="scheduled-date" class="{{ $post->status !== 'scheduled' ? 'hidden' : '' }}">
                <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Publish Date & Time
                </label>
                <input type="datetime-local"
                       name="published_at"
                       id="published_at"
                       value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                       min="{{ now()->addMinutes(10)->format('Y-m-d\TH:i') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('published_at')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options -->
            <div class="space-y-4">
                <label class="flex items-center">
                    <input type="checkbox"
                           name="is_premium"
                           value="1"
                           {{ old('is_premium', $post->is_premium) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Premium content (requires subscription)
                    </span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox"
                           name="allow_comments"
                           value="1"
                           {{ old('allow_comments', $post->allow_comments) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Allow comments on this post
                    </span>
                </label>
            </div>

            <!-- Post Info -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Post Information</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $post->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $post->updated_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    @if($post->published_at && $post->status === 'published')
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Published</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $post->published_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Views</dt>
                        <dd class="text-gray-900 dark:text-white">{{ number_format($post->views_count) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Likes</dt>
                        <dd class="text-gray-900 dark:text-white">{{ number_format($post->likes_count) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Comments</dt>
                        <dd class="text-gray-900 dark:text-white">{{ number_format($post->comments_count) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard.posts') }}"
                       class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        Cancel
                    </a>
                    @if($post->status === 'published')
                    <a href="{{ route('posts.show', $post->slug) }}"
                       target="_blank"
                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        View Post
                    </a>
                    @endif
                </div>
                <div class="flex space-x-3">
                    <button type="submit"
                            name="action"
                            value="save"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/@yaireo/tagify"></script>
<script>
// Character count for excerpt
const excerptTextarea = document.getElementById('excerpt');
const excerptCount = document.getElementById('excerpt-count');
excerptTextarea.addEventListener('input', function() {
    excerptCount.textContent = this.value.length;
});
excerptCount.textContent = excerptTextarea.value.length;

// Read time calculation
const contentTextarea = document.getElementById('content');
const readTimeSpan = document.getElementById('read-time');
function calculateReadTime() {
    const words = contentTextarea.value.trim().split(/\s+/).length;
    const readTime = Math.max(1, Math.ceil(words / 200));
    readTimeSpan.textContent = readTime;
}
contentTextarea.addEventListener('input', calculateReadTime);
calculateReadTime();

// Show/hide scheduled date field
const statusSelect = document.getElementById('status');
const scheduledDateDiv = document.getElementById('scheduled-date');
statusSelect.addEventListener('change', function() {
    if (this.value === 'scheduled') {
        scheduledDateDiv.classList.remove('hidden');
        document.getElementById('published_at').required = true;
    } else {
        scheduledDateDiv.classList.add('hidden');
        document.getElementById('published_at').required = false;
    }
});

// Initialize scheduled date visibility
if (statusSelect.value === 'scheduled') {
    document.getElementById('published_at').required = true;
}

// Image preview
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const previewImg = preview.querySelector('img');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }

        reader.readAsDataURL(input.files[0]);
    }
}

// Tags input
const tagsInput = document.querySelector('input[name=tags]');
const tagify = new Tagify(tagsInput, {
    whitelist: {!! json_encode($tags->pluck('name')->toArray()) !!},
    dropdown: {
        maxItems: 20,
        classname: 'tags-look',
        enabled: 0,
        closeOnSelect: false
    }
});

// Set existing tags
const existingTags = {!! json_encode($post->tags->pluck('name')->toArray()) !!};
tagify.addTags(existingTags);

// Text formatting functions
function formatText(command) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    let replacement = '';

    switch(command) {
        case 'bold':
            replacement = `**${selectedText}**`;
            break;
        case 'italic':
            replacement = `*${selectedText}*`;
            break;
        case 'heading':
            replacement = `## ${selectedText}`;
            break;
        case 'link':
            const url = prompt('Enter URL:');
            if (url) {
                replacement = `[${selectedText}](${url})`;
            } else {
                return;
            }
            break;
        case 'code':
            if (selectedText.includes('\n')) {
                replacement = '```\n' + selectedText + '\n```';
            } else {
                replacement = '`' + selectedText + '`';
            }
            break;
    }

    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + replacement.length, start + replacement.length);
}
</script>
@endpush
@endsection
