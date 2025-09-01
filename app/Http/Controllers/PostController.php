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
        // Check if post can be viewed by current user
        if (!$post->canBeViewedBy(Auth::user())) {
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->with('message', 'Please log in to access this content.');
            }

            if ($post->is_premium) {
                return redirect()->route('subscription.plans')
                    ->with('message', 'This is premium content. Please subscribe to access.');
            }

            abort(403);
        }

        return view('posts.show', compact('post'));
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
}
