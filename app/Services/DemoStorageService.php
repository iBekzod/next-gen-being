<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class DemoStorageService
{
    private const DISK = 'public';

    private function dir(string $slug): string
    {
        return 'demos/'.trim($slug, '/');
    }

    /** Store a single self-contained HTML demo; returns the relative path. */
    public function storeIndexHtml(string $slug, string $html): string
    {
        $path = $this->dir($slug).'/index.html';
        Storage::disk(self::DISK)->put($path, $html);

        return $path;
    }

    public function exists(string $slug): bool
    {
        return Storage::disk(self::DISK)->exists($this->dir($slug).'/index.html');
    }

    public function url(string $slug): string
    {
        return Storage::disk(self::DISK)->url($this->dir($slug).'/index.html');
    }

    public function delete(string $slug): void
    {
        Storage::disk(self::DISK)->deleteDirectory($this->dir($slug));
    }
}
