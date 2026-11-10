<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventCorrectionRequiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public string $reason
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Perlu Koreksi: Event [{$this->event->code}] {$this->event->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-correction-required',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
