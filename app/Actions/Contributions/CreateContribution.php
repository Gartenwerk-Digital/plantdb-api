<?php

declare(strict_types=1);

namespace App\Actions\Contributions;

use App\Enums\ContributionStatus;
use App\Enums\ContributionType;
use App\Mail\ContributionReceived;
use App\Mail\NewContributionSubmitted;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class CreateContribution
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __invoke(
        User $submitter,
        ContributionType $type,
        ?string $plantId,
        array $payload,
    ): Contribution {
        $contribution = new Contribution();
        $contribution->type = $type;
        $contribution->plant_id = $plantId;
        $contribution->submitted_by = $submitter->id;
        $contribution->payload = $payload;
        $contribution->status = ContributionStatus::Pending;
        $contribution->save();

        $configured = config('mail.admin_address');
        $adminAddress = is_string($configured) && $configured !== ''
            ? $configured
            : config('mail.from.address');

        if (is_string($adminAddress) && $adminAddress !== '') {
            Mail::to($adminAddress)->queue(new NewContributionSubmitted($contribution));
        }

        Mail::to($submitter->email)->queue(new ContributionReceived($contribution));

        return $contribution;
    }
}
