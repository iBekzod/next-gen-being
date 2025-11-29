<?php

namespace App\Livewire;

use Livewire\Component;

class ReaderAnalyticsDashboard extends Component
{
    public $postId = null;
    public $analyticsData = [];
    public $isLoading = false;
    public $timeRange = '30days';

    public function mount($postId = null)
    {
        $this->postId = $postId;
        $this->loadAnalytics();
    }

    public function updatedTimeRange()
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics()
    {
        $this->isLoading = true;
        try {
            // Get reader analytics
            if ($this->postId) {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
                ])->get("/api/reader-tracking/{$this->postId}/analytics", [
                    'period' => $this->timeRange,
                ]);
            } else {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
                ])->get('/api/v1/features/analytics/audience', [
                    'period' => $this->timeRange,
                ]);
            }

            $this->analyticsData = $response->json() ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.reader-analytics-dashboard');
    }
}
