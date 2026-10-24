<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\ResolveFarm;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // SPA cookie/session auth: requests from SANCTUM_STATEFUL_DOMAINS run the
        // web session + CSRF stack, so the SPA authenticates via session cookies
        // (no Bearer tokens).
        $middleware->statefulApi();

        // API-only backend: treat every api/* request as JSON so auth failures
        // return 401 JSON instead of redirecting to a non-existent web login route.
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        // Attach to authenticated routes to activate farm scoping (after auth:sanctum).
        $middleware->alias([
            'farm' => ResolveFarm::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
