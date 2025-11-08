<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WebhookController extends Controller
{
    /**
     * Display the create webhook form.
     */
    public function create(): View
    {
        $availableEvents = [
            'post.created' => 'Post Created',
            'post.published' => 'Post Published',
            'post.updated' => 'Post Updated',
            'post.deleted' => 'Post Deleted',
            'comment.created' => 'Comment Created',
            'comment.deleted' => 'Comment Deleted',
            'like.created' => 'Like Created',
            'like.deleted' => 'Like Deleted',
            'bookmark.created' => 'Bookmark Created',
            'bookmark.deleted' => 'Bookmark Deleted',
            'earnings.created' => 'Earnings Created',
            'payout.requested' => 'Payout Requested',
            'payout.completed' => 'Payout Completed',
        ];

        return view('webhooks.create', compact('availableEvents'));
    }

    /**
     * Store a newly created webhook.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'verify_ssl' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'active';
        $validated['max_retries'] = 5;
        $validated['events'] = $request->input('events', []);
        $validated['verify_ssl'] = $request->boolean('verify_ssl', true);

        Auth::user()->webhooks()->create($validated);

        return redirect()->route('dashboard.webhooks')
            ->with('success', 'Webhook created successfully!');
    }

    /**
     * Display the edit webhook form.
     */
    public function edit(Webhook $webhook): View
    {
        $this->authorize('update', $webhook);

        $availableEvents = [
            'post.created' => 'Post Created',
            'post.published' => 'Post Published',
            'post.updated' => 'Post Updated',
            'post.deleted' => 'Post Deleted',
            'comment.created' => 'Comment Created',
            'comment.deleted' => 'Comment Deleted',
            'like.created' => 'Like Created',
            'like.deleted' => 'Like Deleted',
            'bookmark.created' => 'Bookmark Created',
            'bookmark.deleted' => 'Bookmark Deleted',
            'earnings.created' => 'Earnings Created',
            'payout.requested' => 'Payout Requested',
            'payout.completed' => 'Payout Completed',
        ];

        return view('webhooks.edit', compact('webhook', 'availableEvents'));
    }

    /**
     * Update the specified webhook.
     */
    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $this->authorize('update', $webhook);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'verify_ssl' => 'boolean',
        ]);

        $validated['events'] = $request->input('events', []);
        $validated['verify_ssl'] = $request->boolean('verify_ssl', true);

        $webhook->update($validated);

        return redirect()->route('dashboard.webhooks')
            ->with('success', 'Webhook updated successfully!');
    }

    /**
     * Display webhook details.
     */
    public function show(Webhook $webhook): View
    {
        $this->authorize('view', $webhook);

        $statistics = $webhook->getStatistics();
        $recentLogs = $webhook->logs()->orderBy('created_at', 'desc')->limit(50)->get();

        return view('webhooks.show', compact('webhook', 'statistics', 'recentLogs'));
    }

    /**
     * Delete the specified webhook.
     */
    public function destroy(Webhook $webhook): RedirectResponse
    {
        $this->authorize('delete', $webhook);

        $webhook->delete();

        return redirect()->route('dashboard.webhooks')
            ->with('success', 'Webhook deleted successfully!');
    }
}
