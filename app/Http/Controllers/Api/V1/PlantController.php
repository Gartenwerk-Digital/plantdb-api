<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ListPlants;
use App\Actions\ListPlantSitemap;
use App\Actions\ShowPlant;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\PlantResource;
use App\Http\Resources\Api\V1\SitemapPlantResource;
use Illuminate\Http\JsonResponse;

final class PlantController extends ApiController
{
    /**
     * List plants.
     *
     * Paginated list of published plants. Localized via `?locale=` or `Accept-Language`.
     */
    public function index(ListPlants $action): JsonResponse
    {
        return $this->success(PlantResource::collection($action()));
    }

    /**
     * Show a plant.
     *
     * Returns a single published plant identified by its slug, including taxonomy, translations and media.
     */
    public function show(string $slug, ShowPlant $action): JsonResponse
    {
        return $this->success(new PlantResource($action($slug)));
    }

    /**
     * Plant sitemap.
     *
     * Lightweight list of every approved plant (slug, updated_at, available locales) for static site
     * generators and search engines. Public, unthrottled, cached server-side for 5 minutes.
     */
    public function sitemap(ListPlantSitemap $action): JsonResponse
    {
        return $this->success(SitemapPlantResource::collection($action()));
    }
}
