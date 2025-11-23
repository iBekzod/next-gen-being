<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReaderPreferenceService;

class ReaderPreferenceUI extends Component
{
    public ReaderPreferenceService $preferenceService;
    public $preference = null;
    public $selectedCategories = [];
    public $selectedAuthors = [];
    public $selectedTags = [];
    public $contentTypeScores = [];
    public $isLoading = true;

    public function mount()
    {
        $this->preferenceService = app(ReaderPreferenceService::class);
        $this->loadPreferences();
    }

    public function loadPreferences()
    {
        $this->isLoading = true;

        try {
            $user = auth()->user();
            $this->preference = $user->getOrCreateReaderPreference();

            $this->selectedCategories = $this->preference->preferred_categories ?? [];
            $this->selectedAuthors = $this->preference->preferred_authors ?? [];
            $this->selectedTags = $this->preference->preferred_tags ?? [];
            $this->contentTypeScores = $this->preference->content_type_scores ?? [];
        } finally {
            $this->isLoading = false;
        }
    }

    public function savePreferences()
    {
        try {
            $result = $this->preferenceService->updatePreferences(
                auth()->user(),
                [
                    'categories' => $this->selectedCategories,
                    'authors' => $this->selectedAuthors,
                    'tags' => $this->selectedTags,
                    'contentTypeScores' => $this->contentTypeScores,
                ]
            );

            if ($result['success']) {
                $this->dispatch('preferencesUpdated');
            }
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save preferences');
        }
    }

    public function resetPreferences()
    {
        $this->preferenceService->resetPreferences(auth()->user());
        $this->loadPreferences();
        $this->dispatch('preferencesReset');
    }

    public function render()
    {
        return view('livewire.reader-preference-ui');
    }
}
