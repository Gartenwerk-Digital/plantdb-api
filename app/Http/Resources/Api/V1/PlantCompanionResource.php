<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plant
 */
final class PlantCompanionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'scientific_name' => $this->scientific_name,
            'relationship' => $this->pivot?->getAttribute('relationship'),
            'notes' => $this->pivot?->getAttribute('notes'),
        ];
    }
}
