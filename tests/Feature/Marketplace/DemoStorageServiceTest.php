<?php

namespace Tests\Feature\Marketplace;

use App\Services\DemoStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoStorageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_and_serves_a_self_contained_demo(): void
    {
        Storage::fake('public');
        $service = new DemoStorageService();

        $path = $service->storeIndexHtml('fittrack', '<!doctype html><title>Demo</title><h1>Live</h1>');

        $this->assertSame('demos/fittrack/index.html', $path);
        Storage::disk('public')->assertExists('demos/fittrack/index.html');
        $this->assertTrue($service->exists('fittrack'));
        $this->assertStringContainsString('demos/fittrack/index.html', $service->url('fittrack'));
    }

    public function test_exists_is_false_when_no_demo(): void
    {
        Storage::fake('public');
        $this->assertFalse((new DemoStorageService())->exists('nope'));
    }
}
