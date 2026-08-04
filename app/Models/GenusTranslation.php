<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GenusTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $genus_id
 * @property string $locale
 * @property string|null $common_name
 * @property string|null $description
 */
#[Fillable(['genus_id', 'locale', 'common_name', 'description'])]
final class GenusTranslation extends Model
{
    /** @use HasFactory<GenusTranslationFactory> */
    use HasFactory;

    /** @return BelongsTo<Genus, $this> */
    public function genus(): BelongsTo
    {
        return $this->belongsTo(Genus::class);
    }
}
