<?php

declare(strict_types=1);

namespace App\Actions\Contributions;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Enums\PlantStatus;
use App\Mail\ContributionApproved;
use App\Models\Contribution;
use App\Models\Plant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use LogicException;

final class ApproveContribution
{
    public function __invoke(Contribution $contribution, User $reviewer): Contribution
    {
        DB::transaction(function () use ($contribution, $reviewer): void {
            $plant = match ($contribution->type) {
                ContributionType::NewPlant => $this->createPlantFromPayload($contribution, $reviewer),
                ContributionType::Update, ContributionType::Correction => $this->applyUpdateToPlant($contribution),
                ContributionType::Image => throw new LogicException('Image contributions cannot be approved via this workflow.'),
            };

            $contribution->forceFill([
                'status' => ContributionStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'plant_id' => $plant->id,
            ])->save();
        });

        if ($contribution->submitter !== null) {
            Mail::to($contribution->submitter->email)->queue(new ContributionApproved($contribution));
        }

        return $contribution;
    }

    private function createPlantFromPayload(Contribution $contribution, User $reviewer): Plant
    {
        $payload = $contribution->payload;
        $scientificName = isset($payload['scientific_name']) && is_string($payload['scientific_name'])
            ? $payload['scientific_name']
            : '';
        $slug = isset($payload['slug']) && is_string($payload['slug'])
            ? $payload['slug']
            : Str::slug($scientificName);

        return Plant::query()->create($payload + [
            'slug' => $slug,
            'status' => PlantStatus::Approved,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    private function applyUpdateToPlant(Contribution $contribution): Plant
    {
        $plant = $contribution->plant;

        throw_unless($plant instanceof Plant, LogicException::class, 'Contribution has no associated plant.');

        $plant->update($contribution->payload);

        return $plant;
    }
}
