<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalesUpdateReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Event $event)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pengingat H+90: Masukkan Omzet Penjualan Event [{$this->event->code}]",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sales-update-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
