<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Import\ImportPlantFromData;
use App\DTOs\Import\PlantImportData;
use App\Enums\ImportOutcome;
use App\Services\Import\GbifClient;
use App\Services\Import\Mappers\GbifPlantMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;
use Throwable;

final class ImportGbifCommand extends Command
{
    protected $signature = 'import:gbif {--limit=500 : Total number of plant records to import} {--offset=0 : GBIF search offset to start at} {--chunk=100 : Records per GBIF API call}';

    protected $description = 'Import plants from the GBIF Species API as pending_review entries';

    public function handle(GbifClient $client, GbifPlantMapper $mapper, ImportPlantFromData $importPlant): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $offset = max(0, (int) $this->option('offset'));
        $chunk = min(1000, max(1, (int) $this->option('chunk')));

        $counts = [
            ImportOutcome::Imported->value => 0,
            ImportOutcome::SkippedDuplicate->value => 0,
            ImportOutcome::SkippedIncomplete->value => 0,
            ImportOutcome::Failed->value => 0,
        ];
        /** @var array<int, array{scientific_name: string|null, reason: string|null}> $failures */
        $failures = [];

        $processed = 0;
        $bar = $this->output->createProgressBar($limit);
        $bar->start();

        while ($processed < $limit) {
            $requestSize = min($chunk, $limit - $processed);

            try {
                $payload = $client->searchPlants($requestSize, $offset + $processed);
            } catch (Throwable $e) {
                $this->newLine();
                $this->error(sprintf('GBIF request failed at offset %d: %s', $offset, $e->getMessage()));

                return self::FAILURE;
            }

            $results = $payload['results'] ?? [];
            if (! is_array($results) || $results === []) {
                break;
            }

            foreach ($results as $species) {
                if ($processed >= $limit) {
                    break;
                }

                if (! is_array($species)) {
                    continue;
                }

                /** @var array<string, mixed> $species */
                $data = $mapper($species);
                if (! $data instanceof PlantImportData) {
                    $counts[ImportOutcome::SkippedIncomplete->value]++;
                    $processed++;
                    $bar->advance();

                    continue;
                }

                $result = $importPlant($data);
                $counts[$result->outcome->value]++;
                if ($result->outcome === ImportOutcome::Failed) {
                    $failures[] = ['scientific_name' => $result->scientificName, 'reason' => $result->reason];
                }

                $processed++;
                $bar->advance();
            }

            if (($payload['endOfRecords'] ?? false) === true) {
                break;
            }

            Sleep::usleep(100_000);
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Outcome', 'Count'], [
            ['Imported', $counts[ImportOutcome::Imported->value]],
            ['Skipped (duplicate)', $counts[ImportOutcome::SkippedDuplicate->value]],
            ['Skipped (incomplete)', $counts[ImportOutcome::SkippedIncomplete->value]],
            ['Failed', $counts[ImportOutcome::Failed->value]],
            ['Processed total', $processed],
        ]);

        if ($failures !== []) {
            $this->warn('Failures:');
            $this->table(
                ['Scientific name', 'Reason'],
                array_map(static fn (array $f): array => [$f['scientific_name'] ?? '—', $f['reason'] ?? '—'], $failures),
            );
        }

        return self::SUCCESS;
    }
}
