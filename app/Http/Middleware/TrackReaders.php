<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Post;
use App\Services\ReaderTrackingService;

class TrackReaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only track post views
        if ($request->route() && $request->route()->getName() === 'posts.show') {
            $post = $request->route('post');

            if ($post instanceof Post) {
                $this->trackReader($post, $request);
            }
        }

        return $next($request);
    }

    /**
     * Track reader for a post
     */
    private function trackReader(Post $post, Request $request): void
    {
        try {
            $readerTrackingService = app(ReaderTrackingService::class);

            // Get or create session ID for anonymous readers
            $sessionId = $request->session()?->get('reader_session_id');
            if (!$sessionId) {
                $sessionId = Str::uuid()->toString();
                $request->session()?->put('reader_session_id', $sessionId);
            }

            // Get client IP address
            $ipAddress = $request->ip();

            // Track the reader
            $readerTrackingService->trackReader(
                $post,
                auth()->user(),
                $sessionId,
                $ipAddress
            );

        } catch (\Exception $e) {
            // Silently fail - don't break the app if tracking fails
            \Log::warning("Reader tracking error: " . $e->getMessage());
        }
    }
}
