<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $token, public string $email) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your Password - '.config('app.name', 'StAutoparts'),
        );
    }

    public function content(): Content
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $this->email]);
        $count = config('auth.passwords.users.expire', 60);

        return new Content(
            view: 'mail.password-reset',
            with: ['url' => $url, 'email' => $this->email, 'count' => $count],
        );
    }
}
