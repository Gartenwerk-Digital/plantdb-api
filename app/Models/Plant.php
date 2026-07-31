<?php

declare(strict_types=1);

namespace App\Models;

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
use Database\Factories\PlantFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string $id
 * @property string $slug
 * @property string $scientific_name
 * @property string $family_id
 * @property string $genus_id
 * @property string|null $cultivar
 * @property PlantStatus $status
 */
#[Guarded(['id'])]
final class Plant extends Model implements HasMedia
{
    /** @use HasFactory<PlantFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;

    /** @return BelongsTo<Family, $this> */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /** @return BelongsTo<Genus, $this> */
    public function genus(): BelongsTo
    {
        return $this->belongsTo(Genus::class);
    }

    /** @return HasMany<PlantTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(PlantTranslation::class);
    }

    /** @return HasMany<PlantImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(PlantImage::class);
    }

    /** @return HasMany<PlantCareTask, $this> */
    public function careTasks(): HasMany
    {
        return $this->hasMany(PlantCareTask::class);
    }

    /** @return HasMany<PlantPestDisease, $this> */
    public function pestsDiseases(): HasMany
    {
        return $this->hasMany(PlantPestDisease::class);
    }

    /** @return BelongsToMany<Plant, $this> */
    public function companions(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'plant_companions', 'plant_id', 'companion_id')
            ->withPivot(['relationship', 'notes'])
            ->withTimestamps();
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PlantStatus::class,
            'life_cycle' => LifeCycle::class,
            'growth_rate' => GrowthRate::class,
            'root_depth' => RootDepth::class,
            'sun_requirement' => SunRequirement::class,
            'soil_moisture' => SoilMoisture::class,
            'watering_frequency' => WateringFrequency::class,
            'fertilizing_frequency' => FertilizingFrequency::class,
            'maintenance_level' => MaintenanceLevel::class,
            'allergy_potential' => AllergyPotential::class,
            'native_regions' => 'array',
            'soil_types' => 'array',
            'bloom_colors' => 'array',
            'edible_parts' => 'array',
            'propagation_methods' => 'array',
            'deciduous' => 'boolean',
            'suitable_for_pot' => 'boolean',
            'fragrant' => 'boolean',
            'pruning_required' => 'boolean',
            'toxic_to_humans' => 'boolean',
            'toxic_to_pets' => 'boolean',
            'toxic_to_livestock' => 'boolean',
            'invasive' => 'boolean',
            'attracts_bees' => 'boolean',
            'attracts_butterflies' => 'boolean',
            'deer_resistant' => 'boolean',
            'soil_ph_min' => 'float',
            'soil_ph_max' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }
}
