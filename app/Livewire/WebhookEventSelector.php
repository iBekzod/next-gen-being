<?php

namespace App\Livewire;

use Livewire\Component;

class WebhookEventSelector extends Component
{
    public array $availableEvents = [];
    public array $selectedEvents = [];

    public function mount()
    {
        $this->loadAvailableEvents();
    }

    public function loadAvailableEvents()
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->get('/api/v1/webhooks/events/available');

        $this->availableEvents = $response->json('data') ?? [];
    }

    public function toggleEvent($event)
    {
        if (in_array($event, $this->selectedEvents)) {
            $this->selectedEvents = array_values(array_diff($this->selectedEvents, [$event]));
        } else {
            $this->selectedEvents[] = $event;
        }

        $this->dispatch('eventsSelected', events: $this->selectedEvents);
    }

    public function render()
    {
        return view('livewire.webhook-event-selector');
    }
}
