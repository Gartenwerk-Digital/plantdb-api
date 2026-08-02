<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\CreateApiKey;
use App\Actions\Auth\RevokeApiKey;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreApiKeyRequest;
use App\Http\Resources\Api\V1\ApiKeyResource;
use App\Http\Resources\Api\V1\NewApiKeyResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

final class ApiKeyController extends ApiController
{
    /**
     * List the authenticated user's API keys.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(
            ApiKeyResource::collection($user->tokens()->latest('id')->get()),
        );
    }

    /**
     * Create a new named API key. The plaintext token is returned only once.
     */
    public function store(StoreApiKeyRequest $request, CreateApiKey $createApiKey): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $expiresAt = $request->expires_at !== null
            ? Date::parse($request->expires_at)
            : null;

        $token = $createApiKey($user, $request->name, $request->abilities, $expiresAt);

        return $this->created(
            new NewApiKeyResource($token),
            'API key created successfully. Store the plain_text_token now; it will not be shown again.',
        );
    }

    /**
     * Revoke one of the authenticated user's API keys.
     */
    public function destroy(Request $request, RevokeApiKey $revokeApiKey, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $revokeApiKey($user, $id);
        } catch (ModelNotFoundException) {
            return $this->notFound('API key not found');
        }

        return $this->noContent();
    }
}
