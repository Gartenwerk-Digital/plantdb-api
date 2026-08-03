<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
final class PlantMediaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->collection_name,
            'urls' => [
                'original' => $this->getUrl(),
                'portrait' => $this->getUrl('portrait'),
                'thumb' => $this->getUrl('thumb'),
            ],
            'license' => $this->getCustomProperty('license'),
            'attribution' => $this->getCustomProperty('attribution'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
