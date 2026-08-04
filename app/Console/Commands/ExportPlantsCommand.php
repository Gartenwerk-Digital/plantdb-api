<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use App\Models\PlantTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ExportPlantsCommand extends Command
{
    protected $signature = 'plants:export {--output=database/seeders/data : Output directory (relative to base_path or absolute)}';

    protected $description = 'Export plants, families, genera and translations to JSON for seeding fresh environments';

    public function handle(): int
    {
        $outputOption = (string) $this->option('output');
        $directory = str_starts_with($outputOption, DIRECTORY_SEPARATOR)
            ? $outputOption
            : base_path($outputOption);

        File::ensureDirectoryExists($directory);

        $families = Family::query()
            ->orderBy('slug')
            ->get()
            ->map(fn (Family $family): array => [
                'slug' => $family->slug,
                'name' => $family->name,
                'description' => $family->description,
            ])
            ->all();

        /** @var array<string, string> $familySlugById */
        $familySlugById = Family::query()->pluck('slug', 'id')->all();

        $genera = Genus::query()
            ->orderBy('slug')
            ->get()
            ->map(fn (Genus $genus): array => [
                'slug' => $genus->slug,
                'name' => $genus->name,
                'family_slug' => $familySlugById[$genus->family_id] ?? '',
            ])
            ->all();

        /** @var array<string, string> $genusSlugById */
        $genusSlugById = Genus::query()->pluck('slug', 'id')->all();

        $plants = Plant::query()
            ->with(['translations'])
            ->orderBy('scientific_name')
            ->get()
            ->map(fn (Plant $plant): array => $this->serializePlant(
                $plant,
                $familySlugById[$plant->family_id] ?? '',
                $genusSlugById[$plant->genus_id] ?? '',
            ))
            ->all();

        $this->write($directory.'/families.json', $families);
        $this->write($directory.'/genera.json', $genera);
        $this->write($directory.'/plants.json', $plants);

        $this->info(sprintf('Exported %d families, %d genera, %d plants to %s', count($families), count($genera), count($plants), $directory));

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePlant(Plant $plant, string $familySlug, string $genusSlug): array
    {
        $attributes = $plant->getAttributes();

        $exclude = ['id', 'family_id', 'genus_id', 'created_at', 'updated_at', 'submitted_by', 'reviewed_by', 'reviewed_at', 'review_notes', 'search_vector'];
        foreach ($exclude as $key) {
            unset($attributes[$key]);
        }

        foreach (['native_regions', 'soil_types', 'bloom_colors', 'edible_parts', 'propagation_methods'] as $jsonField) {
            if (isset($attributes[$jsonField]) && is_string($attributes[$jsonField])) {
                $decoded = json_decode($attributes[$jsonField], true);
                $attributes[$jsonField] = is_array($decoded) ? $decoded : null;
            }
        }

        foreach (['deciduous', 'suitable_for_pot', 'fragrant', 'pruning_required', 'toxic_to_humans', 'toxic_to_pets', 'toxic_to_livestock', 'invasive', 'attracts_bees', 'attracts_butterflies', 'deer_resistant'] as $bool) {
            if (isset($attributes[$bool])) {
                $attributes[$bool] = (bool) $attributes[$bool];
            }
        }

        foreach (['soil_ph_min', 'soil_ph_max'] as $decimal) {
            $raw = $attributes[$decimal] ?? null;
            if (is_int($raw) || is_float($raw) || is_string($raw)) {
                $attributes[$decimal] = (float) $raw;
            }
        }

        $attributes['family_slug'] = $familySlug;
        $attributes['genus_slug'] = $genusSlug;

        $translations = $plant->translations
            ->map(fn (PlantTranslation $t): array => [
                'locale' => $t->locale,
                'common_name' => $t->common_name,
                'description' => $t->description,
            ])
            ->sortBy('locale')
            ->values()
            ->all();

        $attributes['translations'] = $translations;

        ksort($attributes);

        return $attributes;
    }

    /**
     * @param  array<int, mixed>  $data
     */
    private function write(string $path, array $data): void
    {
        File::put(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL,
        );
    }
}
