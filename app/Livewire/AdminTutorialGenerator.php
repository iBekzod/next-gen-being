<?php

namespace App\Livewire;

use Livewire\Component;

class AdminTutorialGenerator extends Component
{
    public $tutorials = [];
    public $isLoading = false;
    public $filterStatus = 'all';
    public $showGenerateForm = false;
    public $generationTopic = '';

    public function mount()
    {
        $this->loadTutorials();
    }

    public function loadTutorials()
    {
        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/tutorials/status', [
                'status' => $this->filterStatus !== 'all' ? $this->filterStatus : null,
            ]);

            $this->tutorials = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function generateNewTutorial()
    {
        if (!$this->generationTopic) {
            session()->flash('error', 'Please enter a topic');
            return;
        }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->post('/api/v1/tutorials/trigger', [
            'topic' => $this->generationTopic,
        ]);

        if ($response->ok()) {
            $this->showGenerateForm = false;
            $this->generationTopic = '';
            $this->loadTutorials();
            session()->flash('success', 'Tutorial generation started!');
        } else {
            session()->flash('error', 'Failed to generate tutorial');
        }
    }

    public function publishTutorial($tutorialId)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
        ])->post("/api/v1/tutorials/publish", [
            'tutorial_id' => $tutorialId,
        ]);

        if ($response->ok()) {
            $this->loadTutorials();
            session()->flash('success', 'Tutorial published successfully!');
        }
    }

    public function updatedFilterStatus()
    {
        $this->loadTutorials();
    }

    public function render()
    {
        return view('livewire.admin-tutorial-generator');
    }
}
