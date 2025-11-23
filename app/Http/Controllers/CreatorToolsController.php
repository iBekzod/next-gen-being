<?php

namespace App\Http\Controllers;

use App\Models\ContentIdea;
use App\Services\CreatorToolsService;
use Illuminate\Http\Request;

class CreatorToolsController extends Controller
{
    public function __construct(private CreatorToolsService $creatorToolsService) {}

    /**
     * Generate content ideas
     */
    public function generateIdeas(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:100',
            'content_type' => 'required|string',
            'count' => 'integer|min:1|max:20',
            'use_ai' => 'boolean',
        ]);

        $result = $this->creatorToolsService->generateContentIdeas(auth()->user(), $validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * List content ideas
     */
    public function listIdeas(Request $request)
    {
        $status = $request->get('status', 'active');
        $limit = $request->get('limit', 20);

        $ideas = $this->creatorToolsService->getCreatorIdeas(auth()->user(), $status, $limit);

        return response()->json($ideas);
    }

    /**
     * Generate outline for an idea
     */
    public function generateOutline(ContentIdea $idea)
    {
        if ($idea->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->creatorToolsService->generateOutline($idea);

        return response()->json($result);
    }

    /**
     * Analyze SEO
     */
    public function analyzeSEO(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'keywords' => 'required|array',
            'content' => 'nullable|string',
        ]);

        $seo = $this->creatorToolsService->analyzeSEO(
            $validated['title'],
            $validated['keywords'],
            $validated['content'] ?? ''
        );

        return response()->json($seo);
    }

    /**
     * Get audience insights
     */
    public function audience()
    {
        $insights = $this->creatorToolsService->getAudienceInsights(auth()->user());

        return response()->json($insights);
    }

    /**
     * Get publishing suggestions
     */
    public function suggestions()
    {
        $suggestions = $this->creatorToolsService->getPublishingSuggestions(auth()->user());

        return response()->json($suggestions);
    }

    /**
     * Get performance report
     */
    public function report(Request $request)
    {
        $days = $request->get('days', 30);
        $report = $this->creatorToolsService->getPerformanceReport(auth()->user(), $days);

        return response()->json($report);
    }

    /**
     * Update idea
     */
    public function updateIdea(Request $request, ContentIdea $idea)
    {
        if ($idea->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'priority' => 'string|in:low,medium,high',
            'status' => 'string|in:active,in_progress,completed,archived',
            'notes' => 'nullable|string',
        ]);

        $result = $this->creatorToolsService->updateIdea($idea, $validated);

        return response()->json($result);
    }

    /**
     * Delete idea
     */
    public function deleteIdea(ContentIdea $idea)
    {
        if ($idea->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->creatorToolsService->deleteIdea($idea);

        return response()->json($result);
    }
}
