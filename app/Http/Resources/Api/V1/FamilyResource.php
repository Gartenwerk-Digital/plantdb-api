<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Family
 */
final class FamilyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translation = $this->localizedTranslation();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'common_name' => $translation?->common_name,
            'description' => $translation?->description,
            'plants_count' => $this->whenCounted('plants'),
            'translations' => $this->when(
                str_contains((string) $request->query('include', ''), 'translations'),
                fn () => FamilyTranslationResource::collection($this->translations),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
