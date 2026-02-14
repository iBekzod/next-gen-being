<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\CreatorToolsService;

class ContentIdeaList extends Component
{
    use WithPagination;

    public CreatorToolsService $toolsService;
    public array $ideas = [];
    public string $filter = 'active'; // active, archived, all
    public bool $isLoading = true;

    public function mount()
    {
        $this->toolsService = app(CreatorToolsService::class);
        $this->loadIdeas();
    }

    public function loadIdeas()
    {
        $this->isLoading = true;

        try {
            $user = auth()->user();

            if ($this->filter === 'active') {
                $this->ideas = $user->getActiveContentIdeas(20);
            } else {
                $this->ideas = $user->contentIdeas()
                    ->paginate(20);
            }
        } finally {
            $this->isLoading = false;
        }
    }

    public function deleteIdea($ideaId)
    {
        try {
            $result = $this->toolsService->deleteIdea(auth()->user(), $ideaId);

            if ($result['success']) {
                $this->dispatch('ideaDeleted');
                $this->loadIdeas();
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete idea');
        }
    }

    public function updatedFilter()
    {
        $this->resetPage();
        $this->loadIdeas();
    }

    public function render()
    {
        return view('livewire.content-idea-list');
    }
}
