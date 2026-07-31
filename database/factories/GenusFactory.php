<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Family;
use App\Models\Genus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Genus>
 */
final class GenusFactory extends Factory
{
    protected $model = Genus::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'family_id' => Family::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
        ];
    }
}
