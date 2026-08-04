<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

final class PlantSeeder extends Seeder
{
    private const string DATA_DIR = 'database/seeders/data';

    public function run(): void
    {
        $directory = base_path(self::DATA_DIR);

        $familiesFile = $directory.'/families.json';
        $generaFile = $directory.'/genera.json';
        $plantsFile = $directory.'/plants.json';

        if (! File::exists($familiesFile) || ! File::exists($generaFile) || ! File::exists($plantsFile)) {
            if ($this->command !== null) {
                $this->command->warn(sprintf(
                    'PlantSeeder: no JSON data found in %s. Run `php artisan import:gbif` then `php artisan plants:export` to populate.',
                    self::DATA_DIR,
                ));
            }

            return;
        }

        /** @var array<string, string> $familyIdBySlug */
        $familyIdBySlug = [];
        foreach ($this->readJson($familiesFile) as $row) {
            $slug = $this->requireString($row, 'slug', 'family');
            $family = Family::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $this->requireString($row, 'name', 'family'), 'description' => $this->optionalString($row, 'description')],
            );
            $familyIdBySlug[$slug] = (string) $family->id;
        }

        /** @var array<string, string> $genusIdBySlug */
        $genusIdBySlug = [];
        foreach ($this->readJson($generaFile) as $row) {
            $slug = $this->requireString($row, 'slug', 'genus');
            $familySlug = $this->requireString($row, 'family_slug', 'genus');
            $familyId = $familyIdBySlug[$familySlug]
                ?? throw new RuntimeException(sprintf("PlantSeeder: unknown family_slug '%s' for genus '%s'", $familySlug, $slug));

            $genus = Genus::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $this->requireString($row, 'name', 'genus'), 'family_id' => $familyId],
            );
            $genusIdBySlug[$slug] = (string) $genus->id;
        }

        foreach ($this->readJson($plantsFile) as $row) {
            $slug = $this->requireString($row, 'slug', 'plant');
            $familySlug = $this->requireString($row, 'family_slug', 'plant');
            $genusSlug = $this->requireString($row, 'genus_slug', 'plant');

            $translations = $row['translations'] ?? [];
            unset($row['family_slug'], $row['genus_slug'], $row['translations']);

            $row['family_id'] = $familyIdBySlug[$familySlug]
                ?? throw new RuntimeException(sprintf("PlantSeeder: unknown family_slug '%s' for plant '%s'", $familySlug, $slug));
            $row['genus_id'] = $genusIdBySlug[$genusSlug]
                ?? throw new RuntimeException(sprintf("PlantSeeder: unknown genus_slug '%s' for plant '%s'", $genusSlug, $slug));

            $plant = Plant::query()->updateOrCreate(['slug' => $slug], $row);

            if (! is_array($translations)) {
                continue;
            }

            foreach ($translations as $translation) {
                if (! is_array($translation)) {
                    continue;
                }

                /** @var array<string, mixed> $translation */
                $locale = $this->requireString($translation, 'locale', 'translation');
                PlantTranslation::query()->updateOrCreate(
                    ['plant_id' => $plant->id, 'locale' => $locale],
                    [
                        'common_name' => $this->optionalString($translation, 'common_name'),
                        'description' => $this->optionalString($translation, 'description'),
                    ],
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function requireString(array $row, string $key, string $context): string
    {
        $value = $row[$key] ?? null;
        if (! is_string($value) || $value === '') {
            throw new RuntimeException(sprintf("PlantSeeder: %s missing required string key '%s'", $context, $key));
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function optionalString(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readJson(string $path): array
    {
        $decoded = json_decode(File::get($path), true);

        throw_unless(is_array($decoded), RuntimeException::class, 'PlantSeeder: failed to decode JSON at '.$path);

        $rows = [];
        foreach ($decoded as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            /** @var array<string, mixed> $entry */
            $rows[] = $entry;
        }

        return $rows;
    }
}
