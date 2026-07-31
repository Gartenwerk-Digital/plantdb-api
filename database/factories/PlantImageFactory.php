<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlantImageType;
use App\Models\Plant;
use App\Models\PlantImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlantImage>
 */
final class PlantImageFactory extends Factory
{
    protected $model = PlantImage::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'plant_id' => Plant::factory(),
            'url' => fake()->imageUrl(),
            'type' => PlantImageType::Portrait->value,
            'license' => 'CC BY 4.0',
            'attribution' => fake()->name(),
        ];
    }
}
