<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\ListFamilies;
use App\Actions\ShowFamily;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\FamilyResource;
use Illuminate\Http\JsonResponse;

final class FamilyController extends ApiController
{
    public function index(ListFamilies $action): JsonResponse
    {
        return $this->success(FamilyResource::collection($action()));
    }

    public function show(string $slug, ShowFamily $action): JsonResponse
    {
        return $this->success(new FamilyResource($action($slug)));
    }
}
