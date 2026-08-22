<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\PlantStatus;
use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

final class SitePageController extends Controller
{
    public function home(): View
    {
        $featuredPlants = Cache::remember('site.featured_plants', 300, fn () => Plant::with([
            'family',
            'translations' => fn (HasMany $q) => $q->where('locale', 'de'),
        ])
            ->where('status', PlantStatus::Approved)
            ->inRandomOrder()
            ->limit(3)
            ->get());

        $approvedPlantCount = Cache::remember(
            'site.approved_plant_count',
            300,
            fn (): int => Plant::query()->where('status', PlantStatus::Approved)->count(),
        );

        return view('site.home', ['featuredPlants' => $featuredPlants, 'approvedPlantCount' => $approvedPlantCount]);
    }

    public function developers(): View
    {
        return view('site.developers');
    }

    public function contribute(): View
    {
        return view('site.contribute');
    }

    public function about(): View
    {
        return view('site.about');
    }

    public function impressum(): View
    {
        return view('site.impressum');
    }

    public function datenschutz(): View
    {
        return view('site.datenschutz');
    }
}
