<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\PlantCareTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlantCareTask
 */
final class PlantCareTaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_type' => $this->task_type->value,
            'month_start' => $this->month_start,
            'month_end' => $this->month_end,
            'frequency' => $this->frequency,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
