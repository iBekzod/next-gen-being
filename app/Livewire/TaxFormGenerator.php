<?php

namespace App\Livewire;

use Livewire\Component;

class TaxFormGenerator extends Component
{
    public array $taxForms = [];
    public bool $isLoading = false;
    public ?int $selectedYear = null;
    public array $availableYears = [];

    public function mount()
    {
        $this->availableYears = [now()->year, now()->year - 1, now()->year - 2];
        if (!$this->selectedYear) {
            $this->selectedYear = now()->year;
        }
        $this->loadTaxForms();
    }

    public function updatedSelectedYear()
    {
        $this->loadTaxForms();
    }

    public function loadTaxForms()
    {
        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/invoices/tax-form', [
                'year' => $this->selectedYear,
            ]);

            $this->taxForms = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function downloadTaxForm($formType)
    {
        // Trigger download from backend
        return redirect("/api/v1/invoices/tax-form/download?type={$formType}&year={$this->selectedYear}");
    }

    public function render()
    {
        return view('livewire.tax-form-generator');
    }
}
