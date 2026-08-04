<?php

declare(strict_types=1);

namespace App\DTOs\Import;

use App\Enums\LifeCycle;
use Spatie\LaravelData\Data;

final class PlantImportData extends Data
{
    /**
     * @param  array<string, string>|null  $commonNames  locale => common name
     * @param  array<int, string>|null  $nativeRegions
     */
    public function __construct(
        public string $scientificName,
        public string $familyName,
        public string $genusName,
        public ?LifeCycle $lifeCycle = null,
        public ?array $nativeRegions = null,
        public ?array $commonNames = null,
        public ?string $sourceKey = null,
        public ?string $sourceUrl = null,
    ) {}
}
