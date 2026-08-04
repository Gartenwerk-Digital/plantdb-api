<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Genus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Genus
 */
final class GenusResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $translation = $this->localizedTranslation();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'family_id' => $this->family_id,
            'common_name' => $translation?->common_name,
            'description' => $translation?->description,
            'plants_count' => $this->whenCounted('plants'),
            'translations' => $this->when(
                str_contains((string) $request->query('include', ''), 'translations'),
                fn () => GenusTranslationResource::collection($this->translations),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
