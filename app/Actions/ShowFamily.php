<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Family;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\App;

final class ShowFamily
{
    public function __invoke(string $slug): Family
    {
        /** @var string $fallback */
        $fallback = config('i18n.fallback');

        /** @var Family $family */
        $family = Family::query()
            ->where('slug', $slug)
            ->with(['translations' => fn (Relation $q): Relation => $q->whereIn('locale', [
                App::getLocale(),
                $fallback,
            ])])
            ->withCount(['plants' => fn (Builder $q): Builder => $q->where('status', PlantStatus::Approved->value)])
            ->firstOrFail();

        return $family;
    }
}
