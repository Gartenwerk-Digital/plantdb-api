<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AllergyPotential;
use App\Enums\FertilizingFrequency;
use App\Enums\GrowthRate;
use App\Enums\LifeCycle;
use App\Enums\MaintenanceLevel;
use App\Enums\PlantStatus;
use App\Enums\RootDepth;
use App\Enums\SoilMoisture;
use App\Enums\SunRequirement;
use App\Enums\WateringFrequency;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use BackedEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plant>
 */
final class PlantFactory extends Factory
{
    protected $model = Plant::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $scientificName = ucfirst(fake()->unique()->word()).' '.fake()->word();
        $family = Family::factory();

        return [
            'slug' => Str::slug($scientificName).'-'.Str::random(4),
            'scientific_name' => $scientificName,
            'family_id' => $family,
            'genus_id' => Genus::factory()->state(fn (array $attributes, ?Model $model): array => [
                'family_id' => $family,
            ]),
            'cultivar' => null,
            'life_cycle' => $this->pickEnum(LifeCycle::cases()),
            'deciduous' => fake()->boolean(),
            'native_regions' => fake()->randomElements(['Europa', 'Westasien', 'Nordamerika', 'Ostasien'], 2),
            'height_min_cm' => fake()->numberBetween(10, 100),
            'height_max_cm' => fake()->numberBetween(100, 400),
            'width_min_cm' => fake()->numberBetween(10, 80),
            'width_max_cm' => fake()->numberBetween(80, 300),
            'growth_rate' => $this->pickEnum(GrowthRate::cases()),
            'root_depth' => $this->pickEnum(RootDepth::cases()),
            'sun_requirement' => $this->pickEnum(SunRequirement::cases()),
            'hardiness_zone_min' => fake()->numberBetween(3, 6),
            'hardiness_zone_max' => fake()->numberBetween(7, 10),
            'suitable_for_pot' => fake()->boolean(),
            'soil_types' => fake()->randomElements(['loamy', 'sandy', 'clay', 'chalky'], 2),
            'soil_ph_min' => fake()->randomFloat(1, 4.5, 6.5),
            'soil_ph_max' => fake()->randomFloat(1, 6.6, 8.0),
            'soil_moisture' => $this->pickEnum(SoilMoisture::cases()),
            'bloom_start_month' => fake()->numberBetween(3, 6),
            'bloom_end_month' => fake()->numberBetween(7, 10),
            'bloom_colors' => fake()->randomElements(['rot', 'weiß', 'gelb', 'blau'], 2),
            'fragrant' => fake()->boolean(),
            'fruit_season_start' => null,
            'fruit_season_end' => null,
            'edible_parts' => null,
            'harvest_notes' => null,
            'watering_frequency' => $this->pickEnum(WateringFrequency::cases()),
            'fertilizing_frequency' => $this->pickEnum(FertilizingFrequency::cases()),
            'pruning_required' => fake()->boolean(),
            'maintenance_level' => $this->pickEnum(MaintenanceLevel::cases()),
            'toxic_to_humans' => false,
            'toxic_to_pets' => false,
            'toxic_to_livestock' => false,
            'invasive' => false,
            'allergy_potential' => AllergyPotential::None->value,
            'attracts_bees' => fake()->boolean(),
            'attracts_butterflies' => fake()->boolean(),
            'deer_resistant' => fake()->boolean(),
            'propagation_methods' => fake()->randomElements(['Samen', 'Steckling', 'Teilung'], 2),
            'status' => PlantStatus::Approved->value,
        ];
    }

    /**
     * @param  non-empty-array<int, BackedEnum>  $cases
     */
    private function pickEnum(array $cases): string|int
    {
        return $cases[random_int(0, count($cases) - 1)]->value;
    }
}
