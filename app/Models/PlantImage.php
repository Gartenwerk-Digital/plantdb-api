<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlantImageType;
use Database\Factories\PlantImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $plant_id
 * @property string $url
 * @property PlantImageType $type
 * @property string|null $license
 * @property string|null $attribution
 * @property int|null $submitted_by
 */
#[Fillable([
    'plant_id',
    'url',
    'type',
    'license',
    'attribution',
    'submitted_by',
])]
final class PlantImage extends Model
{
    /** @use HasFactory<PlantImageFactory> */
    use HasFactory;

    /** @return BelongsTo<Plant, $this> */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => PlantImageType::class,
        ];
    }
}
