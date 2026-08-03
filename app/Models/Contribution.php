<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use Database\Factories\ContributionFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string $id
 * @property string|null $plant_id
 * @property ContributionType $type
 * @property int|null $submitted_by
 * @property array<string, mixed> $payload
 * @property ContributionStatus $status
 * @property int|null $reviewed_by
 * @property string|null $review_notes
 */
#[Guarded(['id'])]
final class Contribution extends Model implements HasMedia
{
    /** @use HasFactory<ContributionFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithMedia;

    public const string MEDIA_PENDING_IMAGE = 'pending_image';

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

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_PENDING_IMAGE)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => ContributionType::class,
            'status' => ContributionStatus::class,
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }
}
