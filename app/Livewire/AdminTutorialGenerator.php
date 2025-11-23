<?php

namespace App\Livewire;

use App\Services\AITutorialGenerationService;
use App\Jobs\GenerateTutorialSeriesJob;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class AdminTutorialGenerator extends Component
{
    public $topic = '';
    public $parts = 8;
    public $publish = false;
    public $isGenerating = false;
    public $progress = 0;
    public $generatedTutorials = [];
    public $error = null;
    public $successMessage = null;

    protected $rules = [
        'topic' => 'required|string|min:3|max:100',
        'parts' => 'required|integer|in:3,5,8',
        'publish' => 'boolean',
    ];

    public function render()
    {
        return view('livewire.admin-tutorial-generator');
    }

    /**
     * Generate tutorial series
     */
    public function generateTutorial()
    {
        $this->validate();

        try {
            $this->isGenerating = true;
            $this->error = null;
            $this->successMessage = null;
            $this->progress = 0;

            // Dispatch job for async generation
            GenerateTutorialSeriesJob::dispatch(
                topic: $this->topic,
                parts: $this->parts,
                publish: $this->publish,
                userId: auth()->id(),
            );

            $this->successMessage = "Tutorial generation started! You'll receive a notification when complete.";

            // Reset form
            $this->topic = '';
            $this->parts = 8;
            $this->publish = false;

        } catch (\Exception $e) {
            Log::error('Tutorial generation error', [
                'error' => $e->getMessage(),
                'topic' => $this->topic,
            ]);

            $this->error = "Failed to start generation: " . $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Quick generation for common topics
     */
    public function quickGenerate($templateTopic)
    {
        $templates = [
            'marketplace' => ['Laravel Marketplace Platform', 8],
            'ecommerce' => ['Building E-Commerce with Laravel', 8],
            'api' => ['Building RESTful APIs with Laravel', 5],
            'mobile' => ['Mobile App Backend with Laravel', 5],
            'devops' => ['Docker & Kubernetes Deployment', 5],
            'testing' => ['Comprehensive Testing Strategy', 3],
        ];

        if (!isset($templates[$templateTopic])) {
            $this->error = "Unknown template";
            return;
        }

        [$this->topic, $this->parts] = $templates[$templateTopic];
        $this->generateTutorial();
    }

    /**
     * Preview generated content (optional)
     */
    public function previewGeneration()
    {
        $this->validate();

        try {
            $service = new AITutorialGenerationService();

            // Generate just first part for preview
            $content = $service->generatePartWithRetry(
                topic: $this->topic,
                partNumber: 1,
                totalParts: $this->parts,
                seriesTitle: $service->generateSeriesTitle($this->topic, $this->parts),
            );

            if ($content) {
                $this->dispatch('showPreview', ['content' => $content]);
            }

        } catch (\Exception $e) {
            $this->error = "Preview failed: " . $e->getMessage();
        }
    }
}
