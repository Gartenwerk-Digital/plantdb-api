<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiVersion
{
    public function handle(Request $request, Closure $next, string $version = 'v1'): mixed
    {
        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('X-API-Version', $version);
        }

        return $response;
    }
}
