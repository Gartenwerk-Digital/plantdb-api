<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Contribution
 */
final class ContributionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'plant_id' => $this->plant_id,
            'payload' => $this->payload,
            'submitted_by' => $this->submitted_by,
            'submitted_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
