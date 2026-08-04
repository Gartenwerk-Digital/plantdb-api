<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Genus;
use App\Models\GenusTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GenusTranslation>
 */
final class GenusTranslationFactory extends Factory
{
    protected $model = GenusTranslation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'genus_id' => Genus::factory(),
            'locale' => 'de',
            'common_name' => fake()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
