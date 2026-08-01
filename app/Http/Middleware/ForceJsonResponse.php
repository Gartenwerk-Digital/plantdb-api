<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ForceJsonResponse
{
    /**
     * Ensure all responses are JSON and set proper Accept header.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if ($response instanceof JsonResponse) {
            return $response;
        }

        // Convert unexpected non-JSON responses to the unified error envelope.
        if ($response instanceof Response) {
            return response()->json([
                'error' => [
                    'code' => 'unexpected_response',
                    'message' => 'An error occurred',
                ],
            ], $response->getStatusCode());
        }

        return $response;
    }
}
