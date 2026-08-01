<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

final class ApiExceptionRenderer
{
    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $this->isApiRequest($request)) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => $this->json(Response::HTTP_UNPROCESSABLE_ENTITY, 'validation_failed', $e->getMessage(), $e->errors()),
            $e instanceof AuthenticationException => $this->json(Response::HTTP_UNAUTHORIZED, 'unauthenticated', $e->getMessage() !== '' ? $e->getMessage() : 'Unauthenticated.'),
            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException => $this->json(Response::HTTP_FORBIDDEN, 'forbidden', $e->getMessage() !== '' ? $e->getMessage() : 'This action is unauthorized.'),
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => $this->json(Response::HTTP_NOT_FOUND, 'not_found', $e->getMessage() !== '' ? $e->getMessage() : 'Resource not found.'),
            $e instanceof MethodNotAllowedHttpException => $this->json(Response::HTTP_METHOD_NOT_ALLOWED, 'method_not_allowed', $e->getMessage() !== '' ? $e->getMessage() : 'Method not allowed.'),
            $e instanceof TooManyRequestsHttpException => $this->json(Response::HTTP_TOO_MANY_REQUESTS, 'too_many_requests', $e->getMessage() !== '' ? $e->getMessage() : 'Too many requests.'),
            $e instanceof HttpExceptionInterface => $this->json($e->getStatusCode(), $this->codeForStatus($e->getStatusCode()), $e->getMessage() !== '' ? $e->getMessage() : Response::$statusTexts[$e->getStatusCode()] ?? 'Error'),
            default => $this->json(Response::HTTP_INTERNAL_SERVER_ERROR, 'server_error', config('app.debug') === true ? $e->getMessage() : 'Server error.'),
        };
    }

    private function isApiRequest(Request $request): bool
    {
        if ($request->is('api/*')) {
            return true;
        }

        return $request->expectsJson();
    }

    /**
     * @param  array<array-key, mixed>  $details
     */
    private function json(int $status, string $code, string $message, array $details = []): JsonResponse
    {
        $payload = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== []) {
            $payload['details'] = $details;
        }

        return response()->json(['error' => $payload], $status);
    }

    private function codeForStatus(int $status): string
    {
        return match ($status) {
            Response::HTTP_BAD_REQUEST => 'bad_request',
            Response::HTTP_UNAUTHORIZED => 'unauthenticated',
            Response::HTTP_FORBIDDEN => 'forbidden',
            Response::HTTP_NOT_FOUND => 'not_found',
            Response::HTTP_METHOD_NOT_ALLOWED => 'method_not_allowed',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'validation_failed',
            Response::HTTP_TOO_MANY_REQUESTS => 'too_many_requests',
            Response::HTTP_SERVICE_UNAVAILABLE => 'service_unavailable',
            default => $status >= 500 ? 'server_error' : 'error',
        };
    }
}
