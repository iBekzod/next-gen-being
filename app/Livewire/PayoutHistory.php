<?php

namespace App\Livewire;

use Livewire\Component;

class PayoutHistory extends Component
{
    public $payouts = [];
    public $isLoading = false;
    public $selectedPayout = null;

    public function mount()
    {
        $this->loadPayouts();
    }

    public function loadPayouts()
    {
        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/invoices/payouts');

            $this->payouts = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function selectPayout($payoutId)
    {
        $this->selectedPayout = $payoutId;
    }

    public function render()
    {
        return view('livewire.payout-history');
    }
}
