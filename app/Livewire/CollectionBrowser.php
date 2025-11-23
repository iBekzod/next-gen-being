<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\CollectionService;

class CollectionBrowser extends Component
{
    use WithPagination;

    public CollectionService $collectionService;
    public $collections = [];
    public $view = 'grid'; // grid, list
    public $sort = 'latest'; // latest, popular, trending
    public $isLoading = true;

    public function mount()
    {
        $this->collectionService = app(CollectionService::class);
        $this->loadCollections();
    }

    public function loadCollections()
    {
        $this->isLoading = true;

        try {
            // Get public collections
            $this->collections = $this->collectionService->getPublicCollections(
                page: $this->getPage(),
                perPage: 12,
                sortBy: $this->sort
            );
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedSort()
    {
        $this->resetPage();
        $this->loadCollections();
    }

    public function saveCollection($collectionId)
    {
        if (!auth()->check()) {
            $this->dispatch('requireLogin');
            return;
        }

        $result = $this->collectionService->toggleSaveCollection(
            auth()->user(),
            $collectionId
        );

        if ($result['success']) {
            $this->dispatch('collectionSaved', ['id' => $collectionId]);
            $this->loadCollections();
        }
    }

    public function render()
    {
        return view('livewire.collection-browser');
    }
}
