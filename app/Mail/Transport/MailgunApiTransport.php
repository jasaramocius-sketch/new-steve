<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailgunApiTransport extends AbstractTransport
{
    public function __construct(
        private string $domain,
        private string $apiKey,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $email = $sentMessage->getOriginalMessage();
        if (! $email instanceof Email) {
            throw new RuntimeException('Mailgun transport supports standard email messages only.');
        }

        $from = $email->getFrom()[0] ?? $sentMessage->getEnvelope()->getSender();
        $payload = [
            'from' => $this->formatAddress($from),
            'to' => implode(',', array_map(fn (Address $address) => $this->formatAddress($address), $email->getTo())),
            'subject' => $email->getSubject() ?? '',
        ];
        $html = $email->getHtmlBody();
        $text = $email->getTextBody();
        if ($html !== null) {
            $payload['html'] = $this->bodyContent($html);
        }
        if ($text !== null) {
            $payload['text'] = $this->bodyContent($text);
        }
        if ($email->getCc() !== []) {
            $payload['cc'] = implode(',', array_map(fn (Address $address) => $this->formatAddress($address), $email->getCc()));
        }
        if ($email->getBcc() !== []) {
            $payload['bcc'] = implode(',', array_map(fn (Address $address) => $this->formatAddress($address), $email->getBcc()));
        }

        $response = Http::withBasicAuth('api', $this->apiKey)
            ->asForm()
            ->timeout(20)
            ->post('https://api.mailgun.net/v3/'.rawurlencode($this->domain).'/messages', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Mailgun send failed with HTTP '.$response->status().'.');
        }
    }

    public function __toString(): string
    {
        return 'mailgun-api://'.$this->domain;
    }

    private function formatAddress(Address $address): string
    {
        return $address->getName() ? $address->getName().' <'.$address->getAddress().'>' : $address->getAddress();
    }

    private function bodyContent(mixed $body): string
    {
        return is_resource($body) ? (stream_get_contents($body) ?: '') : (string) $body;
    }
}
