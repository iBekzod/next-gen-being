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

/* Form styling with dark mode support */
.create-post-form {
    background-color: #f9fafb;
    color: #111827;
}

.dark .create-post-form {
    background-color: #111827;
    color: #f9fafb;
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
    color: #d1d5db;
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
    background: #ffffff;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    color: #111827;
}

.dark .section-card {
    background: #1f2937;
    border-color: #374151;
    color: #f9fafb;
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
    background: #ffffff;
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px rgba(0,0,0,0.15);
    padding: 1.5rem;
    max-width: 28rem;
    width: 90%;
    color: #111827;
}

.dark .modal-box {
    background: #1f2937;
    color: #f9fafb;
}

.modal-box h3 {
    font-size: 1.125rem;
    font-weight: bold;
    margin-bottom: 1rem;
    color: #1f2937;
}

.dark .modal-box h3 {
    color: #f9fafb;
}

.modal-input {
    width: 100%;
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    font-family: inherit;
    background-color: #ffffff;
    color: #111827;
}

.dark .modal-input {
    background-color: #374151;
    color: #f9fafb;
    border-color: #4b5563;
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
    background: #e5e7eb;
    color: #1f2937;
    border: none;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}

.dark .btn-secondary {
    background: #374151;
    color: #f9fafb;
}

.btn-secondary:hover {
    background: #d1d5db;
}

.dark .btn-secondary:hover {
    background: #4b5563;
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

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

/* Form inputs with dark mode support */
.create-post-form input[type="text"],
.create-post-form input[type="email"],
.create-post-form input[type="number"],
.create-post-form input[type="date"],
.create-post-form input[type="datetime-local"],
.create-post-form textarea,
.create-post-form select {
    background-color: #ffffff;
    color: #111827;
    border-color: #d1d5db;
}

.dark .create-post-form input[type="text"],
.dark .create-post-form input[type="email"],
.dark .create-post-form input[type="number"],
.dark .create-post-form input[type="date"],
.dark .create-post-form input[type="datetime-local"],
.dark .create-post-form textarea,
.dark .create-post-form select {
    background-color: #374151;
    color: #f9fafb;
    border-color: #4b5563;
}

.create-post-form input[type="text"]::placeholder,
.create-post-form input[type="email"]::placeholder,
.create-post-form textarea::placeholder {
    color: #9ca3af;
}

.dark .create-post-form input[type="text"]::placeholder,
.dark .create-post-form input[type="email"]::placeholder,
.dark .create-post-form textarea::placeholder {
    color: #9ca3af;
}

.create-post-form label {
    color: #374151;
}

.dark .create-post-form label {
    color: #e5e7eb;
}

.create-post-form h1,
.create-post-form h2,
.create-post-form h3,
.create-post-form h4,
.create-post-form h5,
.create-post-form h6 {
    color: #111827;
}

.dark .create-post-form h1,
.dark .create-post-form h2,
.dark .create-post-form h3,
.dark .create-post-form h4,
.dark .create-post-form h5,
.dark .create-post-form h6 {
    color: #f9fafb;
}

.create-post-form p {
    color: #111827;
}

.dark .create-post-form p {
    color: #e5e7eb;
}

/* Tagify styling with dark mode support */

/* Tagify input styling */
.create-post-form #tagify-container .tagify {
    background-color: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem;
    min-height: 40px;
}

.dark .create-post-form #tagify-container .tagify {
    background-color: #374151;
    border-color: #4b5563;
}

.create-post-form #tagify-container .tagify__input {
    color: #111827;
}

.dark .create-post-form #tagify-container .tagify__input {
    color: #f9fafb;
}

.create-post-form #tagify-container .tagify__tag {
    background-color: #2563eb;
    color: white;
}

.create-post-form #tagify-container input.tagify__input {
    color: #111827;
}

.dark .create-post-form #tagify-container input.tagify__input {
    color: #f9fafb;
}

/* Dark mode overrides for inline styles */
.dark [style*="background: #f9fafb"] {
    background: #111827 !important;
}

.dark [style*="color: #111827"] {
    color: #f9fafb !important;
}

.dark [style*="color: #6b7280"] {
    color: #d1d5db !important;
}

.dark [style*="color: #374151"] {
    color: #e5e7eb !important;
}

.dark [style*="color: #9ca3af"] {
    color: #9ca3af !important;
}

.dark [style*="color: #1f2937"] {
    color: #f9fafb !important;
}

