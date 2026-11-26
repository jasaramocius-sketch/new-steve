<?php

namespace App\Mail;

use App\Models\User;
use App\Support\EmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    protected ?array $rendered = null;

    public function __construct(public string $token, public string $email) {}

    protected function rendered(): ?array
    {
        if ($this->rendered !== null) {
            return $this->rendered ?: null;
        }

        $url = route('password.reset', ['token' => $this->token, 'email' => $this->email]);
        $user = User::where('email', $this->email)->first();

        $this->rendered = EmailTemplates::render('password_reset', [
            'customer_name' => $user->name ?? 'there',
            'reset_url' => $url,
        ]) ?: [];

        return $this->rendered ?: null;
    }

    public function envelope(): Envelope
    {
        $rendered = $this->rendered();
        return new Envelope(
            subject: $rendered['subject'] ?? ('Reset Your Password - ' . config('app.name', 'Steve Autoparts')),
        );
    }

    public function content(): Content
    {
        $rendered = $this->rendered();
        $url = route('password.reset', ['token' => $this->token, 'email' => $this->email]);
        $count = config('auth.passwords.users.expire', 60);

        if ($rendered) {
            return new Content(htmlString: $rendered['body_html']);
        }

        return new Content(
            view: 'mail.password-reset',
            with: ['url' => $url, 'email' => $this->email, 'count' => $count],
        );
    }
}
