<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ShowPlant;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\PlantResource;
use Illuminate\Http\JsonResponse;

final class PlantController extends ApiController
{
    public function show(string $slug, ShowPlant $action): JsonResponse
    {
        return $this->success(new PlantResource($action($slug)));
    }
}
