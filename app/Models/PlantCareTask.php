<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CareTaskType;
use Database\Factories\PlantCareTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $plant_id
 * @property CareTaskType $task_type
 * @property int $month_start
 * @property int $month_end
 * @property string|null $frequency
 * @property string|null $notes
 */
#[Fillable([
    'plant_id',
    'task_type',
    'month_start',
    'month_end',
    'frequency',
    'notes',
])]
final class PlantCareTask extends Model
{
    /** @use HasFactory<PlantCareTaskFactory> */
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
            'task_type' => CareTaskType::class,
        ];
    }
}
