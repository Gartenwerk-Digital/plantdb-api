<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\ContributionType;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $type
 * @property string|null $plant_id
 * @property array<string, mixed> $payload
 */
final class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(ContributionType::values())],
            'plant_id' => ['nullable', 'uuid'],
            'payload' => ['required', 'array', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $typeInput = $this->input('type');
            $type = is_string($typeInput) ? ContributionType::tryFrom($typeInput) : null;

            if (! $type instanceof ContributionType) {
                return;
            }

            $plantId = $this->input('plant_id');
            $payload = $this->input('payload', []);

            match ($type) {
                ContributionType::Image => $validator->errors()->add(
                    'type',
                    'Image contributions are not yet supported via the API. Coming in a future release.',
                ),
                ContributionType::NewPlant => $this->validateNewPlant($validator, $plantId, $payload),
                ContributionType::Update, ContributionType::Correction => $this->validateUpdate($validator, $plantId),
            };
        });
    }

    /**
     * @param  array<string, mixed>|mixed  $payload
     */
    private function validateNewPlant(Validator $validator, mixed $plantId, mixed $payload): void
    {
        if ($plantId !== null) {
            $validator->errors()->add('plant_id', 'plant_id must be omitted for new_plant contributions.');
        }

        if (! is_array($payload)) {
            return;
        }

        if (! isset($payload['scientific_name']) || ! is_string($payload['scientific_name']) || mb_trim($payload['scientific_name']) === '') {
            $validator->errors()->add('payload.scientific_name', 'payload.scientific_name is required for new_plant contributions.');
        }

        $familyId = $payload['family_id'] ?? null;
        if (! is_string($familyId) || $familyId === '') {
            $validator->errors()->add('payload.family_id', 'payload.family_id is required for new_plant contributions.');
        } elseif (! Family::query()->whereKey($familyId)->exists()) {
            $validator->errors()->add('payload.family_id', 'The selected family does not exist.');
        }

        $genusId = $payload['genus_id'] ?? null;
        if (! is_string($genusId) || $genusId === '') {
            $validator->errors()->add('payload.genus_id', 'payload.genus_id is required for new_plant contributions.');
        } elseif (! Genus::query()->whereKey($genusId)->exists()) {
            $validator->errors()->add('payload.genus_id', 'The selected genus does not exist.');
        }
    }

    private function validateUpdate(Validator $validator, mixed $plantId): void
    {
        if (! is_string($plantId) || $plantId === '') {
            $validator->errors()->add('plant_id', 'plant_id is required for update and correction contributions.');

            return;
        }

        $exists = Plant::query()->whereKey($plantId)->exists();

        if (! $exists) {
            $validator->errors()->add('plant_id', 'The selected plant does not exist.');
        }
    }
}
