<?php

namespace App\Livewire;

use Livewire\Component;

class AffiliateEarningsChart extends Component
{
    public $earningsData = [];
    public $isLoading = false;
    public $timeRange = '30days';

    public function mount()
    {
        $this->loadEarnings();
    }

    public function updatedTimeRange()
    {
        $this->loadEarnings();
    }

    public function loadEarnings()
    {
        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/features/affiliate/earnings', [
                'period' => $this->timeRange,
            ]);

            $this->earningsData = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.affiliate-earnings-chart');
    }
}
