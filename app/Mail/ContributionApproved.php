<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Contribution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContributionApproved extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Contribution $contribution) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your contribution has been approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contributions.approved',
            with: [
                'contribution' => $this->contribution,
            ],
        );
    }
}
