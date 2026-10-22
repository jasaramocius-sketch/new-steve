<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Contact $contact) {}

    public function envelope(): Envelope
    {
        $subject = $this->contact->product_id
            ? 'Reply to your product inquiry: '.($this->contact->product->name ?? '')
            : 'Reply to your inquiry: '.($this->contact->subject ?: 'Contact');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-reply',
            with: ['contact' => $this->contact],
        );
    }
}
