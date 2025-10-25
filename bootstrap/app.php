<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php', // Tambahkan ini!
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->group('api', [
            \App\Http\Middleware\ForceJsonResponse::class, // Add this at the BEGINNING of the array
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\JsonApiMiddleware::class,
        ]);

        // Exclude CSRF untuk semua route API
        $middleware->validateCsrfTokens(except: [
            'api/*',
            '/api/*',
            'http://*/api/*',
            'https://*/api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Resource not found'], 404);
            }
        });

        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Endpoint not found'], 404);
            }
        });

        $exceptions->renderable(function (\Exception $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: 'Server Error';

                if ($status === 500 && !config('app.debug')) {
                    $message = 'Server Error';
                }

                return response()->json(['message' => $message], $status);
            }
        });
    })
    ->create();
