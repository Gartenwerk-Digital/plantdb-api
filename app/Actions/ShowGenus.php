<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Genus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;

final class ShowGenus
{
    public function __invoke(string $slug): Genus
    {
        /** @var string $fallback */
        $fallback = config('i18n.fallback');

        /** @var Genus $genus */
        $genus = Genus::query()
            ->where('slug', $slug)
            ->with(['translations' => fn (Relation $q): Relation => $q->whereIn('locale', [
                App::getLocale(),
                $fallback,
            ])])
            ->withCount(['plants' => fn (Builder $q): Builder => $q->where('status', PlantStatus::Approved->value)])
            ->firstOrFail();

        return $genus;
    }
}
