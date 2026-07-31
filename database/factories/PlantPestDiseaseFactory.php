<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PestDiseaseType;
use App\Models\Plant;
use App\Models\PlantPestDisease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantPestDisease>
 */
final class PlantPestDiseaseFactory extends Factory
{
    protected $model = PlantPestDisease::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'name' => fake()->word(),
            'type' => PestDiseaseType::Pest->value,
            'treatment_notes' => null,
        ];
    }
}
