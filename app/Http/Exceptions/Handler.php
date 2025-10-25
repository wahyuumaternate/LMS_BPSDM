<?php

namespace App\Http\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Handle API requests specifically
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($exception instanceof ModelNotFoundException) {
                $model = strtolower(class_basename($exception->getModel()));
                return response()->json([
                    'message' => "Resource not found: {$model}"
                ], 404);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'The requested resource was not found'
                ], 404);
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'message' => 'Method not allowed for this endpoint'
                ], 405);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $exception->validator->errors()->getMessages()
                ], 422);
            }

            // Generic API error handler
            if (!config('app.debug')) {
                return response()->json([
                    'message' => 'Server Error',
                ], 500);
            }
        }

        return parent::render($request, $exception);
    }
}
