<?php

declare(strict_types=1);

namespace App\Services\Import\Mappers;

use App\DTOs\Import\PlantImportData;

final class GbifPlantMapper
{
    /**
     * @param  array<string, mixed>  $species
     * @param  array<int, array<string, mixed>>  $vernacularNames
     */
    public function __invoke(array $species, array $vernacularNames = []): ?PlantImportData
    {
        $scientificName = $this->string($species, 'canonicalName')
            ?? $this->string($species, 'scientificName');
        $familyName = $this->string($species, 'family');
        $genusName = $this->string($species, 'genus');

        if ($scientificName === null || $familyName === null || $genusName === null) {
            return null;
        }

        $rawKey = $species['nubKey'] ?? $species['key'] ?? null;
        $taxonKey = is_int($rawKey) || is_string($rawKey) ? (string) $rawKey : null;

        return new PlantImportData(
            scientificName: $scientificName,
            familyName: $familyName,
            genusName: $genusName,
            commonNames: $this->pickCommonNames($vernacularNames),
            sourceKey: $taxonKey,
            sourceUrl: $taxonKey !== null ? 'https://www.gbif.org/species/'.$taxonKey : null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function string(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if (! is_string($value)) {
            return null;
        }

        $trimmed = mb_trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Pick one common name per preferred locale (de, en).
     *
     * @param  array<int, array<string, mixed>>  $vernacularNames
     * @return array<string, string>|null
     */
    private function pickCommonNames(array $vernacularNames): ?array
    {
        $localeMap = ['de' => 'de', 'deu' => 'de', 'ger' => 'de', 'en' => 'en', 'eng' => 'en'];
        $picked = [];

        foreach ($vernacularNames as $entry) {
            $language = is_string($entry['language'] ?? null) ? mb_strtolower($entry['language']) : null;
            $name = is_string($entry['vernacularName'] ?? null) ? mb_trim($entry['vernacularName']) : '';
            if ($language === null) {
                continue;
            }

            if ($name === '') {
                continue;
            }

            if (! isset($localeMap[$language])) {
                continue;
            }

            $locale = $localeMap[$language];
            $picked[$locale] ??= $name;
        }

        return $picked === [] ? null : $picked;
    }
}
