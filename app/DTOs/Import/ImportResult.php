<?php

declare(strict_types=1);

namespace App\DTOs\Import;

use App\Enums\ImportOutcome;
use App\Models\Plant;

final readonly class ImportResult
{
    public function __construct(
        public ImportOutcome $outcome,
        public ?Plant $plant = null,
        public ?string $reason = null,
        public ?string $scientificName = null,
    ) {}

    public static function imported(Plant $plant): self
    {
        return new self(ImportOutcome::Imported, $plant, scientificName: $plant->scientific_name);
    }

    public static function skippedDuplicate(string $scientificName): self
    {
        return new self(ImportOutcome::SkippedDuplicate, scientificName: $scientificName);
    }

    public static function skippedIncomplete(?string $reason = null): self
    {
        return new self(ImportOutcome::SkippedIncomplete, reason: $reason);
    }

    public static function failed(string $reason, ?string $scientificName = null): self
    {
        return new self(ImportOutcome::Failed, reason: $reason, scientificName: $scientificName);
    }
}
