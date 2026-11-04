<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact, public bool $toUser = true) {}

    public function envelope(): Envelope
    {
        if ($this->toUser) {
            $subject = 'We received your message - '.config('app.name', 'StAutoparts');
        } else {
            $subject = 'New contact form submission: '.($this->contact->subject ?: 'Inquiry');
        }

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: $this->toUser ? 'mail.contact-received' : 'mail.contact-received-admin',
            with: ['contact' => $this->contact],
        );
    }
}
