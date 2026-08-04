<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Family;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListFamilies
{
    /** @return LengthAwarePaginator<int, Family> */
    public function __invoke(): LengthAwarePaginator
    {
        /** @var string $fallback */
        $fallback = config('i18n.fallback');

        /** @var LengthAwarePaginator<int, Family> $paginator */
        $paginator = QueryBuilder::for(Family::class)
            ->allowedFilters(AllowedFilter::partial('name'))
            ->allowedSorts('name', 'created_at')
            ->defaultSort('name')
            ->with(['translations' => fn (Relation $q): Relation => $q->whereIn('locale', [
                App::getLocale(),
                $fallback,
            ])])
            ->withCount(['plants' => fn (Builder $q): Builder => $q->where('status', PlantStatus::Approved->value)])
            ->paginate(20)
            ->appends(request()->query());

        return $paginator;
    }
}
