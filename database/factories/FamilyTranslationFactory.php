<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Family;
use App\Models\FamilyTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FamilyTranslation>
 */
final class FamilyTranslationFactory extends Factory
{
    protected $model = FamilyTranslation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'family_id' => Family::factory(),
            'locale' => 'de',
            'common_name' => fake()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
