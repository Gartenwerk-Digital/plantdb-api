<?php

declare(strict_types=1);

namespace App\Http\Controllers\Site;

use App\Enums\PlantStatus;
use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Contracts\View\View;

final class SitePlantController extends Controller
{
    public function show(string $slug): View
    {
        Plant::query()
            ->where('slug', $slug)
            ->where('status', PlantStatus::Approved->value)
            ->firstOrFail();

        return view('site.plants.show');
    }
}
