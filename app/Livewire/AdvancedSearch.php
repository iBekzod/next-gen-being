<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\SearchService;

class AdvancedSearch extends Component
{
    use WithPagination;

    public string $query = '';
    public array $selectedCategories = [];
    public array $selectedTags = [];
    public array $selectedAuthors = [];
    public string $contentType = '';
    public string $engagement = '';
    public string $readTime = '';
    public string $sortBy = 'relevant';
    public string $dateFrom = '';
    public string $dateTo = '';
    public int $minViews = 0;
    public int $maxViews = 0;
    public bool $isPremium = false;
    public bool $showFilters = false;
    public int $perPage = 15;

    public array $availableFilters = [];
    public array $searchStats = [];

    protected $searchService;
    protected $queryString = [
        'query' => ['except' => ''],
        'selectedCategories' => ['except' => []],
        'selectedTags' => ['except' => []],
        'sortBy' => ['except' => 'relevant'],
    ];

    public function mount()
    {
        $this->searchService = app(SearchService::class);
        $this->availableFilters = $this->searchService->getAvailableFilters();
        $this->performSearch();
    }

    public function performSearch()
    {
        $this->resetPage();

        if (!empty($this->query)) {
            $this->searchStats = $this->searchService->getSearchStats($this->query);
        }
    }

    public function updatedQuery()
    {
        $this->performSearch();
    }

    public function updatedSelectedCategories()
    {
        $this->resetPage();
    }

    public function updatedSelectedTags()
    {
        $this->resetPage();
    }

    public function updatedSelectedAuthors()
    {
        $this->resetPage();
    }

    public function updatedContentType()
    {
        $this->resetPage();
    }

    public function updatedEngagement()
    {
        $this->resetPage();
    }

    public function updatedReadTime()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->selectedCategories = [];
        $this->selectedTags = [];
        $this->selectedAuthors = [];
        $this->contentType = '';
        $this->engagement = '';
        $this->readTime = '';
        $this->sortBy = 'relevant';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->minViews = 0;
        $this->maxViews = 0;
        $this->isPremium = false;
        $this->resetPage();
    }

    public function getResults()
    {
        return $this->searchService->search([
            'q' => $this->query,
            'categories' => $this->selectedCategories,
            'tags' => $this->selectedTags,
            'authors' => $this->selectedAuthors,
            'content_type' => $this->contentType ?: null,
            'engagement' => $this->engagement ?: null,
            'read_time' => $this->readTime ?: null,
            'sort_by' => $this->sortBy,
            'date_from' => $this->dateFrom ?: null,
            'date_to' => $this->dateTo ?: null,
            'min_views' => $this->minViews ?: null,
            'max_views' => $this->maxViews ?: null,
            'is_premium' => $this->isPremium ? true : null,
            'per_page' => $this->perPage,
        ]);
    }

    public function render()
    {
        return view('livewire.advanced-search', [
            'results' => $this->getResults(),
            'availableFilters' => $this->availableFilters,
            'activeFilters' => $this->getActiveFiltersCount(),
        ]);
    }

    private function getActiveFiltersCount(): int
    {
        $count = 0;
        $count += count($this->selectedCategories);
        $count += count($this->selectedTags);
        $count += count($this->selectedAuthors);
        if ($this->contentType) $count++;
        if ($this->engagement) $count++;
        if ($this->readTime) $count++;
        if ($this->dateFrom) $count++;
        if ($this->dateTo) $count++;
        if ($this->minViews > 0) $count++;
        if ($this->maxViews > 0) $count++;
        if ($this->isPremium) $count++;

        return $count;
    }
}
