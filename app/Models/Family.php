<?php

declare(strict_types=1);

namespace App\Models;

use App\Policies\FamilyPolicy;
use Database\Factories\FamilyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 */
#[Fillable(['name', 'slug', 'description'])]
#[UsePolicy(FamilyPolicy::class)]
final class Family extends Model
{
    /** @use HasFactory<FamilyFactory> */
    use HasFactory;

    use HasUuids;

    /** @return HasMany<Genus, $this> */
    public function genera(): HasMany
    {
        return $this->hasMany(Genus::class);
    }

    /** @return HasMany<Plant, $this> */
    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }

    /** @return HasMany<FamilyTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(FamilyTranslation::class);
    }

    public function localizedTranslation(?string $locale = null): ?FamilyTranslation
    {
        $active = $locale ?? App::getLocale();
        /** @var string $fallback */
        $fallback = config('i18n.fallback');

        $collection = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->whereIn('locale', [$active, $fallback])->get();

        return $collection->firstWhere('locale', $active)
            ?? $collection->firstWhere('locale', $fallback);
    }
}
