<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Genus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class ListGenera
{
    /** @return LengthAwarePaginator<int, Genus> */
    public function __invoke(): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Genus> $paginator */
        $paginator = QueryBuilder::for(Genus::class)
            ->allowedFilters(AllowedFilter::partial('name'))
            ->allowedSorts('name', 'created_at')
            ->defaultSort('name')
            ->withCount(['plants' => fn (Builder $q): Builder => $q->where('status', PlantStatus::Approved->value)])
            ->paginate(20)
            ->appends(request()->query());

        return $paginator;
    }
}