/* Writing Assistant button styles */
.assistant-btn {
    padding: 0.5rem 0.75rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.assistant-btn-grammar {
    background: #fcd34d;
    color: #92400e;
}

.dark .assistant-btn-grammar {
    background: #b45309;
    color: #fef08a;
}

.assistant-btn-style {
    background: #a5f3fc;
    color: #164e63;
}

.dark .assistant-btn-style {
    background: #0e7490;
    color: #cffafe;
}

.assistant-btn-readability {
    background: #bbf7d0;
    color: #166534;
}

.dark .assistant-btn-readability {
    background: #047857;
    color: #d1fae5;
}

.assistant-btn-tone {
    background: #fbcfe8;
    color: #831843;
}

.dark .assistant-btn-tone {
    background: #be185d;
    color: #fbf5fe;
}

/* Assistant results styling */
#assistant-results {
    background: #f3f4f6;
}

.dark #assistant-results {
    background: #374151;
    color: #f9fafb;
}

#results-title {
    color: #111827;
}

.dark #results-title {
    color: #f9fafb;
}

#results-content {
    color: #4b5563;
}

.dark #results-content {
    color: #d1d5db;
}

/* Ensure form doesn't affect navbar */
nav {
    z-index: 50 !important;
    position: sticky !important;
    top: 0 !important;
}

