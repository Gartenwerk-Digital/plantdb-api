<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetApiLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var list<string> $supported */
        $supported = config('i18n.supported');
        /** @var string $default */
        $default = config('i18n.default');

        $queryLocale = $request->query('locale');
        if (is_string($queryLocale) && $queryLocale !== '') {
            if (! in_array($queryLocale, $supported, true)) {
                return $this->unsupportedLocaleResponse($queryLocale, $supported);
            }

            $locale = $queryLocale;
        } else {
            $header = $request->headers->get('Accept-Language');
            $locale = is_string($header) && $header !== ''
                ? ($request->getPreferredLanguage($supported) ?? $default)
                : $default;
        }

        App::setLocale($locale);

        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('Content-Language', $locale);
        }

        return $response;
    }

    /**
     * @param  list<string>  $supported
     */
    private function unsupportedLocaleResponse(string $requested, array $supported): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'unsupported_locale',
                'message' => sprintf('Locale "%s" is not supported.', $requested),
                'details' => [
                    'supported_locales' => $supported,
                ],
            ],
        ], 400);
    }
}
