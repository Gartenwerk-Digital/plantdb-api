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
    /**
     * List genera.
     *
     * Returns all botanical genera with their parent family, localized when available.
     */
    public function index(ListGenera $action): JsonResponse
    {
        return $this->success(GenusResource::collection($action()));
    }

    /**
     * Show a genus.
     *
     * Returns a single genus identified by its slug, including its family and translations.
     */
    public function show(string $slug, ShowGenus $action): JsonResponse
    {
        return $this->success(new GenusResource($action($slug)));
    }
}
