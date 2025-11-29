<?php

namespace App\Livewire;

use Livewire\Component;

class WebhookTestingPanel extends Component
{
    public $webhooks = [];
    public $selectedWebhook = null;
    public $testResponse = null;
    public $isTestingLoading = false;

    public function mount()
    {
        $this->loadWebhooks();
    }

    public function loadWebhooks()
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->get('/api/v1/webhooks');

        $this->webhooks = $response->json('data') ?? [];
    }

    public function testWebhook($webhookId)
    {
        $this->isTestingLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->post("/api/v1/webhooks/{$webhookId}/test");

            $this->testResponse = [
                'success' => $response->ok(),
                'message' => $response->json('message') ?? 'Test sent',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            $this->testResponse = [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => 'error',
            ];
        } finally {
            $this->isTestingLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.webhook-testing-panel');
    }
}
