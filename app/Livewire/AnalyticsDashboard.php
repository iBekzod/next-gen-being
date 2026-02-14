<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\CreatorAnalyticsService;

class AnalyticsDashboard extends Component
{
    public CreatorAnalyticsService $analyticsService;
    public array $stats = [];
    public array $trends = [];
    public array $topPosts = [];
    public array $revenueData = [];
    public string $timeRange = '30days'; // 7days, 30days, 90days, all
    public bool $isLoading = true;

    public function mount()
    {
        $this->analyticsService = app(CreatorAnalyticsService::class);
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        $this->isLoading = true;

        try {
            $user = auth()->user();

            // Get dashboard summary
            $this->stats = $this->analyticsService->getDashboardSummary($user);

            // Get trends
            $this->trends = $this->analyticsService->getPerformanceTrends($user, $this->timeRange);

            // Get top posts
            $this->topPosts = $this->analyticsService->getTopPosts($user, limit: 5);

            // Get revenue breakdown
            $this->revenueData = $this->analyticsService->getRevenueBreakdown($user, $this->timeRange);
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedTimeRange()
    {
        $this->loadAnalytics();
    }

    public function exportAnalytics()
    {
        // Trigger export
        $this->dispatch('analyticsExporting');
    }

    public function render()
    {
        return view('livewire.analytics-dashboard');
    }
}
