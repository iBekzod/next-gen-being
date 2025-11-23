<?php

namespace App\Http\Controllers;

use App\Models\ScheduledPost;
use App\Services\ContentCalendarService;
use Illuminate\Http\Request;

class ContentCalendarController extends Controller
{
    public function __construct(private ContentCalendarService $calendarService) {}

    public function schedule(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image_url' => 'nullable|url',
            'scheduled_for' => 'required|date_format:Y-m-d H:i:s|after:now',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
        ]);

        $result = $this->calendarService->schedulePost(auth()->user(), $validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image_url' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
        ]);

        $result = $this->calendarService->saveDraft(auth()->user(), $validated);

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    public function calendar($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $calendar = $this->calendarService->getAuthorCalendar(auth()->user(), $month, $year);

        return response()->json($calendar);
    }

    public function upcoming()
    {
        $upcoming = $this->calendarService->getUpcomingPosts(auth()->user());

        return response()->json($upcoming);
    }

    public function drafts()
    {
        $drafts = $this->calendarService->getDrafts(auth()->user());

        return response()->json($drafts);
    }

    public function history(Request $request)
    {
        $days = $request->get('days', 30);
        $history = $this->calendarService->getPublishingHistory(auth()->user(), $days);

        return response()->json($history);
    }

    public function update(Request $request, ScheduledPost $scheduled)
    {
        if ($scheduled->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image_url' => 'nullable|url',
            'scheduled_for' => 'nullable|date_format:Y-m-d H:i:s|after:now',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
        ]);

        $result = $this->calendarService->updateScheduledPost($scheduled, $validated);

        return response()->json($result);
    }

    public function delete(ScheduledPost $scheduled)
    {
        if ($scheduled->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->calendarService->deleteScheduledPost($scheduled);

        return response()->json($result);
    }

    public function stats()
    {
        $stats = $this->calendarService->getCalendarStats(auth()->user());

        return response()->json($stats);
    }

    public function suggestions()
    {
        $suggestions = $this->calendarService->getPublishingSuggestions(auth()->user());

        return response()->json($suggestions);
    }
}
