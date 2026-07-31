<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GenusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $family_id
 * @property string $name
 * @property string $slug
 */
#[Fillable(['family_id', 'name', 'slug'])]
#[Table(name: 'genera')]
final class Genus extends Model
{
    /** @use HasFactory<GenusFactory> */
    use HasFactory;

    use HasUuids;

    /** @return BelongsTo<Family, $this> */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /** @return HasMany<Plant, $this> */
    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }
}
