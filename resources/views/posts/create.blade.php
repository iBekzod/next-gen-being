@extends('layouts.app')

@section('title', 'Create New Post - ' . setting('site_name'))
@section('description', 'Create a new blog post with AI assistance')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2"></script>
<script src="https://cdn.jsdelivr.net/npm/tippy.js@6"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/themes/light.css">
<link rel="stylesheet" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css">
<style>
[x-cloak] { display: none; }

/* Force light styling throughout form */
.create-post-form {
    background-color: #f9fafb !important;
    color: #111827 !important;
}

.tooltip-trigger {
    cursor: help;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
}

.input-group-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.input-help-text {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
}

.dark .input-help-text {
    color: #9ca3af;
}

.ai-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(to right, #2563eb, #4f46e5);
    color: white;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.ai-button:hover {
    background: linear-gradient(to right, #1d4ed8, #4338ca);
    box-shadow: 0 10px 15px rgba(0,0,0,0.1);
}

.ai-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.section-card {
    background: #ffffff !important;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    color: #111827 !important;
}

.form-group {
    margin-bottom: 1.5rem;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.modal-overlay.active {
    display: flex;
}

.modal-box {
    background: #ffffff !important;
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px rgba(0,0,0,0.15);
    padding: 1.5rem;
    max-width: 28rem;
    width: 90%;
    color: #111827 !important;
}

.modal-box h3 {
    font-size: 1.125rem;
    font-weight: bold;
    margin-bottom: 1rem;
    color: #1f2937 !important;
}

.modal-input {
    width: 100%;
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    font-family: inherit;
    background-color: #ffffff !important;
    color: #111827 !important;
}

.modal-buttons {
    display: flex;
    gap: 0.75rem;
}

.btn-primary {
    flex: 1;
    padding: 0.5rem 1rem;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-secondary {
    flex: 1;
    padding: 0.5rem 1rem;
    background: #e5e7eb !important;
    color: #1f2937 !important;
    border: none;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-secondary:hover {
    background: #d1d5db !important;
}

.loading {
    display: inline-block;
    animation: spin 1s linear infinite;
    width: 1rem;
    height: 1rem;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Force light theme for all form inputs */
.create-post-form input[type="text"],
.create-post-form input[type="email"],
.create-post-form input[type="number"],
.create-post-form input[type="date"],
.create-post-form input[type="datetime-local"],
.create-post-form textarea,
.create-post-form select {
    background-color: #ffffff !important;
    color: #111827 !important;
    border-color: #d1d5db !important;
}

.create-post-form input[type="text"]::placeholder,
.create-post-form input[type="email"]::placeholder,
.create-post-form textarea::placeholder {
    color: #9ca3af !important;
}

.create-post-form label {
    color: #374151 !important;
}

.create-post-form h1,
.create-post-form h2,
.create-post-form h3,
.create-post-form h4,
.create-post-form h5,
.create-post-form h6 {
    color: #111827 !important;
}

.create-post-form p,
.create-post-form span,
.create-post-form div {
    color: #111827 !important;
}

/* Section styling */
.create-post-form .section-card {
    background-color: #ffffff !important;
    color: #111827 !important;
    border: 1px solid #e5e7eb !important;
}

.create-post-form .form-group {
    color: #111827 !important;
}

/* Ensure all text in the form is dark */
.create-post-form {
    color: #111827 !important;
}

.create-post-form * {
    color: #111827 !important;
}

/* Override any dark mode text */
.create-post-form .dark,
.create-post-form .dark * {
    color: #111827 !important;
    background-color: #ffffff !important;
}

/* Tagify input styling */
.create-post-form #tagify-container .tagify {
    background-color: #ffffff !important;
    border: 1px solid #d1d5db !important;
    border-radius: 0.375rem !important;
    padding: 0.5rem !important;
    min-height: 40px !important;
}

.create-post-form #tagify-container .tagify__input {
    color: #111827 !important;
}

.create-post-form #tagify-container .tagify__tag {
    background-color: #2563eb !important;
    color: white !important;
}

.create-post-form #tagify-container input.tagify__input {
    color: #111827 !important;
}

/* Ensure form doesn't affect navbar */
nav {
    z-index: 50 !important;
}

.create-post-form {
    position: relative;
    z-index: 1;
}
</style>
@endpush

@section('content')
<div style="min-height: 100vh; background: #f9fafb; padding: 2rem 1rem;" class="create-post-form">
    <div style="max-width: 56rem; margin: 0 auto; color: #111827;">
        <!-- Header -->
        <div style="margin-bottom: 2rem;">
            <h1 style="font-size: 1.875rem; font-weight: bold; color: #111827; margin-bottom: 0.5rem;">Create Post</h1>
            <p style="color: #6b7280;">Share your knowledge and grow your audience</p>
        </div>

        <!-- Form -->
        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Post Information -->
            <div class="section-card">
                <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827; margin-bottom: 1.5rem;">Post Information</h2>

                <!-- Title -->
                <div class="form-group">
                    <div class="input-group-label">
                        <label style="font-size: 0.875rem; font-weight: 500; color: #374151;">Title</label>
                        <span style="color: #dc2626;">*</span>
                        <div class="tooltip-trigger" title="Give your post a clear, descriptive title. Good titles help readers find your post and improve search visibility.">
                            <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="currentColor" viewBox="0 0 20 20">
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
                           style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                    <p class="input-help-text">40-70 characters recommended for SEO</p>
                    @error('title')
                        <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Excerpt -->
                <div class="form-group">
                    <div class="input-group-label">
                        <label style="font-size: 0.875rem; font-weight: 500; color: #374151;">Description</label>
                        <span style="color: #dc2626;">*</span>
                        <div class="tooltip-trigger" title="A short summary of your post. This appears in search results and when people share your post.">
                            <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="currentColor" viewBox="0 0 20 20">
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
                              style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">{{ old('excerpt') }}</textarea>
                    <p class="input-help-text">
                        <span id="excerpt-count">0</span>/500 characters
                    </p>
                    @error('excerpt')
                        <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div class="form-group">
                    <div class="input-group-label">
                        <label style="font-size: 0.875rem; font-weight: 500; color: #374151;">Category</label>
                        <span style="color: #dc2626;">*</span>
                        <div class="tooltip-trigger" title="Choose the topic that best describes your post.">
                            <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <select name="category_id" id="category_id" required style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Content Section -->
            <div class="section-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827;">Post Content</h2>
                    <button type="button" onclick="openContentModal()" class="ai-button">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generate with AI
                    </button>
                </div>

                <!-- Content Editor -->
                <div class="form-group">
                    <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">
                        Content <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea name="content"
                              id="content"
                              rows="12"
                              required
                              placeholder="Write your post content here. You can format it with **bold**, *italic*, and line breaks."
                              style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: monospace; font-size: 0.875rem;">{{ old('content') }}</textarea>
                    <p class="input-help-text">Use markdown formatting: **bold**, *italic*, [link text](URL)</p>
                    @error('content')
                        <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Media Section -->
            <div class="section-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827;">Featured Image</h2>
                    <button type="button" onclick="openImageModal()" class="ai-button">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Generate Image
                    </button>
                </div>

                <!-- Image Upload -->
                <div class="form-group">
                    <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">
                        Featured Image
                    </label>
                    <div style="border: 2px dashed #d1d5db; border-radius: 0.5rem; padding: 1.5rem; text-align: center; cursor: pointer;" onclick="document.getElementById('featured_image').click()">
                        <svg style="width: 2rem; height: 2rem; margin: 0 auto 0.5rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p style="font-size: 0.875rem; font-weight: 500; color: #374151; margin: 0;">Click to upload or drag and drop</p>
                        <p style="font-size: 0.75rem; color: #9ca3af; margin: 0;">PNG, JPG up to 2MB</p>
                        <input type="file"
                               name="featured_image"
                               id="featured_image"
                               accept="image/*"
                               style="display: none;"
                               onchange="updateImagePreview(this)">
                    </div>
                    <div id="image-preview" style="margin-top: 1rem; display: none;">
                        <img id="preview-img" src="" alt="Preview" style="max-height: 12rem; border-radius: 0.5rem;">
                    </div>
                    @error('featured_image')
                        <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Attribution -->
                <div class="form-group">
                    <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Image Credit</label>
                    <input type="text"
                           name="image_attribution"
                           id="image_attribution"
                           value="{{ old('image_attribution') }}"
                           placeholder="e.g., Photo by John Doe on Unsplash"
                           style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                    <p class="input-help-text">Give credit to the photographer or designer</p>
                </div>
            </div>

            <!-- Tags Section -->
            @if(isset($tags))
            <div class="section-card">
                <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827; margin-bottom: 1.5rem;">Tags</h2>
                <div class="form-group">
                    <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Add tags to your post</label>
                    <input type="hidden" name="tags" id="tags" value="{{ old('tags') }}">
                    <div id="tagify-container" style="width: 100%;"></div>
                    <p class="input-help-text">Type and press Enter to add tags</p>
                </div>
            </div>
            @endif

            <!-- Publishing Section -->
            <div class="section-card">
                <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827; margin-bottom: 1.5rem;">Publishing Settings</h2>

                <!-- Status -->
                <div class="form-group">
                    <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Status</label>
                    <select name="status" id="status" style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                        <option value="draft" selected>Draft (save for later)</option>
                        <option value="published">Publish Now</option>
                    </select>
                </div>

                <!-- Premium Content -->
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="is_premium" id="is_premium" value="1" style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid #d1d5db;">
                        <span style="font-size: 0.875rem; font-weight: 500; color: #374151;">Premium Content</span>
                        <div class="tooltip-trigger" title="Make this post exclusive to premium subscribers.">
                            <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                </div>

                <!-- Premium Tier -->
                <div class="form-group" id="premium-tier-group" style="display: none;">
                    <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Tier</label>
                    <select name="premium_tier" id="premium_tier" style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                        <option value="basic">Basic</option>
                        <option value="pro">Pro</option>
                        <option value="team">Team</option>
                    </select>
                </div>

                <!-- Allow Comments -->
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="allow_comments" value="1" checked style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid #d1d5db;">
                        <span style="font-size: 0.875rem; font-weight: 500; color: #374151;">Allow Comments</span>
                        <div class="tooltip-trigger" title="Let readers discuss your post in the comments section.">
                            <svg style="width: 1rem; height: 1rem; color: #9ca3af;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn-primary" style="flex: 1; padding: 0.75rem 1rem;">
                    Publish Post
                </button>
                <a href="{{ route('posts.index') }}" class="btn-secondary" style="flex: 1; padding: 0.75rem 1rem; text-align: center; text-decoration: none;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<!-- AI Content Generation Modal -->
<div id="content-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>Generate Post Content</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">What is your post about?</label>
                <input type="text"
                       id="content-topic"
                       class="modal-input"
                       placeholder="e.g., Building Web Applications with Laravel">
            </div>
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Key topics to cover (optional)</label>
                <input type="text"
                       id="content-keywords"
                       class="modal-input"
                       placeholder="e.g., MVC, Database, REST API">
            </div>
            <div class="modal-buttons">
                <button type="button" onclick="generatePostContent()" class="btn-primary">
                    Generate
                </button>
                <button type="button" onclick="closeContentModal()" class="btn-secondary">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- AI Image Generation Modal -->
<div id="image-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>Generate Featured Image</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Describe your ideal image</label>
                <textarea id="image-description"
                          rows="3"
                          class="modal-input"
                          placeholder="e.g., A modern laptop with code on the screen, professional office setting, bright lighting"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" onclick="generatePostImage()" class="btn-primary">
                    Generate
                </button>
                <button type="button" onclick="closeImageModal()" class="btn-secondary">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/@yaireo/tagify"></script>
<script>
// Initialize tooltips
document.querySelectorAll('.tooltip-trigger').forEach(el => {
    tippy(el, {
        content: el.getAttribute('title'),
        theme: 'light',
        placement: 'right',
    });
});

// Excerpt counter
const excerptInput = document.getElementById('excerpt');
if (excerptInput) {
    excerptInput.addEventListener('input', function() {
        document.getElementById('excerpt-count').textContent = this.value.length;
    });
    document.getElementById('excerpt-count').textContent = excerptInput.value.length;
}

// Premium tier visibility
document.getElementById('is_premium').addEventListener('change', function() {
    document.getElementById('premium-tier-group').style.display = this.checked ? 'block' : 'none';
});

// Initialize tagify if container exists
const tagifyContainer = document.getElementById('tagify-container');
if (tagifyContainer) {
    const tagifyInput = document.createElement('input');
    tagifyInput.name = 'tags';
    tagifyInput.id = 'tags-input';
    tagifyInput.value = '{{ old('tags') }}';
    tagifyInput.style.width = '100%';
    tagifyContainer.appendChild(tagifyInput);

    new Tagify(tagifyInput);
}

// Image preview
function updateImagePreview(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
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
    let topic = document.getElementById('content-topic').value.trim();
    const keywords = document.getElementById('content-keywords').value.trim();

    // If no topic provided, use the title from the form
    if (!topic) {
        const title = document.getElementById('title').value.trim();
        if (!title) {
            alert('Please enter a topic for your post or fill in the post title');
            return;
        }
        topic = title;
    }

    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Generating...';

    // Simulate AI generation with more contextual content
    setTimeout(() => {
        const keywordList = keywords ? `\n\nKey topics covered: ${keywords}` : '';

        const content = `# ${topic}\n\n## Introduction\n\nWelcome to this comprehensive guide on ${topic}. In this article, we'll explore the key concepts, best practices, and practical implementations you need to know.${keywordList}\n\n## What is ${topic}?\n\nStart with a clear definition and context for your readers. Explain why this topic matters and who should care about it.\n\n## Key Concepts\n\n### Concept 1\nExplain the first major concept with relevant details and examples.\n\n### Concept 2\nCover the second important aspect with practical insights.\n\n### Concept 3\nProvide additional valuable information related to your topic.\n\n## Best Practices\n\n- Practice 1: Explain why this is important\n- Practice 2: Share practical tips and techniques\n- Practice 3: Provide actionable recommendations\n\n## Common Mistakes to Avoid\n\nDiscuss what readers should watch out for when working with ${topic}.\n\n## Practical Examples\n\nInclude real-world examples and code snippets where applicable.\n\n## Conclusion\n\nSummarize the key takeaways and encourage readers to implement what they've learned. Share next steps for readers interested in going deeper.`;

        document.getElementById('content').value = content;
        closeContentModal();
        btn.disabled = false;
        btn.textContent = originalText;
        document.getElementById('content-topic').value = '';
        document.getElementById('content-keywords').value = '';
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
    let description = document.getElementById('image-description').value.trim();

    // If no description provided, use the title from the form
    if (!description) {
        const title = document.getElementById('title').value.trim();
        if (!title) {
            alert('Please describe the image you want to generate or fill in the post title');
            return;
        }
        description = `A professional featured image for an article about: ${title}`;
    }

    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Generating...';

    // Simulate image generation
    setTimeout(() => {
        // Use different placeholder images based on keywords in the description
        const descriptions = description.toLowerCase();
        let placeholderUrl = 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1200&q=80';

        if (descriptions.includes('code') || descriptions.includes('web') || descriptions.includes('programming')) {
            placeholderUrl = 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1200&q=80';
        } else if (descriptions.includes('business') || descriptions.includes('startup')) {
            placeholderUrl = 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&q=80';
        } else if (descriptions.includes('design') || descriptions.includes('creative')) {
            placeholderUrl = 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=1200&q=80';
        } else if (descriptions.includes('data') || descriptions.includes('analysis')) {
            placeholderUrl = 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=80';
        }

        document.getElementById('preview-img').src = placeholderUrl;
        document.getElementById('image-preview').style.display = 'block';
        document.getElementById('image_attribution').value = 'Generated with AI based on: ' + description;

        closeImageModal();
        btn.disabled = false;
        btn.textContent = originalText;
        document.getElementById('image-description').value = '';
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
