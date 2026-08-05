<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ListPlants;
use App\Actions\ShowPlant;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\PlantResource;
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
}
