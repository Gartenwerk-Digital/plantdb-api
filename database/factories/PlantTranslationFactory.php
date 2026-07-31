<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantTranslation>
 */
final class PlantTranslationFactory extends Factory
{
    protected $model = PlantTranslation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'locale' => 'de',
            'common_name' => fake()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
