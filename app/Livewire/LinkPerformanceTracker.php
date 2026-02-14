<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class LinkPerformanceTracker extends Component
{
    use WithPagination;

    public array $links = [];
    public bool $isLoading = false;
    public string $sortBy = 'clicks';

    public function mount()
    {
        $this->loadLinks();
    }

    public function loadLinks()
    {
        $this->isLoading = true;
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . auth()->user()->api_token ?? auth()->user()->id,
            ])->get('/api/v1/features/affiliate/links', [
                'sort' => $this->sortBy,
            ]);

            $this->links = $response->json('data') ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedSortBy()
    {
        $this->loadLinks();
    }

    public function copyLink($linkId, $url)
    {
        session()->flash('success', 'Link copied to clipboard!');
    }

    public function render()
    {
        return view('livewire.link-performance-tracker');
    }
}
