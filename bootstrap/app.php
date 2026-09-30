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
        $middleware->alias([
            'admin' => \App\Http\Middleware\CheckAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Every unhandled exception goes to the shared error-report bot
        // (Nextgenbeing topic) and to storage/logs/error-feed.jsonl for Jarvis.
        // Filtering (4xx), dedupe and the hourly cap live in ErrorReporter;
        // it never throws back into the request.
        $exceptions->reportable(function (\Throwable $e): void {
            app(\App\Services\Telemetry\ErrorReporter::class)->report($e);
        });
    })->create();
