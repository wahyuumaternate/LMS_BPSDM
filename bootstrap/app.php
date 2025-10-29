<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            // \App\Http\Middleware\ForceJsonResponse::class,
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
        // Menangani ModelNotFoundException (ketika model tidak ditemukan)
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan',
                    'error' => 'not_found',
                    'status' => 404
                ], 404);
            }
        });

        // Menangani NotFoundHttpException (ketika route tidak ditemukan)
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Endpoint tidak ditemukan',
                    'error' => 'not_found',
                    'status' => 404
                ], 404);
            }
        });

        // Menangani Method Not Allowed Exception
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Metode tidak diizinkan untuk endpoint ini',
                    'error' => 'method_not_allowed',
                    'status' => 405
                ], 405);
            }
        });

        // Menangani Validation Exception
        $exceptions->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid',
                    'errors' => $e->errors(),
                    'error' => 'validation_failed',
                    'status' => 422
                ], 422);
            }
        });

        // Menangani Authentication Exception
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Tidak terautentikasi atau token akses tidak valid',
                    'error' => 'unauthenticated',
                    'status' => 401
                ], 401);
            }
        });

        // Menangani Authorization Exception
        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Tindakan ini tidak diizinkan',
                    'error' => 'forbidden',
                    'status' => 403
                ], 403);
            }
        });

        // Menangani Query Exception (error database)
        $exceptions->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                // Mendapatkan kode error dari PDO Exception
                $errorCode = $e->getCode();
                $message = 'Terjadi kesalahan pada query database';

                // Sembunyikan pesan error query yang sebenarnya di production
                if (!config('app.debug')) {
                    $errorInfo = 'Kesalahan database';
                } else {
                    $errorInfo = $e->getMessage();
                }

                // Error constraint foreign key
                if ($errorCode == 23000) {
                    $message = 'Constraint integritas data dilanggar';
                }

                return response()->json([
                    'message' => $message,
                    'error' => 'database_error',
                    'error_info' => $errorInfo,
                    'status' => 500
                ], 500);
            }
        });

        // Menangani File Not Found Exception
        $exceptions->renderable(function (\Illuminate\Contracts\Filesystem\FileNotFoundException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'File yang diminta tidak ditemukan di server',
                    'error' => 'file_not_found',
                    'status' => 404
                ], 404);
            }
        });

        // Menangani TokenMismatchException (CSRF)
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Token CSRF tidak cocok',
                    'error' => 'invalid_csrf',
                    'status' => 419
                ], 419);
            }
        });

        // Menangani Throttle Requests Exception (Rate Limiting)
        $exceptions->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Terlalu banyak permintaan',
                    'error' => 'rate_limit_exceeded',
                    'status' => 429
                ], 429);
            }
        });

        // Menangani HttpException (umum)
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                $statusCode = $e->getStatusCode();
                $message = $e->getMessage() ?: 'Kesalahan HTTP';

                return response()->json([
                    'message' => $message,
                    'error' => 'http_error',
                    'status' => $statusCode
                ], $statusCode);
            }
        });

        // Menangani PHP Error
        $exceptions->renderable(function (\Error $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                // Log informasi error detail untuk debugging
                Log::error('PHP Error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);

                $message = config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server';

                return response()->json([
                    'message' => $message,
                    'error' => 'server_error',
                    'status' => 500
                ], 500);
            }
        });

        // Menangani exception umum - HARUS yang terakhir
        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: 'Terjadi kesalahan pada server';

                if ($status === 500 && !config('app.debug')) {
                    $message = 'Terjadi kesalahan pada server';
                }

                // Log exception yang tidak tertangkap untuk debugging
                Log::error('Exception tidak tertangkap: ' . get_class($e), [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                return response()->json([
                    'message' => $message,
                    'error' => $status === 500 ? 'server_error' : 'http_error',
                    'status' => $status
                ], $status);
            }
        });
    })
    ->create();
