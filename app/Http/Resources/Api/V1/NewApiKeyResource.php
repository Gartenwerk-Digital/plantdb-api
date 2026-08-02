<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\NewAccessToken;

/**
 * @property NewAccessToken $resource
 */
final class NewApiKeyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $token = $this->resource->accessToken;

        return [
            'id' => $token->getKey(),
            'name' => $token->name,
            'abilities' => $token->abilities ?? [],
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'expires_at' => $token->expires_at?->toIso8601String(),
            'created_at' => $token->created_at?->toIso8601String(),
            'plain_text_token' => $this->resource->plainTextToken,
        ];
    }
}
