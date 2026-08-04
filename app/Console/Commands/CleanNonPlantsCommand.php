<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Family;
use App\Models\Genus;
use App\Models\Plant;
use Illuminate\Console\Command;

final class CleanNonPlantsCommand extends Command
{
    private const array DEFAULT_KEEP_SLUGS = [
        'rosa-centifolia',
        'solanum-lycopersicum',
        'lavandula-angustifolia',
    ];

    protected $signature = 'plants:cleanup {--dry-run : Report what would be deleted without changing data} {--keep=* : Additional plant slugs to preserve}';

    protected $description = 'Delete all plants except the curated whitelist, then remove orphaned families and genera';

    public function handle(): int
    {
        $keep = array_values(array_unique(array_merge(
            self::DEFAULT_KEEP_SLUGS,
            array_map(strval(...), (array) $this->option('keep')),
        )));

        $plantsToDelete = Plant::query()->whereNotIn('slug', $keep)->count();
        $totalPlants = Plant::query()->count();

        if ($this->option('dry-run')) {
            $this->line(sprintf('Would delete %d of %d plants (keeping %d curated slug(s))', $plantsToDelete, $totalPlants, count($keep)));

            $familyIds = Plant::query()->whereNotIn('slug', $keep)->pluck('family_id')->unique()->all();
            $genusIds = Plant::query()->whereNotIn('slug', $keep)->pluck('genus_id')->unique()->all();

            $orphanFamilies = Family::query()->whereIn('id', $familyIds)
                ->whereDoesntHave('plants', fn ($q) => $q->whereIn('slug', $keep))
                ->count();
            $orphanGenera = Genus::query()->whereIn('id', $genusIds)
                ->whereDoesntHave('plants', fn ($q) => $q->whereIn('slug', $keep))
                ->count();

            $this->line(sprintf('Would delete %d orphan families and %d orphan genera afterwards', $orphanFamilies, $orphanGenera));

            return self::SUCCESS;
        }

        $deletedPlants = $plantsToDelete;
        Plant::query()->whereNotIn('slug', $keep)->delete();

        $deletedGenera = Genus::query()->doesntHave('plants')->count();
        Genus::query()->doesntHave('plants')->delete();

        $deletedFamilies = Family::query()->doesntHave('plants')->count();
        Family::query()->doesntHave('plants')->delete();

        $this->info(sprintf(
            'Deleted %d plants, %d genera, %d families. Remaining plants: %d.',
            $deletedPlants,
            $deletedGenera,
            $deletedFamilies,
            Plant::query()->count(),
        ));

        return self::SUCCESS;
    }
}
