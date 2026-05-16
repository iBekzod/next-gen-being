{{-- Shared post editor partial. Required vars: $formAction, $isEdit (bool), $post (optional Post), $categories, $tags, $allPosts, $premiumTiers --}}
@php
    $isEdit = $isEdit ?? false;
    $post = $post ?? null;
    $tagsArr = $post && $post->relationLoaded('tags') ? $post->tags->pluck('name')->toArray() : ($post ? $post->tags()->pluck('name')->toArray() : []);
    $tagsCsv = old('tags', $isEdit ? implode(',', $tagsArr) : '');
    $seriesMode = old('series_mode', $isEdit && $post && $post->series_title ? 'new' : '');
    $popularTags = isset($tags) ? $tags->pluck('name')->toArray() : [];
    // image_attribution may be cast as array on Post model — flatten to display string
    $rawAttribution = old('image_attribution', $post->image_attribution ?? '');
    if (is_array($rawAttribution)) {
        $attributionValue = $rawAttribution['name'] ?? $rawAttribution['author'] ?? $rawAttribution['text'] ?? '';
    } else {
        $attributionValue = $rawAttribution;
    }
@endphp

@push('head')
<link rel="stylesheet" href="https://unpkg.com/@yaireo/tagify/dist/tagify.css">
<style>
[x-cloak]{display:none}
.ed-mono{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;tab-size:4}
details>summary{list-style:none;cursor:pointer}
details>summary::-webkit-details-marker{display:none}
details[open] .chev{transform:rotate(180deg)}
.tb-btn{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:6px;color:#475569;cursor:pointer;border:0;background:transparent}
.tb-btn:hover{background:#e2e8f0;color:#0f172a}
.dark .tb-btn{color:#94a3b8}
.dark .tb-btn:hover{background:#1e293b;color:#f1f5f9}
.tb-divider{width:1px;height:20px;background:#e2e8f0;margin:0 4px}
.dark .tb-divider{background:#334155}
.preview-prose{font-size:15px;line-height:1.7;color:#1f2937}
.dark .preview-prose{color:#e5e7eb}
.preview-prose h1{font-size:1.875em;font-weight:700;margin:1em 0 .5em;line-height:1.2}
.preview-prose h2{font-size:1.5em;font-weight:700;margin:1.5em 0 .5em;line-height:1.2}
.preview-prose h3{font-size:1.25em;font-weight:600;margin:1.25em 0 .5em}
.preview-prose p{margin:.75em 0}
.preview-prose a{color:#2563eb;text-decoration:underline}
.preview-prose strong{font-weight:700}.preview-prose em{font-style:italic}
.preview-prose ul,.preview-prose ol{margin:.75em 0;padding-left:1.5em}
.preview-prose ul{list-style:disc}.preview-prose ol{list-style:decimal}
.preview-prose code{background:#f1f5f9;padding:.15em .4em;border-radius:4px;font-size:.9em;font-family:ui-monospace,monospace}
.dark .preview-prose code{background:#1e293b}
.preview-prose pre{background:#0f172a;color:#e2e8f0;padding:1em;border-radius:8px;overflow-x:auto;margin:1em 0;font-size:.85em}
.preview-prose pre code{background:transparent;color:inherit;padding:0}
.preview-prose blockquote{border-left:4px solid #3b82f6;padding-left:1em;color:#64748b;margin:1em 0;font-style:italic}
.preview-prose img{max-width:100%;border-radius:8px;margin:1em 0}
.preview-prose hr{border:0;border-top:1px solid #e2e8f0;margin:2em 0}
.dark .preview-prose hr{border-top-color:#334155}
.preview-prose table{border-collapse:collapse;margin:1em 0;width:100%}
.preview-prose th,.preview-prose td{border:1px solid #e2e8f0;padding:.5em .75em}
.preview-prose th{background:#f8fafc;font-weight:600}
.dark .preview-prose th{background:#1e293b}
.dark .preview-prose th,.dark .preview-prose td{border-color:#334155}
.focus-mode aside{display:none}
.focus-mode .editor-col{grid-column:span 3/span 3;max-width:48rem;margin:0 auto}
.health-row{display:flex;align-items:center;gap:.5rem;padding:.375rem 0;font-size:.8125rem}
.health-row .dot{width:14px;height:14px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center}
.health-row.ok .dot{background:#dcfce7;color:#16a34a}
.health-row.warn .dot{background:#fef3c7;color:#d97706}
.health-row.bad .dot{background:#fee2e2;color:#dc2626}
.dark .health-row.ok .dot{background:rgba(34,197,94,.2)}
.dark .health-row.warn .dot{background:rgba(245,158,11,.2)}
.dark .health-row.bad .dot{background:rgba(239,68,68,.2)}
.tagify{--tag-bg:#dbeafe;--tag-text-color:#1e40af;border-radius:.5rem;border-color:#d1d5db}
.dark .tagify{--tag-bg:rgba(59,130,246,.2);--tag-text-color:#93c5fd;border-color:#334155;background:#0f172a}
.dark .tagify__input{color:#e5e7eb}
</style>
@endpush

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" id="post-form" class="block" data-edit="{{ $isEdit ? '1' : '0' }}">
    @csrf
    @if($isEdit) @method('PUT') @endif
    <input type="hidden" name="status" value="{{ $isEdit && $post ? $post->status : 'draft' }}">

    {{-- Sticky toolbar --}}
    <div class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-gray-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 py-2.5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ url()->previous() }}" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Back">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight">{{ $isEdit ? 'Edit post' : 'New post' }}</div>
                    <div class="text-xs text-gray-500 flex items-center gap-2">
                        <span id="ls-words">0</span> words
                        <span class="text-gray-300 dark:text-gray-600">·</span>
                        <span id="ls-read">1</span> min read
                        <span class="text-gray-300 dark:text-gray-600">·</span>
                        <span id="save-state" class="text-gray-400">{{ $isEdit ? 'Loaded' : 'Not saved' }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="focus-toggle" title="Focus mode (F1)" class="hidden sm:inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </button>
                <div class="hidden sm:flex bg-gray-100 dark:bg-slate-800 rounded-lg p-0.5" id="view-mode">
                    <button type="button" data-mode="write" class="px-2.5 py-1 text-xs font-semibold rounded-md bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm">Write</button>
                    <button type="button" data-mode="split" class="px-2.5 py-1 text-xs font-semibold rounded-md text-gray-600 dark:text-gray-400">Split</button>
                    <button type="button" data-mode="preview" class="px-2.5 py-1 text-xs font-semibold rounded-md text-gray-600 dark:text-gray-400">Preview</button>
                </div>
                <button type="submit" name="action" value="save_draft" class="px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700">
                    <span class="hidden sm:inline">Save </span>draft
                </button>
                <button type="submit" name="action" value="publish" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">
                    {{ $isEdit ? 'Update' : 'Publish' }}
                </button>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 dark:bg-slate-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Editor column --}}
            <div class="lg:col-span-2 space-y-4 editor-col">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 sm:p-7">
                    <input type="text" name="title" id="title" value="{{ old('title', $post->title ?? '') }}" required maxlength="255"
                           placeholder="Post title"
                           class="w-full text-3xl sm:text-4xl font-bold tracking-tight bg-transparent border-0 focus:ring-0 focus:outline-none px-0 placeholder-gray-300 dark:placeholder-slate-600 text-gray-900 dark:text-white">
                    <div class="flex items-center justify-between mt-1 mb-3 text-xs text-gray-400">
                        <span id="slug-preview" class="font-mono text-blue-600 dark:text-blue-400 truncate"></span>
                        <span><span id="title-count">0</span>/255</span>
                    </div>
                    @error('title')<p class="text-sm text-red-600 mb-3">{{ $message }}</p>@enderror

                    <textarea name="excerpt" id="excerpt" required rows="2" maxlength="500"
                              placeholder="One-line description for search results and social shares..."
                              class="w-full text-lg bg-transparent border-0 border-t border-gray-100 dark:border-slate-700 pt-4 focus:ring-0 focus:outline-none px-0 resize-none placeholder-gray-300 dark:placeholder-slate-600 text-gray-700 dark:text-gray-300">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                    <div class="flex items-center justify-between mt-1 text-xs text-gray-400">
                        <span>Search snippet · social previews</span>
                        <span><span id="excerpt-count">0</span>/500</span>
                    </div>
                    @error('excerpt')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Content + toolbar --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
                    <div class="flex items-center justify-between px-3 py-1.5 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
                        <div class="flex items-center flex-wrap gap-0.5">
                            <button type="button" class="tb-btn" data-md="bold" title="Bold (⌘B)"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6 4h5a3 3 0 012 5.2A3.5 3.5 0 0111 16H6V4zm2 2v3h3a1.5 1.5 0 100-3H8zm0 5v3h3a1.5 1.5 0 100-3H8z"/></svg></button>
                            <button type="button" class="tb-btn" data-md="italic" title="Italic (⌘I)"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M8 4h6v2h-2.2l-2.6 8H11v2H5v-2h2.2l2.6-8H8V4z"/></svg></button>
                            <span class="tb-divider"></span>
                            <button type="button" class="tb-btn" data-md="h2" title="Heading"><span class="text-xs font-bold">H</span></button>
                            <button type="button" class="tb-btn" data-md="quote" title="Quote"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 6c0-1.1.9-2 2-2h2v4H6v4H4V6zm8 0c0-1.1.9-2 2-2h2v4h-2v4h-2V6z"/></svg></button>
                            <button type="button" class="tb-btn" data-md="link" title="Link (⌘K)"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg></button>
                            <span class="tb-divider"></span>
                            <button type="button" class="tb-btn" data-md="code" title="Inline code"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg></button>
                            <button type="button" class="tb-btn" data-md="codeblock" title="Code block"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l-4 3 4 3m8-6l4 3-4 3M14 4l-4 16"/></svg></button>
                            <span class="tb-divider"></span>
                            <button type="button" class="tb-btn" data-md="ul" title="Bulleted list"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h.01M4 12h.01M4 18h.01M8 6h12M8 12h12M8 18h12"/></svg></button>
                            <button type="button" class="tb-btn" data-md="ol" title="Numbered list"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></button>
                            <button type="button" class="tb-btn" data-md="hr" title="Divider"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></button>
                            <span class="tb-divider"></span>
                            <button type="button" class="tb-btn" data-md="table" title="Table"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-9v18M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg></button>
                        </div>
                        <div class="hidden sm:flex items-center gap-3 text-xs text-gray-500">
                            <span><b id="stat-words">0</b> words</span>
                            <span class="text-gray-300 dark:text-gray-600">·</span>
                            <span><b id="stat-headings">0</b> headings</span>
                            <span class="text-gray-300 dark:text-gray-600">·</span>
                            <span><b id="stat-links">0</b> links</span>
                        </div>
                    </div>

                    <div id="edit-panes" class="grid grid-cols-1 min-h-[480px]">
                        <textarea name="content" id="content" required
                                  placeholder="Write your post in markdown. Use **bold**, *italic*, [links](url), code blocks (```)..."
                                  class="ed-mono w-full p-5 bg-transparent border-0 focus:ring-0 focus:outline-none text-sm leading-relaxed text-gray-800 dark:text-gray-200 min-h-[480px]">{{ old('content', $post->content ?? '') }}</textarea>
                        <div id="preview" class="hidden p-5 bg-gray-50/50 dark:bg-slate-900/30 border-l border-gray-200 dark:border-slate-700 overflow-auto min-h-[480px] preview-prose"></div>
                    </div>
                    @error('content')<p class="text-sm text-red-600 mt-2 px-5 pb-3">{{ $message }}</p>@enderror
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pre-publish checklist</h3>
                        <span id="health-score" class="text-xs font-bold text-gray-500">0/8</span>
                    </div>
                    <div id="health-list" class="grid grid-cols-1 sm:grid-cols-2 gap-x-6"></div>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-4 lg:sticky lg:top-20 self-start">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 space-y-4">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 block mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" id="category_id" required
                                class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white">
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $post->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 block mb-2">Tags</label>
                        <input type="text" name="tags" id="tags" value="{{ $tagsCsv }}"
                               placeholder="laravel, php, api"
                               class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white">
                        <p class="text-xs text-gray-400 mt-1">Up to 5 tags · comma separated</p>
                    </div>
                </div>

                <details class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700" {{ ($isEdit && $post && $post->featured_image) ? 'open' : '' }}>
                    <summary class="px-5 py-4 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Cover image</span>
                        <svg class="w-4 h-4 text-gray-400 chev transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-5 pb-5 space-y-3">
                        <div id="cover-preview-wrap" class="{{ ($isEdit && $post && $post->featured_image) ? '' : 'hidden' }}">
                            <img id="cover-preview" src="{{ $isEdit && $post ? $post->featured_image : '' }}" alt="" class="w-full h-32 object-cover rounded-lg bg-gray-100">
                            @if($isEdit && $post && $post->featured_image)
                                <p class="text-xs text-gray-400 mt-2">Current cover · choose a new file to replace</p>
                            @endif
                        </div>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*"
                               class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/30 dark:file:text-blue-300">
                        <input type="text" name="image_attribution" value="{{ $attributionValue }}"
                               placeholder="Image credit (optional)"
                               class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                        <p class="text-xs text-gray-400">Max 2MB · 1200×630 recommended</p>
                        @error('featured_image')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </details>

                <details class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700" {{ $seriesMode ? 'open' : '' }}>
                    <summary class="px-5 py-4 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Tutorial series</span>
                        <svg class="w-4 h-4 text-gray-400 chev transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-5 pb-5 space-y-3">
                        <select name="series_mode" id="series_mode" class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                            <option value="" {{ $seriesMode === '' ? 'selected' : '' }}>No series</option>
                            <option value="new" {{ $seriesMode === 'new' ? 'selected' : '' }}>{{ $isEdit ? 'Edit series info' : 'Create new series' }}</option>
                            @if(!$isEdit)
                            <option value="existing" {{ $seriesMode === 'existing' ? 'selected' : '' }}>Add to existing series</option>
                            @endif
                        </select>
                        <div id="new-series-group" style="display:none" class="space-y-3">
                            <input type="text" name="series_title" id="series_title" value="{{ old('series_title', $post->series_title ?? '') }}"
                                   placeholder="Series title"
                                   class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="series_part" id="series_part" value="{{ old('series_part', $post->series_part ?? '') }}" min="1"
                                       placeholder="Part #"
                                       class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                                <input type="number" name="series_total_parts" id="series_total_parts" value="{{ old('series_total_parts', $post->series_total_parts ?? '') }}" min="1"
                                       placeholder="Total"
                                       class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                            </div>
                            <textarea name="series_description" id="series_description" rows="2"
                                      placeholder="Series description (optional)"
                                      class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">{{ old('series_description', $post->series_description ?? '') }}</textarea>
                        </div>
                        @if(!$isEdit)
                        @php
                            $seriesPosts = collect($allPosts)->filter(fn($p) => !empty($p->series_title))->groupBy('series_title');
                        @endphp
                        <div id="existing-series-group" style="display:none">
                            @if($seriesPosts->isEmpty())
                                <div class="p-3 bg-gray-50 dark:bg-slate-900 rounded-lg text-xs text-gray-500 dark:text-gray-400">
                                    You have no past series yet. Switch to <strong>Create new series</strong> above to start one.
                                </div>
                            @else
                                <select name="existing_post_id" id="existing_post_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                                    <option value="">-- Pick the latest part of a series --</option>
                                    @foreach($seriesPosts as $seriesTitle => $parts)
                                        @php $sortedParts = $parts->sortBy('series_part'); @endphp
                                        <optgroup label="{{ $seriesTitle }}">
                                            @foreach($sortedParts as $p)
                                                <option value="{{ $p->id }}"
                                                        data-series-title="{{ htmlspecialchars($p->series_title ?? '', ENT_QUOTES) }}"
                                                        data-series-part="{{ (int)($p->series_part ?? 0) }}"
                                                        data-series-total="{{ (int)($p->series_total_parts ?? 0) }}"
                                                        data-series-description="{{ htmlspecialchars($p->series_description ?? '', ENT_QUOTES) }}">
                                                    Part {{ $p->series_part }} — {{ \Illuminate\Support\Str::limit($p->title, 60) }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Picking the latest part auto-fills the next part number.</p>
                                <div id="series-info-display" class="mt-3 p-3 hidden rounded-lg bg-blue-50 dark:bg-blue-900/20 text-xs text-blue-900 dark:text-blue-200"></div>
                            @endif
                        </div>
                        @endif
                    </div>
                </details>

                <details class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700">
                    <summary class="px-5 py-4 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Visibility</span>
                        <svg class="w-4 h-4 text-gray-400 chev transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="px-5 pb-5 space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="is_premium" id="is_premium" value="1" {{ old('is_premium', ($post && $post->is_premium) ? '1' : null) ? 'checked' : '' }}
                                   class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Premium content</span>
                                <p class="text-xs text-gray-500">Only subscribers see the full post</p>
                            </div>
                        </label>
                        @if(!$isEdit)
                        <div id="premium-tier-group" style="display:none">
                            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 block mb-2">Minimum tier</label>
                            <select name="premium_tier" class="w-full text-sm rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                                @foreach($premiumTiers as $key => $label)
                                    <option value="{{ $key }}" {{ old('premium_tier') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <label class="flex items-start gap-3 cursor-pointer pt-3 border-t border-gray-100 dark:border-slate-700">
                            <input type="checkbox" name="allow_comments" value="1" {{ old('allow_comments', ($post ? $post->allow_comments : true)) ? 'checked' : '' }}
                                   class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                            <div class="flex-1">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Allow comments</span>
                                <p class="text-xs text-gray-500">Readers can comment</p>
                            </div>
                        </label>
                    </div>
                </details>

                <div class="lg:hidden flex gap-2">
                    <button type="submit" name="action" value="save_draft" class="flex-1 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg">Save draft</button>
                    <button type="submit" name="action" value="publish" class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg">{{ $isEdit ? 'Update' : 'Publish' }}</button>
                </div>
            </aside>
        </div>
    </div>
</form>

<div id="toast" class="fixed bottom-6 right-6 px-4 py-2.5 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-xl opacity-0 transition-opacity z-50 pointer-events-none"></div>

@push('scripts')
<script src="https://unpkg.com/@yaireo/tagify"></script>
<script src="https://cdn.jsdelivr.net/npm/marked@12/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
<script>
(function(){
    const POPULAR_TAGS = @json($popularTags);
    const HAS_EXISTING_COVER = {{ ($isEdit && $post && $post->featured_image) ? 'true' : 'false' }};
    const STORAGE_KEY = '{{ $isEdit && $post ? "ngb_post_edit_".$post->id : "ngb_post_draft_v1" }}';
    const IS_EDIT = {{ $isEdit ? 'true' : 'false' }};

    const $ = id => document.getElementById(id);
    const form = $('post-form');
    const titleEl=$('title'), excerptEl=$('excerpt'), contentEl=$('content');
    const titleCount=$('title-count'), excerptCount=$('excerpt-count');
    const lsWords=$('ls-words'), lsRead=$('ls-read');
    const statWords=$('stat-words'), statHeadings=$('stat-headings'), statLinks=$('stat-links');
    const slugPreview=$('slug-preview');
    const preview=$('preview'), editPanes=$('edit-panes');
    const saveState=$('save-state');
    const toastEl=$('toast');

    let toastTimer;
    function toast(msg, ms){toastEl.textContent=msg;toastEl.style.opacity='1';clearTimeout(toastTimer);toastTimer=setTimeout(()=>{toastEl.style.opacity='0';},ms||1800);}

    function slugify(s){return (s||'').toLowerCase().replace(/[^\w\s-]/g,'').trim().replace(/\s+/g,'-').replace(/-+/g,'-').slice(0,80);}
    function updateSlug(){const s=slugify(titleEl.value);slugPreview.textContent=s?`/posts/${s}`:'';}
    function wordsOf(s){return (s.trim().match(/\S+/g)||[]).length;}
    function countMatches(s,re){return (s.match(re)||[]).length;}
    function readMinutes(t){
        const codeLines=(t.match(/```[\s\S]*?```/g)||[]).reduce((a,b)=>a+Math.max(0,(b.match(/\n/g)||[]).length-1),0);
        const images=countMatches(t,/!\[[^\]]*\]\([^)]+\)/g);
        const clean=t.replace(/```[\s\S]*?```/g,' ').replace(/`[^`]+`/g,' code ');
        const sec=(wordsOf(clean)/240)*60+codeLines+images*8;
        return Math.max(1,Math.ceil(sec/60));
    }
    function refreshStats(){
        const t=contentEl.value;
        const w=wordsOf(t), r=readMinutes(t);
        const h=countMatches(t,/^#{1,6}\s+\S/gm), l=countMatches(t,/\[[^\]]+\]\([^)]+\)/g);
        lsWords.textContent=w.toLocaleString();lsRead.textContent=r;
        statWords.textContent=w.toLocaleString();statHeadings.textContent=h;statLinks.textContent=l;
        refreshHealth();
    }

    function getTagCount(){
        // Tagify may not be initialized yet; fall back to comma split of raw input
        if (window._tagifyInst) return window._tagifyInst.getCleanData().length;
        return $('tags').value.split(',').map(x=>x.trim()).filter(Boolean).length;
    }
    function hasCover(){
        if ($('featured_image').files.length>0) return true;
        return HAS_EXISTING_COVER;
    }
    function refreshHealth(){
        const t=titleEl.value.trim(), e=excerptEl.value.trim(), c=contentEl.value;
        const wc=wordsOf(c);
        const headings=countMatches(c,/^#{1,6}\s+\S/gm);
        const links=countMatches(c,/\[[^\]]+\]\([^)]+\)/g);
        const codeBlocks=countMatches(c,/```[\s\S]*?```/g);
        const cover=hasCover();
        const cat=$('category_id').value;
        const tags=getTagCount();

        const checks=[
            {ok:t.length>=40&&t.length<=70, warn:t.length>0, label:`Title ${t.length}/40-70 chars`},
            {ok:e.length>=120&&e.length<=160, warn:e.length>0, label:`Excerpt ${e.length}/120-160 chars`},
            {ok:wc>=1500, warn:wc>=500, label:`Content ${wc.toLocaleString()} words (1500+)`},
            {ok:headings>=3, warn:headings>=1, label:`${headings} headings (3+)`},
            {ok:links>=2, warn:links>=1, label:`${links} outbound links (2+)`},
            {ok:codeBlocks>=1, warn:false, label:`${codeBlocks} code blocks`},
            {ok:cover, warn:false, label:`Cover image ${cover?'set':'missing'}`},
            {ok:!!cat&&tags>=2, warn:!!cat, label:`Category${cat?'':' missing'}, ${tags} tags`},
        ];
        const passed=checks.filter(c=>c.ok).length;
        $('health-score').textContent=`${passed}/${checks.length}`;
        $('health-score').className='text-xs font-bold '+(passed===checks.length?'text-green-600':passed>=5?'text-yellow-600':'text-gray-500');
        $('health-list').innerHTML=checks.map(c=>{
            const cls=c.ok?'ok':(c.warn?'warn':'bad');
            const icon=c.ok?'<svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z"/></svg>':'<svg class="w-2 h-2" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>';
            return `<div class="health-row ${cls}"><span class="dot">${icon}</span><span>${c.label}</span></div>`;
        }).join('');
    }

    if (window.marked) marked.setOptions({breaks:true, gfm:true});
    function renderPreview(){
        if (preview.classList.contains('hidden')) return;
        const html=window.marked?marked.parse(contentEl.value||'_Nothing to preview yet._'):'<p>'+(contentEl.value||'').replace(/</g,'&lt;')+'</p>';
        preview.innerHTML=window.DOMPurify?DOMPurify.sanitize(html):html;
    }

    function setMode(m){
        document.querySelectorAll('#view-mode button').forEach(x=>{
            const active=x.dataset.mode===m;
            x.classList.toggle('bg-white',active);x.classList.toggle('dark:bg-slate-700',active);
            x.classList.toggle('text-gray-900',active);x.classList.toggle('dark:text-white',active);
            x.classList.toggle('shadow-sm',active);
            x.classList.toggle('text-gray-600',!active);x.classList.toggle('dark:text-gray-400',!active);
        });
        if (m==='write'){editPanes.classList.remove('lg:grid-cols-2');contentEl.classList.remove('hidden');preview.classList.add('hidden');}
        else if (m==='preview'){editPanes.classList.remove('lg:grid-cols-2');contentEl.classList.add('hidden');preview.classList.remove('hidden');renderPreview();}
        else if (m==='split'){editPanes.classList.add('lg:grid-cols-2');contentEl.classList.remove('hidden');preview.classList.remove('hidden');renderPreview();}
    }
    document.querySelectorAll('#view-mode button').forEach(b=>b.addEventListener('click',()=>setMode(b.dataset.mode)));

    function ensureWriteVisible(){if (contentEl.classList.contains('hidden')) setMode('split');}
    function wrap(before, after, placeholder){
        ensureWriteVisible();
        after=after===undefined?before:after;
        const s=contentEl.selectionStart, en=contentEl.selectionEnd;
        const sel=contentEl.value.substring(s,en)||(placeholder||'text');
        contentEl.value=contentEl.value.substring(0,s)+before+sel+after+contentEl.value.substring(en);
        contentEl.selectionStart=s+before.length;
        contentEl.selectionEnd=s+before.length+sel.length;
        contentEl.focus();afterEdit();
    }
    function prefixLines(pref){
        ensureWriteVisible();
        const s=contentEl.selectionStart, en=contentEl.selectionEnd;
        const before=contentEl.value.substring(0,s);
        const lineStart=before.lastIndexOf('\n')+1;
        const sel=contentEl.value.substring(lineStart,en)||'text';
        const lines=sel.split('\n').map((l,i)=>typeof pref==='function'?pref(l,i):pref+l).join('\n');
        contentEl.value=contentEl.value.substring(0,lineStart)+lines+contentEl.value.substring(en);
        contentEl.focus();afterEdit();
    }
    function insertBlock(text){
        ensureWriteVisible();
        const s=contentEl.selectionStart;
        const pre=contentEl.value.substring(0,s);
        const sep=pre.endsWith('\n\n')||pre===''?'':(pre.endsWith('\n')?'\n':'\n\n');
        contentEl.value=pre+sep+text+'\n\n'+contentEl.value.substring(s);
        contentEl.focus();afterEdit();
    }
    const actions={
        bold:()=>wrap('**','**','bold text'),
        italic:()=>wrap('*','*','italic text'),
        h2:()=>prefixLines('## '),
        quote:()=>prefixLines('> '),
        ul:()=>prefixLines('- '),
        ol:()=>prefixLines((l,i)=>(i+1)+'. '+l),
        code:()=>wrap('`','`','code'),
        codeblock:()=>insertBlock('```php\n// code here\n```'),
        link:()=>{
            ensureWriteVisible();
            const s=contentEl.selectionStart, en=contentEl.selectionEnd;
            const sel=contentEl.value.substring(s,en)||'link text';
            const url=prompt('URL:','https://');
            if(!url) return;
            const text='['+sel+']('+url+')';
            contentEl.value=contentEl.value.substring(0,s)+text+contentEl.value.substring(en);
            contentEl.selectionStart=s+1;
            contentEl.selectionEnd=s+1+sel.length;
            contentEl.focus();afterEdit();
        },
        hr:()=>insertBlock('---'),
        table:()=>insertBlock('| Column 1 | Column 2 |\n|----------|----------|\n| cell     | cell     |'),
    };
    document.querySelectorAll('[data-md]').forEach(b=>b.addEventListener('click',()=>actions[b.dataset.md]&&actions[b.dataset.md]()));

    document.addEventListener('keydown',e=>{
        const cmd=e.metaKey||e.ctrlKey;
        if(cmd&&e.key==='s'){e.preventDefault();form.querySelector('button[value=save_draft]').click();return;}
        if(document.activeElement===contentEl){
            if(cmd&&e.key.toLowerCase()==='b'){e.preventDefault();actions.bold();}
            else if(cmd&&e.key.toLowerCase()==='i'){e.preventDefault();actions.italic();}
            else if(cmd&&e.key.toLowerCase()==='k'){e.preventDefault();actions.link();}
            else if(e.key==='Tab'){
                e.preventDefault();
                const s=contentEl.selectionStart,en=contentEl.selectionEnd;
                contentEl.value=contentEl.value.substring(0,s)+'    '+contentEl.value.substring(en);
                contentEl.selectionStart=contentEl.selectionEnd=s+4;
                afterEdit();
            }
        }
        if(e.key==='F1'){e.preventDefault();$('focus-toggle').click();}
    });

    $('focus-toggle').addEventListener('click',()=>document.body.classList.toggle('focus-mode'));

    let saveTimer;
    function debouncedSave(){
        clearTimeout(saveTimer);
        saveState.textContent='Saving…';saveState.className='text-gray-400';
        saveTimer=setTimeout(()=>{
            try{
                localStorage.setItem(STORAGE_KEY,JSON.stringify({
                    title:titleEl.value, excerpt:excerptEl.value, content:contentEl.value,
                    tags:$('tags').value, category_id:$('category_id').value, at:Date.now()
                }));
                const d=new Date();
                saveState.textContent='Saved '+d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
                saveState.className='text-green-600 dark:text-green-400';
            }catch(err){}
        },800);
    }

    function maybeRestoreDraft(){
        if (IS_EDIT) return; // don't restore drafts in edit mode
        try{
            const raw=localStorage.getItem(STORAGE_KEY);if(!raw) return;
            const d=JSON.parse(raw);
            if(titleEl.value||excerptEl.value||contentEl.value) return;
            if(!d.title&&!d.content) return;
            const age=Math.round((Date.now()-d.at)/60000);
            if(confirm(`Restore unsaved draft from ${age} min ago?`)){
                titleEl.value=d.title||'';excerptEl.value=d.excerpt||'';contentEl.value=d.content||'';
                if(d.tags) $('tags').value=d.tags;
                if(d.category_id) $('category_id').value=d.category_id;
                afterEdit(true);
            } else { localStorage.removeItem(STORAGE_KEY); }
        }catch(err){}
    }

    function afterEdit(skipSave){
        titleCount.textContent=titleEl.value.length;
        excerptCount.textContent=excerptEl.value.length;
        refreshStats();updateSlug();
        if(!preview.classList.contains('hidden')) renderPreview();
        if(!skipSave) debouncedSave();
    }
    titleEl.addEventListener('input',afterEdit);
    excerptEl.addEventListener('input',afterEdit);
    contentEl.addEventListener('input',afterEdit);
    $('tags').addEventListener('input',()=>{debouncedSave();refreshHealth();});
    $('category_id').addEventListener('change',()=>{debouncedSave();refreshHealth();});

    $('featured_image').addEventListener('change',e=>{
        const f=e.target.files[0];if(!f) return;
        const url=URL.createObjectURL(f);
        $('cover-preview').src=url;
        $('cover-preview-wrap').classList.remove('hidden');
        refreshHealth();
    });

    $('is_premium').addEventListener('change',function(){
        const grp=$('premium-tier-group');
        if (grp) grp.style.display=this.checked?'block':'none';
    });
    if($('is_premium').checked){const grp=$('premium-tier-group');if(grp)grp.style.display='block';}

    const seriesMode=$('series_mode');
    function syncSeriesMode(){
        if(!seriesMode) return;
        const v=seriesMode.value;
        const newGrp=$('new-series-group'), exGrp=$('existing-series-group');
        if (newGrp) newGrp.style.display=v==='new'?'block':'none';
        if (exGrp) exGrp.style.display=v==='existing'?'block':'none';
    }
    seriesMode?.addEventListener('change',syncSeriesMode);
    syncSeriesMode();

    $('existing_post_id')?.addEventListener('change',function(){
        const opt=this.options[this.selectedIndex];
        const t=opt.getAttribute('data-series-title')||'';
        const p=parseInt(opt.getAttribute('data-series-part')||'0');
        const tot=parseInt(opt.getAttribute('data-series-total')||'0');
        const d=opt.getAttribute('data-series-description')||'';
        const info=$('series-info-display');
        // Clean previous hidden inputs
        form.querySelectorAll('input[type=hidden][data-existing-series]').forEach(n=>n.remove());
        if(this.value&&t){
            const np=p>0?p+1:1, tp=tot>0?tot:Math.max(np,tot);
            if (info) {
                info.classList.remove('hidden');
                info.innerHTML=`<div class="font-semibold mb-1">Adding to: ${t}</div>This post will be saved as <strong>Part ${np} of ${tp}</strong>.`;
            }
            [['series_title',t],['series_part',np],['series_total_parts',tp],['series_description',d]].forEach(([name,val])=>{
                const h=document.createElement('input');
                h.type='hidden';h.name=name;h.value=val;h.dataset.existingSeries='1';
                form.appendChild(h);
            });
        } else if (info) {
            info.classList.add('hidden');
            info.innerHTML='';
        }
    });

    // Tagify - ensure original input gets comma-separated value
    if (window.Tagify) {
        window._tagifyInst = new Tagify($('tags'), {
            maxTags: 5,
            delimiters: ',',
            whitelist: POPULAR_TAGS,
            dropdown: { maxItems: 15, enabled: 1, closeOnSelect: false },
            enforceWhitelist: false,
            originalInputValueFormat: vals => vals.map(v => v.value).join(',')
        });
        window._tagifyInst.on('change', () => refreshHealth());
    }

    // Form submit: sync Tagify + clean series fields
    form.addEventListener('submit', function() {
        // Tagify: ensure value is comma-separated
        if (window._tagifyInst) {
            const v = window._tagifyInst.getCleanData().map(t => t.value).join(',');
            $('tags').value = v;
        }
        // Series mode cleanup
        const mode = seriesMode ? seriesMode.value : '';
        if (mode === '') {
            // No series → clear all series fields
            ['series_title','series_part','series_total_parts','series_description'].forEach(n=>{
                const el=document.querySelector(`[name="${n}"]:not([data-existing-series])`);
                if (el) el.value='';
            });
            form.querySelectorAll('input[data-existing-series]').forEach(n=>n.remove());
        } else if (mode === 'new') {
            // Keep new-series inputs; remove any leftover existing-series hidden inputs
            form.querySelectorAll('input[data-existing-series]').forEach(n=>n.remove());
        } else if (mode === 'existing') {
            // Existing series hidden inputs override visible ones; disable the visible ones so they don't conflict
            ['series_title','series_part','series_total_parts','series_description'].forEach(n=>{
                const el=document.querySelector(`[name="${n}"]:not([data-existing-series])`);
                if (el) el.disabled = true;
            });
        }
        // Clear localStorage on submit
        try { localStorage.removeItem(STORAGE_KEY); } catch(e) {}
    });

    // Init
    titleCount.textContent=titleEl.value.length;
    excerptCount.textContent=excerptEl.value.length;
    updateSlug();refreshStats();maybeRestoreDraft();
})();
</script>
@endpush
