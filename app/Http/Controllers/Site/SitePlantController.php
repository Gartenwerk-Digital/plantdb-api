<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\PlantStatus;
use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

final class SitePlantController extends Controller
{
    public function show(string $slug): View
    {
        $locale = App::getLocale();

        $plant = Cache::remember(
            sprintf('site.plant.%s.%s', $slug, $locale),
            300,
            fn () => Plant::query()
                ->with(['family', 'genus', 'translations', 'careTasks', 'media'])
                ->where('slug', $slug)
                ->where('status', PlantStatus::Approved)
                ->first(),
        );

        abort_unless($plant instanceof Plant, 404);

        return view('site.plants.show', [
            'plant' => $plant,
            'translation' => $plant->localizedTranslation($locale),
        ]);
    }
}
