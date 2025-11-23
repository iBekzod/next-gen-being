<?php

namespace App\Http\Controllers;

use App\Services\ReaderPreferenceService;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function __construct(private ReaderPreferenceService $preferenceService) {}

    /**
     * Get user's preferences
     */
    public function show()
    {
        $summary = $this->preferenceService->getPreferenceSummary(auth()->user());

        return response()->json($summary);
    }

    /**
     * Update preferences
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'preferred_categories' => 'nullable|array',
            'preferred_authors' => 'nullable|array',
            'disliked_categories' => 'nullable|array',
            'disliked_authors' => 'nullable|array',
        ]);

        $preferences = $this->preferenceService->getOrCreatePreferences(auth()->user());

        if (isset($validated['preferred_categories'])) {
            $preferences->update(['preferred_categories' => $validated['preferred_categories']]);
        }

        if (isset($validated['disliked_categories'])) {
            $preferences->update(['disliked_categories' => $validated['disliked_categories']]);
        }

        return response()->json(['success' => true, 'preferences' => $preferences]);
    }

    /**
     * Reset all preferences
     */
    public function reset()
    {
        $this->preferenceService->resetPreferences(auth()->user());

        return response()->json(['success' => true, 'message' => 'Preferences reset']);
    }

    /**
     * Add disliked category
     */
    public function dislike(Request $request, $categoryId)
    {
        $this->preferenceService->dislikeContent(auth()->user(), $categoryId);

        return response()->json(['success' => true]);
    }

    /**
     * Get recommended posts
     */
    public function recommendations(Request $request)
    {
        $limit = $request->get('limit', 20);
        $recommended = $this->preferenceService->getRecommendedPosts(auth()->user(), $limit);

        return response()->json($recommended);
    }
}
