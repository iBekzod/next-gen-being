<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Models\User;
use App\Services\AnalyticsService;

class AnalyticsDashboard extends Component
{
    public User $author;
    public string $selectedPeriod = 'month';
    public string $selectedMetric = 'views';
    public int $daysToShow = 30;

    public array $dashboardStats = [];
    public array $trendingPosts = [];
    public array $growthComparison = [];
    public array $audienceInsights = [];
    public array $chartData = [];

    protected $analyticsService;

    public function mount(User $author = null)
    {
        $this->analyticsService = app(AnalyticsService::class);
        $this->author = $author ?? auth()->user();

        if (!$this->author) {
            abort(401, 'Unauthorized');
        }

        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        // Load all dashboard data
        $this->dashboardStats = $this->analyticsService->getAuthorDashboardStats($this->author);
        $this->trendingPosts = $this->analyticsService->getAuthorTrendingPosts($this->author, 5)->toArray();
        $this->growthComparison = $this->analyticsService->getGrowthComparison($this->author, $this->selectedPeriod);
        $this->audienceInsights = $this->analyticsService->getAudienceInsights($this->author);

        $this->prepareChartData();
    }

    public function setPeriod(string $period)
    {
        $this->selectedPeriod = $period;
        $this->daysToShow = $period === 'week' ? 7 : 30;
        $this->loadAnalytics();
    }

    public function setMetric(string $metric)
    {
        $this->selectedMetric = $metric;
        $this->prepareChartData();
    }

    private function prepareChartData()
    {
        // Get detailed post analytics for charting
        $posts = Post::where('author_id', $this->author->id)->published()->limit(5)->get();

        $labels = [];
        $datasets = [];

        // Collect data for each post
        foreach ($posts as $post) {
            $analytics = $this->analyticsService->getPostAnalytics($post, $this->daysToShow);
            $dailyData = $analytics['daily_data'];

            if (empty($labels) && !empty($dailyData)) {
                $labels = array_column($dailyData, 'date');
            }

            $metricKey = $this->selectedMetric;
            $data = array_column($dailyData, $metricKey);

            $datasets[] = [
                'label' => $post->title,
                'data' => $data,
                'borderColor' => $this->getRandomColor(),
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'tension' => 0.4,
                'fill' => false,
            ];
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function getRandomColor(): string
    {
        $colors = [
            'rgba(59, 130, 246, 1)',      // Blue
            'rgba(34, 197, 94, 1)',       // Green
            'rgba(239, 68, 68, 1)',       // Red
            'rgba(249, 115, 22, 1)',      // Orange
            'rgba(139, 92, 246, 1)',      // Purple
            'rgba(14, 165, 233, 1)',      // Sky
        ];

        return $colors[array_rand($colors)];
    }

    public function render()
    {
        return view('livewire.analytics-dashboard', [
            'dashboardStats' => $this->dashboardStats,
            'trendingPosts' => $this->trendingPosts,
            'growthComparison' => $this->growthComparison,
            'audienceInsights' => $this->audienceInsights,
            'chartData' => $this->chartData,
        ]);
    }
}
