<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FamilyTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $family_id
 * @property string $locale
 * @property string|null $common_name
 * @property string|null $description
 */
#[Fillable(['family_id', 'locale', 'common_name', 'description'])]
final class FamilyTranslation extends Model
{
    /** @use HasFactory<FamilyTranslationFactory> */
    use HasFactory;

    /** @return BelongsTo<Family, $this> */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
