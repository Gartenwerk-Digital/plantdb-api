<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Family;
use Illuminate\Database\Eloquent\Builder;

final class ShowFamily
{
    public function __invoke(string $slug): Family
    {
        /** @var Family $family */
        $family = Family::query()
            ->where('slug', $slug)
            ->withCount(['plants' => fn (Builder $q): Builder => $q->where('status', PlantStatus::Approved->value)])
            ->firstOrFail();

        return $family;
    }
}
