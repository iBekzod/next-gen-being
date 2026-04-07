<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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
            $query = Post::whereNotNull('series_title')->orderBy('created_at', 'desc');

            if ($this->filterStatus !== 'all') {
                $query->where('status', $this->filterStatus);
            }

            $this->tutorials = $query->get()->map(fn($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'series' => $post->series_title,
                'part' => $post->series_part,
                'status' => $post->status,
                'created_at' => $post->created_at?->toIso8601String(),
            ])->toArray();
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

        if (!auth()->user()?->isAdmin()) {
            session()->flash('error', 'Unauthorized');
            return;
        }

        try {
            Artisan::call('tutorial:generate', ['topic' => $this->generationTopic, '--parts' => 3]);

            $this->showGenerateForm = false;
            $this->generationTopic = '';
            $this->loadTutorials();
            session()->flash('success', 'Tutorial generation started!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate tutorial: ' . $e->getMessage());
        }
    }

    public function publishTutorial($tutorialId)
    {
        if (!auth()->user()?->isAdmin()) {
            session()->flash('error', 'Unauthorized');
            return;
        }

        $post = Post::find($tutorialId);
        if (!$post) {
            session()->flash('error', 'Tutorial not found');
            return;
        }

        $post->update(['status' => 'published', 'published_at' => now()]);
        $this->loadTutorials();
        session()->flash('success', 'Tutorial published successfully!');
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
