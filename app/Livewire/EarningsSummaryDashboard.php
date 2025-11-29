<?php

namespace App\Livewire;

use Livewire\Component;

class EarningsSummaryDashboard extends Component
{
    public $summaryData = [];
    public $earningsTrend = [];
    public $isLoading = false;
    public $timeRange = '30days';

    public function mount()
    {
        $this->loadSummary();
    }

    public function updatedTimeRange()
    {
        $this->loadSummary();
    }

    public function loadSummary()
    {
        $this->isLoading = true;
        try {
            // Get earnings summary
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/invoices/earnings/summary', [
                'period' => $this->timeRange,
            ]);

            $this->summaryData = $response->json() ?? [];

            // Get earnings statistics
            $statsResponse = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/invoices/statistics');

            if ($statsResponse->ok()) {
                $this->earningsTrend = $statsResponse->json('data') ?? [];
            }
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.earnings-summary-dashboard');
    }
}
