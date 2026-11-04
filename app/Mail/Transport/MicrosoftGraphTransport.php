<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MicrosoftGraphTransport extends AbstractTransport
{
    public function __construct(
        private string $tenantId,
        private string $clientId,
        private string $clientSecret,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $sentMessage): void
    {
        $email = $sentMessage->getOriginalMessage();
        if (! $email instanceof Email) {
            throw new RuntimeException('Microsoft Graph transport supports standard email messages only.');
        }

        $from = $email->getFrom()[0] ?? $sentMessage->getEnvelope()->getSender();
        $body = $email->getHtmlBody() ?? $email->getTextBody() ?? '';
        $contentType = $email->getHtmlBody() !== null ? 'HTML' : 'Text';
        if (is_resource($body)) {
            $body = stream_get_contents($body) ?: '';
        }

        $payload = [
            'message' => [
                'subject' => $email->getSubject() ?? '',
                'body' => ['contentType' => $contentType, 'content' => (string) $body],
                'from' => ['emailAddress' => ['address' => $from->getAddress(), 'name' => $from->getName() ?? '']],
                'toRecipients' => $this->recipients($email->getTo()),
            ],
            'saveToSentItems' => true,
        ];
        if ($email->getCc() !== []) {
            $payload['message']['ccRecipients'] = $this->recipients($email->getCc());
        }
        if ($email->getBcc() !== []) {
            $payload['message']['bccRecipients'] = $this->recipients($email->getBcc());
        }

        $response = Http::withToken($this->accessToken())
            ->timeout(20)
            ->post('https://graph.microsoft.com/v1.0/users/'.rawurlencode($from->getAddress()).'/sendMail', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Microsoft Graph sendMail failed with HTTP '.$response->status().'.');
        }
    }

    public function __toString(): string
    {
        return 'microsoft-graph://'.$this->tenantId;
    }

    private function accessToken(): string
    {
        $cacheKey = 'microsoft_graph_mail_token_'.hash('sha256', $this->tenantId.$this->clientId);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::asForm()->timeout(15)->post(
            'https://login.microsoftonline.com/'.rawurlencode($this->tenantId).'/oauth2/v2.0/token',
            [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]
        );
        if (! $response->successful() || ! is_string($response->json('access_token'))) {
            throw new RuntimeException('Microsoft Graph authentication failed with HTTP '.$response->status().'.');
        }

        $token = $response->json('access_token');
        Cache::put($cacheKey, $token, max(60, (int) $response->json('expires_in', 3600) - 60));

        return $token;
    }

    private function recipients(array $addresses): array
    {
        return array_map(static fn (Address $address) => [
            'emailAddress' => [
                'address' => $address->getAddress(),
                'name' => $address->getName() ?? '',
            ],
        ], $addresses);
    }
}
