<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use App\Services\ReaderTrackingService;

class ReaderMap extends Component
{
    public Post $post;
    public array $mapData = [];
    public array $topCountries = [];
    public int $totalReaderLocations = 0;

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->loadMapData();
    }

    public function loadMapData(): void
    {
        $readerTrackingService = app(ReaderTrackingService::class);

        // Get GeoJSON data for map
        $this->mapData = $readerTrackingService->getReaderMapData($this->post->id);

        // Get top countries
        $this->topCountries = $readerTrackingService->getTopCountries($this->post->id, 10);

        // Count unique reader locations
        $this->totalReaderLocations = count($this->mapData['features'] ?? []);
    }

    public function render()
    {
        return view('livewire.reader-map');
    }
}
