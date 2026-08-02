<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Contributions\CreateContribution;
use App\Enums\ContributionType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreContributionRequest;
use App\Http\Resources\Api\V1\ContributionResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class ContributionController extends ApiController
{
    /**
     * Submit a new community contribution (new plant, update, or correction).
     */
    public function store(StoreContributionRequest $request, CreateContribution $createContribution): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $type = ContributionType::from($request->type);

        $contribution = $createContribution(
            $user,
            $type,
            $request->plant_id,
            $request->payload,
        );

        return $this->created(
            new ContributionResource($contribution),
            'Contribution received. You will be notified once it has been reviewed.',
        );
    }
}
