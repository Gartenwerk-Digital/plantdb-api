<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Contributions\CreateContribution;
use App\Enums\ContributionType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\StoreContributionRequest;
use App\Http\Resources\Api\V1\ContributionResource;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ContributionController extends ApiController
{
    /**
     * List the authenticated user's own contributions (most recent first).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $contributions = Contribution::query()
            ->where('submitted_by', $user->id)
            ->latest()
            ->paginate(20);

        return ContributionResource::collection($contributions);
    }

    /**
     * Submit a new community contribution (new plant, update, correction, or image).
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
            $request->file('image'),
        );

        return $this->created(
            new ContributionResource($contribution),
            'Contribution received. You will be notified once it has been reviewed.',
        );
    }
}
