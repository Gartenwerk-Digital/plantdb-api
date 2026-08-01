<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Genus;
use Illuminate\Database\Eloquent\Builder;

final class ShowGenus
{
    public function __invoke(string $slug): Genus
    {
        /** @var Genus $genus */
        $genus = Genus::query()
            ->where('slug', $slug)
            ->withCount(['plants' => fn (Builder $q): Builder => $q->where('status', PlantStatus::Approved->value)])
            ->firstOrFail();

        return $genus;
    }
}
