<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\CreatorToolsService;

class SEOAnalyzer extends Component
{
    public CreatorToolsService $toolsService;
    public string $title = '';
    public string $description = '';
    public string $content = '';
    public ?array $seoAnalysis = null;
    public array $recommendations = [];
    public bool $isAnalyzing = false;

    public function mount()
    {
        $this->toolsService = app(CreatorToolsService::class);
    }

    public function analyzeSEO()
    {
        $this->validate([
            'title' => 'required|string|max:60',
            'description' => 'required|string|max:160',
            'content' => 'required|string|min:100',
        ]);

        $this->isAnalyzing = true;

        try {
            $result = $this->toolsService->analyzeSEO(
                auth()->user(),
                [
                    'title' => $this->title,
                    'description' => $this->description,
                    'content' => $this->content,
                ]
            );

            $this->seoAnalysis = $result['analysis'] ?? [];
            $this->recommendations = $result['recommendations'] ?? [];

            $this->dispatch('seoAnalyzed');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to analyze SEO');
        } finally {
            $this->isAnalyzing = false;
        }
    }

    public function resetAnalysis()
    {
        $this->title = '';
        $this->description = '';
        $this->content = '';
        $this->seoAnalysis = null;
        $this->recommendations = [];
    }

    public function render()
    {
        return view('livewire.seo-analyzer');
    }
}
