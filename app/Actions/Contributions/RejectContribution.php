<?php

declare(strict_types=1);

namespace App\Actions\Contributions;

use App\Enums\ContributionStatus;
use App\Mail\ContributionRejected;
use App\Models\Contribution;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class RejectContribution
{
    public function __invoke(Contribution $contribution, User $reviewer, string $reviewNotes): Contribution
    {
        $contribution->forceFill([
            'status' => ContributionStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $reviewNotes,
        ])->save();

        if ($contribution->submitter !== null) {
            Mail::to($contribution->submitter->email)->queue(new ContributionRejected($contribution));
        }

        return $contribution;
    }
}
