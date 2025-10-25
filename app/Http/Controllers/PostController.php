<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return view('posts.index');
    }

    public function show(Post $post)
    {
        $post->loadMissing(['author', 'category', 'tags']);

        // Determine if user should see paywall
        $showPaywall = $post->shouldShowPaywall(Auth::user());

        // Get content based on user access
        $content = $showPaywall ? $post->getPreviewContent() : $post->content;

        // Track view (only for public or subscribed users)
        if (!$showPaywall) {
            $post->increment('views_count');
        }

        return view('posts.show', [
            'post' => $post,
            'content' => $content,
            'showPaywall' => $showPaywall,
            'paywallMessage' => $post->getPaywallMessage(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Post::class);

        $suggestion = null;
        if ($request->has('suggestion')) {
            $suggestion = \App\Models\AiContentSuggestion::find($request->get('suggestion'));
        }

        return view('posts.create', [
            'categories' => Category::active()->ordered()->get(),
            'tags' => Tag::active()->popular()->get(),
            'suggestion' => $suggestion,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date|after:now',
            'is_premium' => 'boolean',
            'allow_comments' => 'boolean',
        ]);

        $validated['author_id'] = Auth::id();
        $validated['slug'] = null; // Will be auto-generated

        if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }
        if ($request->input('action') === 'save_draft') {
            $validated['status'] = 'draft';
        } elseif ($request->input('action') === 'publish') {
            $validated['status'] = 'published';
            if (!isset($validated['published_at'])) {
                $validated['published_at'] = now();
            }
        }
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = Storage::url($path);
        }
        $post = Post::create($validated);

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post created successfully!');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', [
            'post' => $post,
            'categories' => Category::active()->ordered()->get(),
            'tags' => Tag::active()->popular()->get(),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'is_premium' => 'boolean',
            'allow_comments' => 'boolean',
        ]);
        if ($request->input('action') === 'save_draft') {
            $validated['status'] = 'draft';
        } elseif ($request->input('action') === 'publish') {
            $validated['status'] = 'published';
            if (!isset($validated['published_at'])) {
                $validated['published_at'] = now();
            }
        }
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = Storage::url($path);
        }
        $post->update($validated);

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('posts.show', $post->slug)
            ->with('success', 'Post updated successfully!');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }

    public function tutorials()
    {
        // Get all unique series
        $series = Post::published()
            ->whereNotNull('series_slug')
            ->select('series_slug', 'series_title', 'series_description', 'series_total_parts')
            ->selectRaw('MIN(series_part) as first_part')
            ->selectRaw('MAX(published_at) as last_updated')
            ->selectRaw('COUNT(*) as published_parts')
            ->with(['category'])
            ->groupBy('series_slug', 'series_title', 'series_description', 'series_total_parts')
            ->orderByDesc('last_updated')
            ->get();

        // For each series, get the first post for the image and category
        $series = $series->map(function ($item) {
            $firstPost = Post::published()
                ->where('series_slug', $item->series_slug)
                ->where('series_part', $item->first_part)
                ->with(['category', 'author'])
                ->first();

            return [
                'slug' => $item->series_slug,
                'title' => $item->series_title,
                'description' => $item->series_description,
                'total_parts' => $item->series_total_parts,
                'published_parts' => $item->published_parts,
                'last_updated' => \Carbon\Carbon::parse($item->last_updated),
                'featured_image' => $firstPost->featured_image ?? null,
                'category' => $firstPost->category ?? null,
                'author' => $firstPost->author ?? null,
                'is_complete' => $item->published_parts == $item->series_total_parts,
            ];
        });

        return view('tutorials.index', compact('series'));
    }

    public function series($seriesSlug)
    {
        $posts = Post::published()
            ->inSeries($seriesSlug)
            ->with(['author', 'category', 'tags'])
            ->get();

        if ($posts->isEmpty()) {
            abort(404, 'Series not found');
        }

        $seriesInfo = [
            'title' => $posts->first()->series_title,
            'description' => $posts->first()->series_description,
            'total_parts' => $posts->first()->series_total_parts,
            'slug' => $seriesSlug,
        ];

        return view('tutorials.series', compact('posts', 'seriesInfo'));
    }
}
