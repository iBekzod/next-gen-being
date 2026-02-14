<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class WebhookLogsBrowser extends Component
{
    use WithPagination;

    public array $logs = [];
    public ?int $webhookId = null;
    public bool $isLoading = false;
    public string $filterStatus = 'all';

    public function mount($webhookId = null)
    {
        $this->webhookId = $webhookId;
        if ($this->webhookId) {
            $this->loadLogs();
        }
    }

    public function loadLogs()
    {
        if (!$this->webhookId) {
            return;
        }

        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get("/api/v1/webhooks/{$this->webhookId}/logs", [
                'status' => $this->filterStatus !== 'all' ? $this->filterStatus : null,
            ]);

            $this->logs = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedFilterStatus()
    {
        $this->loadLogs();
    }

    public function render()
    {
        return view('livewire.webhook-logs-browser');
    }
}
