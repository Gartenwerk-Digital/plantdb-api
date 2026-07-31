<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PestDiseaseType;
use Database\Factories\PlantPestDiseaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $plant_id
 * @property string $name
 * @property PestDiseaseType $type
 * @property string|null $treatment_notes
 */
#[Fillable(['plant_id', 'name', 'type', 'treatment_notes'])]
#[Table(name: 'plant_pests_diseases')]
final class PlantPestDisease extends Model
{
    /** @use HasFactory<PlantPestDiseaseFactory> */
    use HasFactory;

    /** @return BelongsTo<Plant, $this> */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => PestDiseaseType::class,
        ];
    }
}
