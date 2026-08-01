<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ListGenera;
use App\Actions\ShowGenus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\GenusResource;
use Illuminate\Http\JsonResponse;

final class GenusController extends ApiController
{
    public function index(ListGenera $action): JsonResponse
    {
        return $this->success(GenusResource::collection($action()));
    }

    public function show(string $slug, ShowGenus $action): JsonResponse
    {
        return $this->success(new GenusResource($action($slug)));
    }
}
