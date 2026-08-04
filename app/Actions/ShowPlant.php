<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

final class ShowPlant
{
    public function __invoke(string $slug): Plant
    {
        /** @var string $fallback */
        $fallback = config('i18n.fallback');

        $query = Plant::query()
            ->where('status', PlantStatus::Approved->value)
            ->where('slug', $slug)
            ->with(['translations' => fn (Relation $q): Relation => $q->whereIn('locale', [
                App::getLocale(),
                $fallback,
            ])]);

        /** @var Plant $plant */
        $plant = QueryBuilder::for($query)
            ->allowedIncludes(
                AllowedInclude::relationship('images', 'media'),
                AllowedInclude::relationship('companions'),
                AllowedInclude::relationship('care_tasks', 'careTasks'),
                AllowedInclude::relationship('translations'),
            )
            ->firstOrFail();

        return $plant;
    }
}
