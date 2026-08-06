<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plant
 */
final class SitemapPlantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'locales' => $this->translations
                ->pluck('locale')
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
