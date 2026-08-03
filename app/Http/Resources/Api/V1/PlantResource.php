<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plant
 */
final class PlantResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'scientific_name' => $this->scientific_name,
            'family_id' => $this->family_id,
            'genus_id' => $this->genus_id,
            'cultivar' => $this->cultivar,
            'status' => $this->status->value,

            'life_cycle' => $this->life_cycle?->value,
            'deciduous' => $this->deciduous,
            'native_regions' => $this->native_regions,
            'height_min_cm' => $this->height_min_cm,
            'height_max_cm' => $this->height_max_cm,
            'width_min_cm' => $this->width_min_cm,
            'width_max_cm' => $this->width_max_cm,
            'growth_rate' => $this->growth_rate?->value,
            'root_depth' => $this->root_depth?->value,

            'sun_requirement' => $this->sun_requirement?->value,
            'hardiness_zone_min' => $this->hardiness_zone_min,
            'hardiness_zone_max' => $this->hardiness_zone_max,
            'suitable_for_pot' => $this->suitable_for_pot,
            'soil_types' => $this->soil_types,
            'soil_ph_min' => $this->soil_ph_min,
            'soil_ph_max' => $this->soil_ph_max,
            'soil_moisture' => $this->soil_moisture?->value,

            'bloom_start_month' => $this->bloom_start_month,
            'bloom_end_month' => $this->bloom_end_month,
            'bloom_colors' => $this->bloom_colors,
            'fragrant' => $this->fragrant,
            'fruit_season_start' => $this->fruit_season_start,
            'fruit_season_end' => $this->fruit_season_end,
            'edible_parts' => $this->edible_parts,
            'harvest_notes' => $this->harvest_notes,

            'watering_frequency' => $this->watering_frequency?->value,
            'fertilizing_frequency' => $this->fertilizing_frequency?->value,
            'pruning_required' => $this->pruning_required,
            'maintenance_level' => $this->maintenance_level?->value,

            'toxic_to_humans' => $this->toxic_to_humans,
            'toxic_to_pets' => $this->toxic_to_pets,
            'toxic_to_livestock' => $this->toxic_to_livestock,
            'invasive' => $this->invasive,
            'allergy_potential' => $this->allergy_potential?->value,
            'attracts_bees' => $this->attracts_bees,
            'attracts_butterflies' => $this->attracts_butterflies,
            'deer_resistant' => $this->deer_resistant,

            'propagation_methods' => $this->propagation_methods,

            'images' => PlantMediaResource::collection($this->whenLoaded('media')),
            'companions' => PlantCompanionResource::collection($this->whenLoaded('companions')),
            'care_tasks' => PlantCareTaskResource::collection($this->whenLoaded('careTasks')),
            'translations' => PlantTranslationResource::collection($this->whenLoaded('translations')),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
