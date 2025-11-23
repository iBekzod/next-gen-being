<?php

namespace App\Http\Controllers;

use App\Services\CreatorAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private CreatorAnalyticsService $analyticsService) {}

    /**
     * Get creator dashboard analytics
     */
    public function dashboard(Request $request)
    {
        $days = $request->get('days', 30);

        $dashboard = $this->analyticsService->getDashboardData(auth()->user(), $days);

        return response()->json($dashboard);
    }

    /**
     * Get performance report
     */
    public function performance(Request $request)
    {
        $days = $request->get('days', 30);

        $report = $this->analyticsService->getPerformanceReport(auth()->user(), $days);

        return response()->json($report);
    }

    /**
     * Get audience insights
     */
    public function audience()
    {
        $insights = $this->analyticsService->getAudienceInsights(auth()->user());

        return response()->json($insights);
    }

    /**
     * Get revenue breakdown
     */
    public function revenue(Request $request)
    {
        $days = $request->get('days', 30);
        $analytics = $this->analyticsService->getCreatorAnalytics(auth()->user(), $days);

        return response()->json($analytics);
    }

    /**
     * Get analytics for specific date
     */
    public function dailyAnalytics(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $analytic = \App\Models\CreatorAnalytic::where('user_id', auth()->id())
            ->where('date', $date)
            ->first();

        return response()->json($analytic ?? ['message' => 'No data for this date']);
    }
}
