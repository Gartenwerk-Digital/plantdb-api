<?php

declare(strict_types=1);

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;

final class PlantSeeder extends Seeder
{
    public function run(): void
    {
        $rosaceae = Family::query()->firstOrCreate(['slug' => 'rosaceae'], ['name' => 'Rosaceae', 'description' => 'Rosengewächse']);
        $solanaceae = Family::query()->firstOrCreate(['slug' => 'solanaceae'], ['name' => 'Solanaceae', 'description' => 'Nachtschattengewächse']);
        $lamiaceae = Family::query()->firstOrCreate(['slug' => 'lamiaceae'], ['name' => 'Lamiaceae', 'description' => 'Lippenblütler']);

        $rosa = Genus::query()->firstOrCreate(['slug' => 'rosa'], ['family_id' => $rosaceae->id, 'name' => 'Rosa']);
        $solanum = Genus::query()->firstOrCreate(['slug' => 'solanum'], ['family_id' => $solanaceae->id, 'name' => 'Solanum']);
        $lavandula = Genus::query()->firstOrCreate(['slug' => 'lavandula'], ['family_id' => $lamiaceae->id, 'name' => 'Lavandula']);

        Plant::query()->updateOrCreate(['slug' => 'rosa-centifolia'], [
            'scientific_name' => 'Rosa centifolia',
            'family_id' => $rosaceae->id,
            'genus_id' => $rosa->id,
            'life_cycle' => LifeCycle::Perennial->value,
            'deciduous' => true,
            'native_regions' => ['Europa', 'Westasien'],
            'height_min_cm' => 100,
            'height_max_cm' => 200,
            'width_min_cm' => 80,
            'width_max_cm' => 150,
            'growth_rate' => GrowthRate::Medium->value,
            'root_depth' => RootDepth::Medium->value,
            'sun_requirement' => SunRequirement::FullSun->value,
            'hardiness_zone_min' => 5,
            'hardiness_zone_max' => 9,
            'soil_types' => ['loamy', 'clay'],
            'soil_ph_min' => 6.0,
            'soil_ph_max' => 7.0,
            'soil_moisture' => SoilMoisture::Normal->value,
            'bloom_start_month' => 6,
            'bloom_end_month' => 7,
            'bloom_colors' => ['rosa', 'weiß'],
            'fragrant' => true,
            'watering_frequency' => WateringFrequency::Medium->value,
            'fertilizing_frequency' => FertilizingFrequency::Medium->value,
            'pruning_required' => true,
            'maintenance_level' => MaintenanceLevel::Medium->value,
            'allergy_potential' => AllergyPotential::Low->value,
            'attracts_bees' => true,
            'propagation_methods' => ['Steckling', 'Veredelung'],
            'status' => PlantStatus::Approved->value,
        ]);

        Plant::query()->updateOrCreate(['slug' => 'solanum-lycopersicum'], [
            'scientific_name' => 'Solanum lycopersicum',
            'family_id' => $solanaceae->id,
            'genus_id' => $solanum->id,
            'life_cycle' => LifeCycle::Annual->value,
            'native_regions' => ['Südamerika'],
            'height_min_cm' => 60,
            'height_max_cm' => 200,
            'width_min_cm' => 40,
            'width_max_cm' => 80,
            'growth_rate' => GrowthRate::Fast->value,
            'root_depth' => RootDepth::Medium->value,
            'sun_requirement' => SunRequirement::FullSun->value,
            'hardiness_zone_min' => 9,
            'hardiness_zone_max' => 11,
            'suitable_for_pot' => true,
            'soil_types' => ['loamy', 'sandy'],
            'soil_ph_min' => 6.0,
            'soil_ph_max' => 6.8,
            'soil_moisture' => SoilMoisture::Moist->value,
            'bloom_start_month' => 5,
            'bloom_end_month' => 9,
            'bloom_colors' => ['gelb'],
            'fruit_season_start' => 7,
            'fruit_season_end' => 10,
            'edible_parts' => ['Frucht'],
            'harvest_notes' => 'Ernte wenn Früchte vollständig rot und leicht weich sind.',
            'watering_frequency' => WateringFrequency::High->value,
            'fertilizing_frequency' => FertilizingFrequency::High->value,
            'pruning_required' => true,
            'maintenance_level' => MaintenanceLevel::High->value,
            'toxic_to_pets' => true,
            'allergy_potential' => AllergyPotential::Low->value,
            'propagation_methods' => ['Samen'],
            'status' => PlantStatus::Approved->value,
        ]);

        Plant::query()->updateOrCreate(['slug' => 'lavandula-angustifolia'], [
            'scientific_name' => 'Lavandula angustifolia',
            'family_id' => $lamiaceae->id,
            'genus_id' => $lavandula->id,
            'life_cycle' => LifeCycle::Perennial->value,
            'native_regions' => ['Mittelmeerraum'],
            'height_min_cm' => 30,
            'height_max_cm' => 90,
            'width_min_cm' => 30,
            'width_max_cm' => 90,
            'growth_rate' => GrowthRate::Medium->value,
            'root_depth' => RootDepth::Deep->value,
            'sun_requirement' => SunRequirement::FullSun->value,
            'hardiness_zone_min' => 5,
            'hardiness_zone_max' => 9,
            'suitable_for_pot' => true,
            'soil_types' => ['sandy', 'chalky'],
            'soil_ph_min' => 6.5,
            'soil_ph_max' => 7.8,
            'soil_moisture' => SoilMoisture::Dry->value,
            'bloom_start_month' => 6,
            'bloom_end_month' => 8,
            'bloom_colors' => ['violett', 'blau'],
            'fragrant' => true,
            'watering_frequency' => WateringFrequency::Low->value,
            'fertilizing_frequency' => FertilizingFrequency::Low->value,
            'pruning_required' => true,
            'maintenance_level' => MaintenanceLevel::Low->value,
            'allergy_potential' => AllergyPotential::Low->value,
            'attracts_bees' => true,
            'attracts_butterflies' => true,
            'deer_resistant' => true,
            'propagation_methods' => ['Steckling', 'Samen'],
            'status' => PlantStatus::Approved->value,
        ]);
    }
}
