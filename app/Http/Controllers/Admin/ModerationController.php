<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Services\ContentModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        // Add admin middleware once you have it set up
        // $this->middleware('role:admin');
    }

    /**
     * Show moderation queue
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'pending');

        $query = Post::with(['author', 'category', 'moderator'])
            ->orderByDesc('created_at');

        match($filter) {
            'pending' => $query->pendingModeration(),
            'approved' => $query->moderatedApproved(),
            'rejected' => $query->moderatedRejected(),
            default => $query->pendingModeration(),
        };

        $posts = $query->paginate(20);

        $stats = [
            'pending' => Post::pendingModeration()->count(),
            'approved_today' => Post::moderatedApproved()
                ->whereDate('moderated_at', today())
                ->count(),
            'rejected_today' => Post::moderatedRejected()
                ->whereDate('moderated_at', today())
                ->count(),
        ];

        return view('admin.moderation.index', compact('posts', 'stats', 'filter'));
    }

    /**
     * Show single post for detailed review
     */
    public function show(Post $post)
    {
        $post->load(['author', 'category', 'tags', 'moderator']);

        return view('admin.moderation.show', compact('post'));
    }

    /**
     * Approve a post
     */
    public function approve(Request $request, Post $post)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $post->approve(auth()->user(), $request->notes);

        // Send notification to author
        if ($post->author && $post->author->email) {
            try {
                Mail::to($post->author->email)->send(
                    new \App\Mail\PostApproved($post)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send approval email', ['error' => $e->getMessage()]);
            }
        }

        return redirect()
            ->route('admin.moderation.index')
            ->with('success', 'Post approved and published!');
    }

    /**
     * Reject a post
     */
    public function reject(Request $request, Post $post)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $post->reject(auth()->user(), $request->reason);

        // Send notification to author
        if ($post->author && $post->author->email) {
            try {
                Mail::to($post->author->email)->send(
                    new \App\Mail\PostRejected($post, $request->reason)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send rejection email', ['error' => $e->getMessage()]);
            }
        }

        return redirect()
            ->route('admin.moderation.index')
            ->with('success', 'Post rejected. Author has been notified.');
    }

    /**
     * Re-run AI moderation check
     */
    public function recheck(Post $post)
    {
        $moderationService = new ContentModerationService();
        $result = $moderationService->moderateContent(
            $post->title,
            $post->content,
            $post->excerpt
        );

        $post->update([
            'ai_moderation_check' => $result
        ]);

        return redirect()
            ->back()
            ->with('success', 'AI moderation check completed');
    }

    /**
     * Bulk approve posts
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id',
        ]);

        $posts = Post::whereIn('id', $request->post_ids)
            ->pendingModeration()
            ->get();

        foreach ($posts as $post) {
            $post->approve(auth()->user(), 'Bulk approved');
        }

        return redirect()
            ->back()
            ->with('success', count($posts) . ' posts approved');
    }

    /**
     * Bulk reject posts
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id',
            'reason' => 'required|string|max:1000',
        ]);

        $posts = Post::whereIn('id', $request->post_ids)
            ->pendingModeration()
            ->get();

        foreach ($posts as $post) {
            $post->reject(auth()->user(), $request->reason);
        }

        return redirect()
            ->back()
            ->with('success', count($posts) . ' posts rejected');
    }
}
