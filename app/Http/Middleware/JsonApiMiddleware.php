<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JsonApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Accept header to application/json for API routes
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        // Handle the request and get the response
        $response = $next($request);

        // Only modify JSON responses
        if (!$response->headers->contains('Content-Type', 'application/json') && 
            !$request->expectsJson() &&
            !$request->is('api/*')) {
            return $response;
        }

        // Ensure we return a JSON response for API routes
        if ($request->is('api/*') && !$response instanceof \Illuminate\Http\JsonResponse) {
            // Convert HTML responses to JSON for API routes (like error pages)
            if ($response->getStatusCode() >= 400) {
                $statusText = Response::$statusTexts[$response->getStatusCode()] ?? 'Unknown Error';
                return response()->json([
                    'message' => $statusText
                ], $response->getStatusCode());
            }
        }

        return $response;
    }
}
