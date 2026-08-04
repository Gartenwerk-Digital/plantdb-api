<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Import\ImportPlantFromData;
use App\DTOs\Import\PlantImportData;
use App\Enums\ImportOutcome;
use App\Enums\PlantImportSource;
use App\Services\Import\GbifClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Sleep;
use RuntimeException;
use Throwable;

final class ImportCuratedPlantsCommand extends Command
{
    protected $signature = 'import:curated {--file=database/curated/garden-plants.json : Curated plant list (relative to base_path or absolute)}';

    protected $description = 'Import a curated list of garden plants, resolving taxonomy via GBIF species/match';

    public function handle(GbifClient $client, ImportPlantFromData $importPlant): int
    {
        $path = $this->resolvePath((string) $this->option('file'));

        $entries = $this->readCuratedList($path);
        $this->info(sprintf('Importing %d curated plants from %s', count($entries), $path));

        $counts = [
            ImportOutcome::Imported->value => 0,
            ImportOutcome::SkippedDuplicate->value => 0,
            ImportOutcome::SkippedIncomplete->value => 0,
            ImportOutcome::Failed->value => 0,
            'no_match' => 0,
        ];
        /** @var array<int, array{scientific_name: string, reason: string}> $failures */
        $failures = [];

        $bar = $this->output->createProgressBar(count($entries));
        $bar->start();

        foreach ($entries as $entry) {
            $scientificName = $entry['scientific_name'];

            try {
                $match = $client->matchSpecies($scientificName);
            } catch (Throwable $e) {
                $counts[ImportOutcome::Failed->value]++;
                $failures[] = ['scientific_name' => $scientificName, 'reason' => 'GBIF match request failed: '.$e->getMessage()];
                $bar->advance();

                continue;
            }

            if ($match === null) {
                $counts['no_match']++;
                $failures[] = ['scientific_name' => $scientificName, 'reason' => 'No GBIF backbone match'];
                $bar->advance();

                continue;
            }

            $family = $match['family'] ?? null;
            $genus = $match['genus'] ?? null;
            if (! is_string($family) || ! is_string($genus)) {
                $counts['no_match']++;
                $failures[] = ['scientific_name' => $scientificName, 'reason' => 'No GBIF backbone match with family/genus'];
                $bar->advance();

                continue;
            }

            $data = new PlantImportData(
                scientificName: is_string($match['canonicalName'] ?? null) ? $match['canonicalName'] : $scientificName,
                familyName: $family,
                genusName: $genus,
                commonNames: $this->commonNamesFromEntry($entry),
                sourceKey: $this->sourceKeyFromMatch($match),
                sourceUrl: $this->sourceUrlFromMatch($match),
            );

            $result = $importPlant($data, PlantImportSource::Gbif);
            $counts[$result->outcome->value]++;
            if ($result->outcome === ImportOutcome::Failed) {
                $failures[] = ['scientific_name' => $scientificName, 'reason' => $result->reason ?? 'unknown'];
            }

            Sleep::usleep(50_000);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Outcome', 'Count'], [
            ['Imported', $counts[ImportOutcome::Imported->value]],
            ['Skipped (duplicate)', $counts[ImportOutcome::SkippedDuplicate->value]],
            ['Skipped (incomplete)', $counts[ImportOutcome::SkippedIncomplete->value]],
            ['No GBIF match', $counts['no_match']],
            ['Failed', $counts[ImportOutcome::Failed->value]],
        ]);

        if ($failures !== []) {
            $this->warn('Details:');
            $this->table(
                ['Scientific name', 'Reason'],
                array_map(static fn (array $f): array => [$f['scientific_name'], $f['reason']], $failures),
            );
        }

        return self::SUCCESS;
    }

    private function resolvePath(string $option): string
    {
        return str_starts_with($option, DIRECTORY_SEPARATOR) ? $option : base_path($option);
    }

    /**
     * @return list<array{scientific_name: string, de: ?string, en: ?string, category: ?string}>
     */
    private function readCuratedList(string $path): array
    {
        throw_unless(File::exists($path), RuntimeException::class, 'Curated list not found: '.$path);

        $decoded = json_decode(File::get($path), true);
        throw_unless(is_array($decoded), RuntimeException::class, 'Curated list is not valid JSON: '.$path);

        $entries = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (! is_string($row['scientific_name'] ?? null)) {
                continue;
            }

            if ($row['scientific_name'] === '') {
                continue;
            }

            $entries[] = [
                'scientific_name' => mb_trim($row['scientific_name']),
                'de' => is_string($row['de'] ?? null) ? mb_trim($row['de']) : null,
                'en' => is_string($row['en'] ?? null) ? mb_trim($row['en']) : null,
                'category' => is_string($row['category'] ?? null) ? mb_trim($row['category']) : null,
            ];
        }

        return $entries;
    }

    /**
     * @param  array{scientific_name: string, de: ?string, en: ?string, category: ?string}  $entry
     * @return array<string, string>|null
     */
    private function commonNamesFromEntry(array $entry): ?array
    {
        $names = [];
        if ($entry['de'] !== null && $entry['de'] !== '') {
            $names['de'] = $entry['de'];
        }

        if ($entry['en'] !== null && $entry['en'] !== '') {
            $names['en'] = $entry['en'];
        }

        return $names === [] ? null : $names;
    }

    /**
     * @param  array<string, mixed>  $match
     */
    private function sourceKeyFromMatch(array $match): ?string
    {
        $key = $match['usageKey'] ?? $match['speciesKey'] ?? null;

        return is_int($key) || is_string($key) ? (string) $key : null;
    }

    /**
     * @param  array<string, mixed>  $match
     */
    private function sourceUrlFromMatch(array $match): ?string
    {
        $key = $this->sourceKeyFromMatch($match);

        return $key === null ? null : 'https://www.gbif.org/species/'.$key;
    }
}