body {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

main {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

.create-post-form {
    position: relative;
    z-index: 1;
    margin-top: 0;
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
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827;">Post Content</h2>
                    <div style="display: flex; gap: 0.75rem;">
                        <button type="button" onclick="openContentModal()" class="ai-button">
                            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Generate with AI
                        </button>
                        <button type="button" onclick="openStructureModal()" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: linear-gradient(to right, #8b5cf6, #7c3aed); color: white; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Suggest Structure
                        </button>
                    </div>
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

            <!-- Writing Assistant Section -->
            <div class="section-card">
                <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827; margin-bottom: 1.5rem;">Writing Assistant</h2>
                <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem;">
                    Get real-time suggestions for grammar, style, readability, tone, and more.
                </p>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1.5rem;">
                    <button type="button" onclick="checkGrammar()" class="assistant-btn assistant-btn-grammar">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Check Grammar</span>
                    </button>
                    <button type="button" onclick="analyzeStyle()" class="assistant-btn assistant-btn-style">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Style Tips</span>
                    </button>
                    <button type="button" onclick="checkReadability()" class="assistant-btn assistant-btn-readability">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.5 6.253 2 10.998 2 17.25m20-11.002c5.5 0 10 4.747 10 11.002M12 6.253N12 3m0 13.002c-5.5 0-10-4.747-10-11"/>
                        </svg>
                        <span>Readability</span>
                    </button>
                    <button type="button" onclick="analyzeTone()" class="assistant-btn assistant-btn-tone">
                        <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Tone Analysis</span>
                    </button>
                </div>
                <div id="assistant-results" style="margin-top: 1rem; padding: 1rem; border-radius: 0.5rem; display: none;">
                    <h4 style="font-weight: 600; color: #111827; margin-bottom: 0.5rem;" id="results-title"></h4>
                    <div id="results-content" style="font-size: 0.875rem; color: #4b5563; line-height: 1.6;"></div>
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

            <!-- Tutorial Series Section (Optional) -->
            <div class="section-card">
                <h2 style="font-size: 1.125rem; font-weight: bold; color: #111827; margin-bottom: 1.5rem;">Tutorial Series (Optional)</h2>
                <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem;">Make this post part of a tutorial series to help readers follow a learning path.</p>

                <!-- Series Title -->
                <div class="form-group">
                    <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Series Title</label>
                    <input type="text"
                           name="series_title"
                           id="series_title"
                           value="{{ old('series_title') }}"
                           placeholder="e.g., Advanced Laravel Development"
                           style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                    <p class="input-help-text">Leave empty if this is not part of a series</p>
                    @error('series_title')
                        <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Series Part and Total Parts (shown when series_title is filled) -->
                <div id="series-details-group" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <!-- Series Part Number -->
                        <div class="form-group">
                            <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Part Number</label>
                            <input type="number"
                                   name="series_part"
                                   id="series_part"
                                   value="{{ old('series_part') }}"
                                   min="1"
                                   placeholder="e.g., 1"
                                   style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                            <p class="input-help-text">Which part is this in the series?</p>
                            @error('series_part')
                                <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Total Parts -->
                        <div class="form-group">
                            <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Total Parts</label>
                            <input type="number"
                                   name="series_total_parts"
                                   id="series_total_parts"
                                   value="{{ old('series_total_parts') }}"
                                   min="1"
                                   placeholder="e.g., 5"
                                   style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">
                            <p class="input-help-text">How many parts in total?</p>
                            @error('series_total_parts')
                                <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Series Description -->
                    <div class="form-group">
                        <label style="font-size: 0.875rem; font-weight: 500; color: #374151; display: block; margin-bottom: 0.5rem;">Series Description</label>
                        <textarea name="series_description"
                                  id="series_description"
                                  rows="2"
                                  placeholder="Brief description of what this series covers..."
                                  style="width: 100%; padding: 0.5rem 1rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-family: inherit;">{{ old('series_description') }}</textarea>
                        <p class="input-help-text">Helps readers understand the learning path</p>
                        @error('series_description')
                            <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #dc2626;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

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
        <h3>Generate Post Content with AI</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <!-- Topic Input -->
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">What is your post about?</label>
                <input type="text"
                       id="content-topic"
                       class="modal-input"
                       placeholder="e.g., Building Web Applications with Laravel">
            </div>

            <!-- Generation Type -->
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Type of Content</label>
                <select id="content-type" class="modal-input" style="cursor: pointer;">
                    <option value="full">Full Article Content</option>
                    <option value="outline">Article Outline</option>
                    <option value="introduction">Introduction Only</option>
                    <option value="conclusion">Conclusion Only</option>
                </select>
            </div>

            <!-- Tone -->
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Writing Tone</label>
                <select id="content-tone" class="modal-input" style="cursor: pointer;">
                    <option value="professional">Professional & Formal</option>
                    <option value="engaging">Engaging & Conversational</option>
                    <option value="casual">Casual & Friendly</option>
                    <option value="academic">Academic & Technical</option>
                </select>
            </div>

            <!-- Length -->
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Desired Length</label>
                <select id="content-length" class="modal-input" style="cursor: pointer;">
                    <option value="short">Short (500-800 words)</option>
                    <option value="medium" selected>Medium (1000-1500 words)</option>
                    <option value="long">Long (2000+ words)</option>
                </select>
            </div>

            <!-- Keywords -->
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Key topics to cover (optional)</label>
                <input type="text"
                       id="content-keywords"
                       class="modal-input"
                       placeholder="e.g., MVC, Database, REST API">
            </div>
            <div class="modal-buttons">
                <button type="button" onclick="generatePostContent()" class="btn-primary">
                    <span id="generate-btn-text">Generate</span>
                    <span id="generate-loading" style="display:none;" class="loading"></span>
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

<!-- AI Structure Suggestion Modal -->
<div id="structure-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>Suggest Post Structure</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                <label style="font-size: 0.875rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Post Topic (optional)</label>
                <input type="text"
                       id="structure-topic"
                       class="modal-input"
                       placeholder="Leave blank to use your post title">
            </div>
            <p style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">This will generate a template outline for your post structure. You can then use "Generate with AI" to fill in the content.</p>
            <div class="modal-buttons">
                <button type="button" onclick="generateStructure()" class="btn-primary">
                    Generate Structure
                </button>
                <button type="button" onclick="closeStructureModal()" class="btn-secondary">
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

// Series details visibility
const seriesTitleInput = document.getElementById('series_title');
const seriesDetailsGroup = document.getElementById('series-details-group');
if (seriesTitleInput) {
    // Toggle on input
    seriesTitleInput.addEventListener('input', function() {
        seriesDetailsGroup.style.display = this.value.trim() ? 'block' : 'none';
    });
    // Initialize on page load
    if (seriesTitleInput.value.trim()) {
        seriesDetailsGroup.style.display = 'block';
    }
}

// Initialize tagify if container exists
const tagifyContainer = document.getElementById('tagify-container');
let tagifyInstance = null;
if (tagifyContainer) {
    const tagifyInput = document.createElement('input');
    tagifyInput.id = 'tags-input';
    tagifyInput.value = '{{ old('tags') }}';
    tagifyInput.style.width = '100%';
    tagifyContainer.appendChild(tagifyInput);

    tagifyInstance = new Tagify(tagifyInput);
}

// Handle form submission - sync tagify data before submit
const createPostForm = document.querySelector('form[action="{{ route('posts.store') }}"]');
if (createPostForm) {
    createPostForm.addEventListener('submit', function(e) {
        // Sync Tagify data to hidden input before submission
        if (tagifyInstance) {
            const tags = tagifyInstance.getCleanData();
            const tagNames = tags.map(tag => typeof tag === 'string' ? tag : tag.value).join(',');

            // Create or update hidden input with tag data
            let tagsInput = document.getElementById('tags-hidden');
            if (!tagsInput) {
                tagsInput = document.createElement('input');
                tagsInput.type = 'hidden';
                tagsInput.name = 'tags';
                tagsInput.id = 'tags-hidden';
                this.appendChild(tagsInput);
            }
            tagsInput.value = tagNames;
        }
    });
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
    const contentType = document.getElementById('content-type').value;
    const tone = document.getElementById('content-tone').value;
    const length = document.getElementById('content-length').value;

    // Validate inputs
    if (!topic) {
        const title = document.getElementById('title').value.trim();
        if (!title) {
            alert('Please enter a topic for your post or fill in the post title');
            return;
        }
        topic = title;
    }

    const btn = event.target.closest('button');
    const originalText = btn.textContent;
    const btnText = document.getElementById('generate-btn-text');
    const btnLoading = document.getElementById('generate-loading');

    // Show loading state
    btn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'inline-block';

    // Call backend API
    fetch('/api/v1/writing/generate-content', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            topic: topic,
            type: contentType,
            tone: tone,
            length: length,
            keywords: keywords || null
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Insert generated content into textarea
            document.getElementById('content').value = data.data.content;

            // Show success message
            showNotification(`Content generated successfully! (${data.data.wordCount} words)`, 'success');

            // Clear input fields and close modal
            document.getElementById('content-topic').value = '';
            document.getElementById('content-keywords').value = '';
            closeContentModal();
        } else {
            showNotification('Failed to generate content: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error generating content: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        btn.disabled = false;
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
    });
}

// Notification helper
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background-color: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 0.5rem;
        font-weight: 500;
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
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

document.getElementById('structure-modal').addEventListener('click', function(e) {
    if (e.target === this) closeStructureModal();
});

// Structure Modal Functions
function openStructureModal() {
    document.getElementById('structure-modal').classList.add('active');
}

function closeStructureModal() {
    document.getElementById('structure-modal').classList.remove('active');
}

function generateStructure() {
    let topic = document.getElementById('structure-topic').value.trim();

    // If no topic provided, use the title from the form
    if (!topic) {
        const title = document.getElementById('title').value.trim();
        if (!title) {
            alert('Please enter a topic or use your post title');
            return;
        }
        topic = title;
    }

    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Generating...';

    // Generate structure template
    setTimeout(() => {
        const structure = `# ${topic}\n\n## Introduction\n\nWelcome to this comprehensive guide on ${topic}. In this article, we'll explore the key concepts, best practices, and practical implementations you need to know.\n\n## What is ${topic}?\n\nStart with a clear definition and context for your readers. Explain why this topic matters and who should care about it.\n\n## Key Concepts\n\n### Concept 1\nExplain the first major concept with relevant details and examples.\n\n### Concept 2\nCover the second important aspect with practical insights.\n\n### Concept 3\nProvide additional valuable information related to your topic.\n\n## Best Practices\n\n- Practice 1: Explain why this is important\n- Practice 2: Share practical tips and techniques\n- Practice 3: Provide actionable recommendations\n\n## Common Mistakes to Avoid\n\nDiscuss what readers should watch out for when working with ${topic}.\n\n## Practical Examples\n\nInclude real-world examples and code snippets where applicable.\n\n## Conclusion\n\nSummarize the key takeaways and encourage readers to implement what they've learned. Share next steps for readers interested in going deeper.`;

        document.getElementById('content').value = structure;
        closeStructureModal();
        btn.disabled = false;
        btn.textContent = originalText;
        document.getElementById('structure-topic').value = '';
    }, 2000);
}

// Writing Assistant Functions
function checkGrammar() {
    showAssistantLoading('Checking grammar...');
    setTimeout(() => {
        showAssistantResults('Grammar Check', [
            '✓ No major grammar issues found',
            'Tip: Keep sentences under 20 words',
            'Consider using more active voice'
        ]);
    }, 500);
}

function analyzeStyle() {
    showAssistantLoading('Analyzing style...');
    setTimeout(() => {
        showAssistantResults('Style Analysis', [
            'Your writing style is clear and concise',
            'Try varying sentence structure more',
            'Good use of technical terminology'
        ]);
    }, 500);
}

function checkReadability() {
    showAssistantLoading('Checking readability...');
    setTimeout(() => {
        showAssistantResults('Readability Score', [
            'Flesch Reading Ease: 60/100 (Standard)',
            'Recommended for: College graduates',
            'Consider: Breaking into shorter sections'
        ]);
    }, 500);
}

function analyzeTone() {
    showAssistantLoading('Analyzing tone...');
    setTimeout(() => {
        showAssistantResults('Tone Analysis', [
            'Detected tone: Professional & Informative',
            'Emotion: Neutral to Positive',
            'Good for: Technical documentation'
        ]);
    }, 500);
}

function showAssistantLoading(message) {
    const resultsDiv = document.getElementById('assistant-results');
    resultsDiv.style.display = 'block';
    document.getElementById('results-title').textContent = message;
    document.getElementById('results-content').innerHTML = '<p style="color: #9ca3af;">Loading...</p>';
}

function showAssistantResults(title, results) {
    document.getElementById('results-title').textContent = title;
    document.getElementById('results-content').innerHTML = results
        .map(r => `<p style="margin-bottom: 0.5rem;">• ${r}</p>`)
        .join('');
}
</script>
@endpush

@endsection
