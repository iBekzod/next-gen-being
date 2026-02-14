<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = 'all';
    public ?int $selectedInvoice = null;
    public bool $isLoading = false;

    protected $queryString = ['search', 'filterStatus'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function loadInvoices()
    {
        $this->isLoading = true;
        try {
            // Call API endpoint to get invoices
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/invoices/payouts', [
                'search' => $this->search,
                'status' => $this->filterStatus !== 'all' ? $this->filterStatus : null,
            ]);

            return $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function downloadInvoice($invoiceId)
    {
        // Download invoice PDF from backend
        return response()->download("/api/v1/invoices/payout/{$invoiceId}/download");
    }

    public function selectInvoice($invoiceId)
    {
        $this->selectedInvoice = $invoiceId;
    }

    public function render()
    {
        return view('livewire.invoice-manager', [
            'invoices' => $this->loadInvoices(),
        ]);
    }
}
