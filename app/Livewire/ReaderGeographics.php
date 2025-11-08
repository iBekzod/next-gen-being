<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Services\ReaderTrackingService;

class ReaderGeographics extends Component
{
    public Post $post;
    public array $topCountries = [];
    public array $readerBreakdown = [];
    public array $analytics = [];
    public string $selectedMetric = 'readers';

    protected $rules = [
        'selectedMetric' => 'in:readers,authenticated,anonymous',
    ];

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadGeographicData();
    }

    public function loadGeographicData(): void
    {
        $readerTrackingService = app(ReaderTrackingService::class);

        // Get top countries with reader counts
        $this->topCountries = $readerTrackingService->getTopCountries($this->post->id, 15);

        // Get breakdown
        $this->readerBreakdown = $readerTrackingService->getReaderBreakdown($this->post->id);

        // Get today's analytics
        $analytics = $readerTrackingService->getReaderAnalytics($this->post->id);
        if ($analytics) {
            $this->analytics = [
                'total_today' => $analytics->total_readers_today,
                'authenticated_today' => $analytics->authenticated_readers_today,
                'anonymous_today' => $analytics->anonymous_readers_today,
                'peak_concurrent' => $analytics->peak_concurrent_readers,
                'countries_count' => count($analytics->top_countries ?? []),
            ];
        }
    }

    public function setMetric(string $metric): void
    {
        $this->selectedMetric = $metric;
    }

    public function render()
    {
        return view('livewire.reader-geographics');
    }
}
