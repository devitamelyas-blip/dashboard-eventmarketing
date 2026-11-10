<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PostEventReportSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Laporan Pasca-Event Diisi: [{$this->event->code}] {$this->event->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.post-event-report-submitted',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
