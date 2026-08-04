<?php

declare(strict_types=1);

namespace App\Actions\Import;

use App\DTOs\Import\ImportResult;
use App\DTOs\Import\PlantImportData;
use App\Enums\PlantImportSource;
use App\Enums\PlantStatus;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class ImportPlantFromData
{
    public function __invoke(PlantImportData $data, PlantImportSource $source): ImportResult
    {
        if (Plant::query()->where('scientific_name', $data->scientificName)->exists()) {
            return ImportResult::skippedDuplicate($data->scientificName);
        }

        try {
            $plant = DB::transaction(function () use ($data, $source): Plant {
                $family = Family::query()->firstOrCreate(
                    ['slug' => Str::slug($data->familyName)],
                    ['name' => $data->familyName],
                );

                $genus = Genus::query()->firstOrCreate(
                    ['slug' => Str::slug($data->genusName)],
                    ['name' => $data->genusName, 'family_id' => $family->id],
                );

                $plant = Plant::query()->create([
                    'slug' => $this->uniqueSlug($data->scientificName),
                    'scientific_name' => $data->scientificName,
                    'family_id' => $family->id,
                    'genus_id' => $genus->id,
                    'life_cycle' => $data->lifeCycle?->value,
                    'native_regions' => $data->nativeRegions,
                    'status' => PlantStatus::PendingReview->value,
                    'import_source' => $source->value,
                    'source_key' => $data->sourceKey,
                ]);

                foreach ($data->commonNames ?? [] as $locale => $commonName) {
                    PlantTranslation::query()->create([
                        'plant_id' => $plant->id,
                        'locale' => $locale,
                        'common_name' => $commonName,
                    ]);
                }

                return $plant;
            });
        } catch (Throwable $throwable) {
            return ImportResult::failed($throwable->getMessage(), $data->scientificName);
        }

        return ImportResult::imported($plant);
    }

    private function uniqueSlug(string $scientificName): string
    {
        $base = Str::slug($scientificName);
        $slug = $base;
        $suffix = 2;

        while (Plant::query()->where('slug', $slug)->exists()) {
            $slug = sprintf('%s-%d', $base, $suffix);
            $suffix++;
        }

        return $slug;
    }
}
