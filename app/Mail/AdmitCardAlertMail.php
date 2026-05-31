<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AdmitCardAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ?string $name,
        public Collection $jobs,
        public string $subjectStr,
        public string $tracking_token
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectStr,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admit_card_alert',
            with: [
                'name' => $this->name,
                'jobs' => $this->jobs,
                'subject' => $this->subjectStr,
                'tracking_token' => $this->tracking_token,
                'unsubscribe_url' => url('/unsubscribe?email=' . urlencode($this->to[0]['address'] ?? '')),
            ]
        );
    }
}
