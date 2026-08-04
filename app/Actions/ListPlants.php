<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Plant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListPlants
{
    /** @return LengthAwarePaginator<int, Plant> */
    public function __invoke(): LengthAwarePaginator
    {
        /** @var string $fallback */
        $fallback = config('i18n.fallback');

        $base = Plant::query()
            ->where('status', PlantStatus::Approved->value)
            ->with(['translations' => fn (Relation $q): Relation => $q->whereIn('locale', [
                App::getLocale(),
                $fallback,
            ])]);

        $perPage = min(max(request()->integer('per_page', 20), 1), 50);

        /** @var LengthAwarePaginator<int, Plant> $paginator */
        $paginator = QueryBuilder::for($base)
            ->allowedFilters(
                AllowedFilter::exact('life_cycle'),
                AllowedFilter::exact('sun_requirement'),
                AllowedFilter::exact('soil_moisture'),
                AllowedFilter::exact('toxic_to_pets'),
                AllowedFilter::callback('q', function (Builder $query, mixed $value): Builder {
                    $term = is_scalar($value) ? (string) $value : '';

                    return $query->whereRaw(
                        "search_vector @@ plainto_tsquery('simple', ?)",
                        [$term],
                    );
                }),
                AllowedFilter::callback('bloom_month', function (Builder $query, mixed $value): Builder {
                    $month = is_scalar($value) ? (int) $value : 0;

                    return $query
                        ->where('bloom_start_month', '<=', $month)
                        ->where('bloom_end_month', '>=', $month);
                }),
                AllowedFilter::callback('edible', function (Builder $query, mixed $value): Builder {
                    $truthy = filter_var($value, FILTER_VALIDATE_BOOL);

                    if ($truthy) {
                        return $query
                            ->whereNotNull('edible_parts')
                            ->whereRaw("edible_parts::text <> '[]'");
                    }

                    return $query->where(function (Builder $inner): void {
                        $inner->whereNull('edible_parts')
                            ->orWhereRaw("edible_parts::text = '[]'");
                    });
                }),
            )
            ->allowedSorts('scientific_name', 'created_at')
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends(request()->query());

        return $paginator;
    }
}
