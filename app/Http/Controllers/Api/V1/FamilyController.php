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
    /**
     * List plant families.
     *
     * Returns all botanical families in alphabetical order, with localized names when available.
     */
    public function index(ListFamilies $action): JsonResponse
    {
        return $this->success(FamilyResource::collection($action()));
    }

    /**
     * Show a family.
     *
     * Returns a single family identified by its slug, including translations and member genera count.
     */
    public function show(string $slug, ShowFamily $action): JsonResponse
    {
        return $this->success(new FamilyResource($action($slug)));
    }
}
