<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Models\Contribution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contribution>
 */
final class ContributionFactory extends Factory
{
    protected $model = Contribution::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'plant_id' => null,
            'type' => ContributionType::NewPlant->value,
            'submitted_by' => null,
            'payload' => ['scientific_name' => fake()->word()],
            'status' => ContributionStatus::Pending->value,
        ];
    }
}
