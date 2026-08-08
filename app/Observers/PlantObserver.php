<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Plant;
use Illuminate\Support\Facades\Cache;

final class PlantObserver
{
    public function saved(Plant $plant): void
    {
        $this->forgetCaches($plant);
    }

    public function deleted(Plant $plant): void
    {
        $this->forgetCaches($plant);
    }

    private function forgetCaches(Plant $plant): void
    {
        /** @var list<string> $locales */
        $locales = config('i18n.supported', []);

        $originalSlug = $plant->getOriginal('slug');
        $slugs = array_filter(array_unique([
            $plant->slug,
            is_string($originalSlug) ? $originalSlug : null,
        ]));

        foreach ($slugs as $slug) {
            foreach ($locales as $locale) {
                Cache::forget(sprintf('site.plant.%s.%s', $slug, $locale));
            }
        }

        Cache::forget('site.featured_plants');
        Cache::forget('site.approved_plant_count');
    }
}
