<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Services\ReaderTrackingService;

class LiveReaders extends Component
{
    public Post $post;
    public int $activeReaderCount = 0;
    public array $readerBreakdown = [];
    public array $liveReadersList = [];
    public array $topCountries = [];

    protected $listeners = ['refresh' => 'loadReaderData'];

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadReaderData();
    }

    public function loadReaderData(): void
    {
        $readerTrackingService = app(ReaderTrackingService::class);

        // Get active reader count and breakdown
        $this->activeReaderCount = $readerTrackingService->getActiveReaderCount($this->post->id);
        $this->readerBreakdown = $readerTrackingService->getReaderBreakdown($this->post->id);
        $this->liveReadersList = $readerTrackingService->getLiveReadersList($this->post->id);
        $this->topCountries = $readerTrackingService->getTopCountries($this->post->id, 5);
    }

    public function render()
    {
        return view('livewire.live-readers');
    }
}
