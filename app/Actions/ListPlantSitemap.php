<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlantStatus;
use App\Models\Plant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class ListPlantSitemap
{
    private const string CACHE_KEY = 'api:v1:plants:sitemap';

    private const int CACHE_TTL_SECONDS = 300;

    /** @return Collection<int, Plant> */
    public function __invoke(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): Collection => Plant::query()
                ->where('status', PlantStatus::Approved->value)
                ->select(['id', 'slug', 'updated_at'])
                ->with(['translations:id,plant_id,locale'])
                ->orderBy('slug')
                ->get(),
        );
    }
}
