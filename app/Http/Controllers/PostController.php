<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Services\ContentModerationService;
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

        $user = Auth::user();
        $userAiQuota = [
            'tier' => $user->ai_tier,
            'posts_generated' => $user->ai_posts_generated,
            'posts_limit' => $user->monthly_ai_posts_limit,
            'images_generated' => $user->ai_images_generated,
            'images_limit' => $user->monthly_ai_images_limit,
            'can_generate' => $user->canGenerateAIContent(),
            'can_generate_image' => $user->canGenerateAIImage(),
        ];

        // Get user's all posts for tutorial series selection
        $allPosts = Post::where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('posts.create', [
            'categories' => Category::active()->ordered()->get(),
            'tags' => Tag::active()->popular()->get(),
            'allPosts' => $allPosts,
            'suggestion' => $suggestion,
            'userAiQuota' => $userAiQuota,
            'premiumTiers' => [
                'free' => 'Free',
                'basic' => 'Basic ($4.99/mo)',
                'pro' => 'Pro ($9.99/mo)',
                'team' => 'Team ($29.99/mo)',
            ],
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
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date|after:now',
            'is_premium' => 'boolean',
            'premium_tier' => 'nullable|in:free,basic,pro,team',
            'allow_comments' => 'boolean',
            'series_title' => 'nullable|string|max:255',
            'series_part' => 'nullable|integer|min:1',
            'series_total_parts' => 'nullable|integer|min:1',
            'series_description' => 'nullable|string|max:1000',
            'image_attribution' => 'nullable|string',
            'post_type' => 'nullable|in:standard,tutorial,video,guide,review',
        ]);

        // If series_title is provided, require series_part and series_total_parts
        if (!empty($validated['series_title'])) {
            if (empty($validated['series_part']) || empty($validated['series_total_parts'])) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'series_part' => 'Series part number is required when creating a tutorial series',
                        'series_total_parts' => 'Total series parts is required when creating a tutorial series'
                    ]);
            }
        }

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

        // Handle tags: convert comma-separated string to tag names
        $tagNames = [];
        if (!empty($validated['tags'])) {
            $tagNames = array_map('trim', explode(',', $validated['tags']));
        }
        unset($validated['tags']); // Remove from validated data

        // Run AI moderation check before creating post
        $moderationService = new ContentModerationService();
        $moderationResult = $moderationService->moderateContent(
            $validated['title'],
            $validated['content'],
            $validated['excerpt']
        );

        // Store moderation result
        $validated['ai_moderation_check'] = $moderationResult;

        // Set moderation status based on AI result and post status
        if ($validated['status'] === 'draft') {
            // Drafts don't need moderation yet
            $validated['moderation_status'] = 'pending';
        } elseif ($moderationResult['passed'] && $moderationResult['score'] >= 80) {
            // High-quality content can auto-approve
            $validated['moderation_status'] = 'approved';
            $validated['moderated_by'] = null; // Auto-approved by AI
            $validated['moderated_at'] = now();
            $validated['moderation_notes'] = 'Auto-approved by AI (score: ' . $moderationResult['score'] . ')';
        } else {
            // Everything else needs manual review
            $validated['moderation_status'] = 'pending';
            $validated['status'] = 'draft'; // Keep as draft until approved
        }

        // Generate series slug if series title provided
        if (!empty($validated['series_title'])) {
            $validated['series_slug'] = \Illuminate\Support\Str::slug($validated['series_title']);
        }

        $post = Post::create($validated);

        // Attach tags by finding or creating them
        if (!empty($tagNames)) {
            $tagIds = [];
            foreach ($tagNames as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => \Illuminate\Support\Str::slug($tagName), 'is_active' => true]
                    );
                    $tagIds[] = $tag->id;
                }
            }
            $post->tags()->sync($tagIds);
        }

        // Different messages based on moderation status
        if ($post->moderation_status === 'approved') {
            $message = 'Post created and published successfully!';
        } else {
            $message = 'Post created and submitted for moderation. You will be notified once it is reviewed.';
        }

        return redirect()->route('posts.show', $post->slug)
            ->with('success', $message);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $user = Auth::user();
        $allPosts = Post::where('author_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('posts.edit', [
            'post' => $post,
            'categories' => Category::active()->ordered()->get(),
            'tags' => Tag::active()->popular()->get(),
            'allPosts' => $allPosts,
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
            'tags' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|date',
            'is_premium' => 'boolean',
            'allow_comments' => 'boolean',
            'series_title' => 'nullable|string|max:255',
            'series_part' => 'nullable|integer|min:1',
            'series_total_parts' => 'nullable|integer|min:1',
            'series_description' => 'nullable|string|max:1000',
        ]);

        // If series_title is provided, require series_part and series_total_parts
        if (!empty($validated['series_title'])) {
            if (empty($validated['series_part']) || empty($validated['series_total_parts'])) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'series_part' => 'Series part number is required when creating a tutorial series',
                        'series_total_parts' => 'Total series parts is required when creating a tutorial series'
                    ]);
            }
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

        // Generate series slug if series title provided
        if (!empty($validated['series_title'])) {
            $validated['series_slug'] = \Illuminate\Support\Str::slug($validated['series_title']);
        }

        // Handle tags: convert comma-separated string to tag names
        $tagIds = [];
        if (!empty($validated['tags'])) {
            $tagNames = array_map('trim', explode(',', $validated['tags']));
            foreach ($tagNames as $tagName) {
                if (!empty($tagName)) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => \Illuminate\Support\Str::slug($tagName), 'is_active' => true]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }
        unset($validated['tags']); // Remove from validated data

        $post->update($validated);

        // Sync tags
        $post->tags()->sync($tagIds);

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
        try {
            // Get all unique series - group by series_slug only to avoid duplicates from NULL values
            $series = Post::published()
                ->whereNotNull('series_slug')
                ->select('series_slug', 'series_title', 'series_description')
                ->selectRaw('MAX(series_total_parts) as series_total_parts')
                ->selectRaw('MIN(series_part) as first_part')
                ->selectRaw('MAX(published_at) as last_updated')
                ->selectRaw('COUNT(*) as published_parts')
                ->with(['category'])
                ->groupBy('series_slug', 'series_title', 'series_description')
                ->orderByDesc('last_updated')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Failed to load tutorial series: ' . $e->getMessage());
            $series = collect();
        }

        // Get current user progress if authenticated
        $user = auth()->user();
        $tutorialProgressService = null;
        if ($user) {
            try {
                $tutorialProgressService = app(\App\Services\Tutorial\TutorialProgressService::class);
            } catch (\Exception $e) {
                \Log::error('Failed to load TutorialProgressService: ' . $e->getMessage());
                $tutorialProgressService = null;
            }
        }

        // For each series, get the first post for the image and category
        $series = $series->map(function ($item) use ($user, $tutorialProgressService) {
            try {
                $firstPost = Post::published()
                    ->where('series_slug', $item->series_slug)
                    ->where('series_part', $item->first_part)
                    ->with(['category', 'author'])
                    ->first();

                // Check if user has completed all parts in this series
                $is_complete = false;
                if ($user && $tutorialProgressService) {
                    try {
                        $seriesProgress = $tutorialProgressService->getSeriesProgress($user, $item->series_slug);
                        $is_complete = $seriesProgress['is_complete'];
                    } catch (\Exception $e) {
                        \Log::error('Failed to get series progress: ' . $e->getMessage());
                    }
                }

                return [
                    'slug' => $item->series_slug,
                    'title' => $item->series_title,
                    'description' => $item->series_description,
                    'total_parts' => $item->series_total_parts,
                    'published_parts' => $item->published_parts,
                    'last_updated' => \Carbon\Carbon::parse($item->last_updated),
                    'featured_image' => $firstPost?->featured_image ?? null,
                    'category' => $firstPost?->category ?? null,
                    'author' => $firstPost?->author ?? null,
                    'is_complete' => $is_complete,
                ];
            } catch (\Exception $e) {
                \Log::error('Failed to process tutorial series item: ' . $e->getMessage());
                return null;
            }
        })->filter(); // Remove null entries from failed mappings

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
