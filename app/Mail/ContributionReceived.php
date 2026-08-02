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

final class ContributionReceived extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Contribution $contribution) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your contribution',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contributions.received',
            with: [
                'contribution' => $this->contribution,
            ],
        );
    }
}
