<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AllergyPotential;
use App\Enums\FertilizingFrequency;
use App\Enums\GrowthRate;
use App\Enums\LifeCycle;
use App\Enums\MaintenanceLevel;
use App\Enums\PlantImageType;
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
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string $id
 * @property string $slug
 * @property string $scientific_name
 * @property string $family_id
 * @property string $genus_id
 * @property string|null $cultivar
 * @property PlantStatus $status
 * @property LifeCycle|null $life_cycle
 * @property bool $deciduous
 * @property array<int, string>|null $native_regions
 * @property int|null $height_min_cm
 * @property int|null $height_max_cm
 * @property int|null $width_min_cm
 * @property int|null $width_max_cm
 * @property GrowthRate|null $growth_rate
 * @property RootDepth|null $root_depth
 * @property SunRequirement|null $sun_requirement
 * @property int|null $hardiness_zone_min
 * @property int|null $hardiness_zone_max
 * @property bool $suitable_for_pot
 * @property array<int, string>|null $soil_types
 * @property float|null $soil_ph_min
 * @property float|null $soil_ph_max
 * @property SoilMoisture|null $soil_moisture
 * @property int|null $bloom_start_month
 * @property int|null $bloom_end_month
 * @property array<int, string>|null $bloom_colors
 * @property bool $fragrant
 * @property int|null $fruit_season_start
 * @property int|null $fruit_season_end
 * @property array<int, string>|null $edible_parts
 * @property string|null $harvest_notes
 * @property WateringFrequency|null $watering_frequency
 * @property FertilizingFrequency|null $fertilizing_frequency
 * @property bool $pruning_required
 * @property MaintenanceLevel|null $maintenance_level
 * @property bool $toxic_to_humans
 * @property bool $toxic_to_pets
 * @property bool $toxic_to_livestock
 * @property bool $invasive
 * @property AllergyPotential|null $allergy_potential
 * @property bool $attracts_bees
 * @property bool $attracts_butterflies
 * @property bool $deer_resistant
 * @property array<int, string>|null $propagation_methods
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Pivot|null $pivot
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

    public function registerMediaCollections(): void
    {
        $mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

        foreach (PlantImageType::cases() as $type) {
            $this->addMediaCollection($type->value)
                ->acceptsMimeTypes($mimes);
        }
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('portrait')
            ->nonQueued()
            ->fit(Fit::Crop, 400, 600)
            ->format('webp')
            ->quality(85);

        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->fit(Fit::Crop, 200, 200)
            ->format('webp')
            ->quality(80);
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
