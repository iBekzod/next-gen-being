<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Guest checkout: link the Lemon Squeezy webhook to an account by email.
        $middleware->append(\App\Http\Middleware\ProvisionGuestCheckoutBillable::class);
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Every unhandled exception goes to the shared error-report bot
        // (Nextgenbeing topic) and to storage/logs/error-feed.jsonl for Jarvis.
        // Filtering (4xx), dedupe and the hourly cap live in ErrorReporter;
        // it never throws back into the request.
        // A client trying to write a #[Locked] Livewire property is a tampered
        // request, not a server error: answer 403 instead of 500.
        $exceptions->map(
            \Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class,
            fn ($e) => new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException($e->getMessage(), $e),
        );
        // Bot probes: file uploads posted to components without WithFileUploads,
        // and calls to methods a component does not expose. Malformed client
        // requests, so 400 (not reported by ErrorReporter) instead of 500.
        foreach ([
            \Livewire\Features\SupportFileUploads\MissingFileUploadsTraitException::class,
            \Livewire\Exceptions\MethodNotFoundException::class,
        ] as $probe) {
            $exceptions->map(
                $probe,
                fn ($e) => new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($e->getMessage(), $e),
            );
        }
        $exceptions->reportable(function (\Throwable $e): void {
            app(\App\Services\Telemetry\ErrorReporter::class)->report($e);
        });
    })->create();
