<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Force Accept header to application/json
        $request->headers->set('Accept', 'application/json');

        // Process the request
        $response = $next($request);

        // If the response is HTML and this is an API route, convert it to JSON
        if (
            $request->is('api/*') &&
            $response instanceof Response &&
            strpos($response->headers->get('Content-Type'), 'text/html') !== false
        ) {
            // Get the status code
            $statusCode = $response->getStatusCode();

            // Determine a message based on status code
            $message = match ($statusCode) {
                404 => 'Resource not found',
                403 => 'Forbidden',
                401 => 'Unauthorized',
                419 => 'CSRF token mismatch',
                429 => 'Too many requests',
                500 => 'Server error',
                503 => 'Service unavailable',
                default => 'An error occurred',
            };

            // Create a new JSON response
            return response()->json(['message' => $message], $statusCode);
        }

        return $response;
    }
}
