<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class WebhookManager extends Component
{
    use WithPagination;

    public array $webhooks = [];
    public bool $isLoading = false;
    public bool $showCreateForm = false;
    public array $webhookData = ['url' => '', 'events' => [], 'active' => true];
    public ?array $editingWebhook = null;

    public function mount()
    {
        $this->loadWebhooks();
    }

    public function loadWebhooks()
    {
        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/webhooks');

            $this->webhooks = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        if (!$this->showCreateForm) {
            $this->resetForm();
        }
    }

    public function createWebhook()
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->post('/api/v1/webhooks', [
            'url' => $this->webhookData['url'],
            'events' => $this->webhookData['events'],
            'active' => $this->webhookData['active'],
        ]);

        if ($response->ok()) {
            $this->showCreateForm = false;
            $this->resetForm();
            $this->loadWebhooks();
            session()->flash('success', 'Webhook created successfully!');
        } else {
            session()->flash('error', 'Failed to create webhook');
        }
    }

    public function deleteWebhook($webhookId)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->delete("/api/v1/webhooks/{$webhookId}");

        if ($response->ok()) {
            $this->loadWebhooks();
            session()->flash('success', 'Webhook deleted successfully!');
        }
    }

    public function toggleWebhookActive($webhookId, $currentStatus)
    {
        \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->put("/api/v1/webhooks/{$webhookId}", [
            'active' => !$currentStatus,
        ]);

        $this->loadWebhooks();
    }

    public function resetForm()
    {
        $this->webhookData = ['url' => '', 'events' => [], 'active' => true];
        $this->editingWebhook = null;
    }

    public function render()
    {
        return view('livewire.webhook-manager');
    }
}
