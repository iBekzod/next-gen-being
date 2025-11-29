<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class TutorialBrowser extends Component
{
    use WithPagination;

    public $tutorials = [];
    public $isLoading = false;
    public $filterCategory = 'all';
    public $searchQuery = '';

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
            ])->get('/api/v1/tutorials', [
                'category' => $this->filterCategory !== 'all' ? $this->filterCategory : null,
                'search' => $this->searchQuery,
            ]);

            $this->tutorials = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedFilterCategory()
    {
        $this->loadTutorials();
    }

    public function updatedSearchQuery()
    {
        $this->loadTutorials();
    }

    public function render()
    {
        return view('livewire.tutorial-browser');
    }
}
