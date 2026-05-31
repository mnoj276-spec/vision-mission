<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ?string $name,
        public Collection $recentJobs,
        public Collection $admitCards,
        public Collection $results,
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
            view: 'emails.weekly_digest',
            with: [
                'name' => $this->name,
                'recentJobs' => $this->recentJobs,
                'admitCards' => $this->admitCards,
                'results' => $this->results,
                'subject' => $this->subjectStr,
                'tracking_token' => $this->tracking_token,
                'unsubscribe_url' => url('/unsubscribe?email=' . urlencode($this->to[0]['address'] ?? '')),
            ]
        );
    }
}
