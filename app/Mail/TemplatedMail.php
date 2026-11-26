<?php

namespace App\Mail;

use App\Support\EmailTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplatedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected ?array $rendered = null;

    public function __construct(
        public string $templateKey,
        public array $data = [],
        public ?string $fallbackSubject = null,
        public ?string $fallbackView = null,
        public array $fallbackViewData = []
    ) {}

    protected function rendered(): ?array
    {
        if ($this->rendered !== null) {
            return $this->rendered ?: null;
        }

        $this->rendered = EmailTemplates::render($this->templateKey, $this->data) ?: [];
        return $this->rendered ?: null;
    }

    public function envelope(): Envelope
    {
        $rendered = $this->rendered();
        return new Envelope(
            subject: $rendered['subject'] ?? ($this->fallbackSubject ?? config('app.name')),
        );
    }

    public function content(): Content
    {
        $rendered = $this->rendered();
        if ($rendered) {
            return new Content(htmlString: $rendered['body_html']);
        }

        if ($this->fallbackView) {
            return new Content(view: $this->fallbackView, with: $this->fallbackViewData);
        }

        return new Content(htmlString: '<p>' . e($this->fallbackSubject ?? 'Notification') . '</p>');
    }
}
