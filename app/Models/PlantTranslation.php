<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlantTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $plant_id
 * @property string $locale
 * @property string|null $common_name
 * @property string|null $description
 */
#[Fillable(['plant_id', 'locale', 'common_name', 'description'])]
final class PlantTranslation extends Model
{
    /** @use HasFactory<PlantTranslationFactory> */
    use HasFactory;

    /** @return BelongsTo<Plant, $this> */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }
}
