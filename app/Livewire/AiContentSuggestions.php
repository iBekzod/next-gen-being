<?php

namespace App\Livewire;

use App\Models\AiContentSuggestion;
use App\Services\AiContentService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class AiContentSuggestions extends Component
{
    use WithPagination;

    public bool $isGenerating = false;
    public string $statusFilter = 'all';
    public string $sortBy = 'relevance';

    public function generateSuggestions()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'content_manager'])) {
            abort(403);
        }

        $this->isGenerating = true;

        try {
            app(AiContentService::class)->generateContentSuggestions();

            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'AI content suggestions generated successfully!'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Failed to generate suggestions: ' . $e->getMessage()
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function approveSuggestion(AiContentSuggestion $suggestion)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'content_manager'])) {
            abort(403);
        }

        $suggestion->approve(Auth::user());

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Suggestion approved!'
        ]);
    }

    public function rejectSuggestion(AiContentSuggestion $suggestion)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'content_manager'])) {
            abort(403);
        }
        
        $suggestion->reject(Auth::user());

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Suggestion rejected!'
        ]);
    }

    public function render()
    {
        return view('livewire.ai-content-suggestions', [
            'suggestions' => AiContentSuggestion::with(['suggestedBy', 'reviewedBy'])
                ->latest()
                ->paginate(10),
        ]);
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }
}
