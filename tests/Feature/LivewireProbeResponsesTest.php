<?php

namespace Tests\Feature;

use App\Livewire\NewsletterSubscribe;
use App\Services\Telemetry\ErrorReporter;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Features\SupportFileUploads\MissingFileUploadsTraitException;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireProbeResponsesTest extends TestCase
{
    public function test_upload_probe_on_component_without_uploads_throws_missing_trait(): void
    {
        $this->expectException(MissingFileUploadsTraitException::class);

        Livewire::test(NewsletterSubscribe::class)->call('_startUpload', 'email', [['name' => 'x.php']], false);
    }

    public function test_upload_probe_is_400_and_not_reported(): void
    {
        $this->withoutVite();

        $this->assertProbeIs400AndIgnored(new MissingFileUploadsTraitException(new NewsletterSubscribe));
    }

    public function test_unknown_method_probe_is_400_and_not_reported(): void
    {
        $this->withoutVite();

        $this->assertProbeIs400AndIgnored(new MethodNotFoundException('_xNonExist'));
    }

    private function assertProbeIs400AndIgnored(\Throwable $e): void
    {
        $handler = app(ExceptionHandler::class);
        $response = $handler->render(request(), $e);

        $this->assertSame(400, $response->getStatusCode());

        // What the handler actually reports is the mapped exception.
        $mapped = (fn () => $this->mapException($e))->call($handler);
        $this->assertFalse(app(ErrorReporter::class)->shouldReport($mapped));
    }
}
