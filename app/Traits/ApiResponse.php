<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    protected function success(
        mixed $data = null,
        ?string $message = null,
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return response()->json($this->buildSuccessPayload($data, $message), $code);
    }

    protected function created(
        mixed $data = null,
        ?string $message = null
    ): JsonResponse {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    protected function error(
        string $message = 'Error',
        int $code = Response::HTTP_BAD_REQUEST,
        ?string $errorCode = null,
        array $details = []
    ): JsonResponse {
        $payload = [
            'code' => $errorCode ?? self::defaultErrorCode($code),
            'message' => $message,
        ];

        if ($details !== []) {
            $payload['details'] = $details;
        }

        return response()->json(['error' => $payload], $code);
    }

    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND, 'not_found');
    }

    protected function unauthorized(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED, 'unauthenticated');
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN, 'forbidden');
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, 'validation_failed', $errors);
    }

    /**
     * @return LengthAwarePaginator<int, mixed>|null
     */
    private static function extractPaginator(mixed $data): ?LengthAwarePaginator
    {
        if ($data instanceof LengthAwarePaginator) {
            return $data;
        }

        if ($data instanceof ResourceCollection && $data->resource instanceof LengthAwarePaginator) {
            return $data->resource;
        }

        return null;
    }

    private static function defaultErrorCode(int $status): string
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

    /**
     * @return array<string, mixed>
     */
    private function buildSuccessPayload(mixed $data, ?string $message): array
    {
        $paginator = self::extractPaginator($data);

        if ($paginator instanceof LengthAwarePaginator) {
            $body = [
                'data' => $data instanceof ResourceCollection
                    ? $data->resolve(request())
                    : $paginator->items(),
                'meta' => [
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'last_page' => $paginator->lastPage(),
                    ],
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ];
        } else {
            $body = ['data' => $data];
        }

        if ($message !== null) {
            $body['meta'] = array_merge($body['meta'] ?? [], ['message' => $message]);
        }

        return $body;
    }
}
