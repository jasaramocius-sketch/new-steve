<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class StripeCheckoutService
{
    private const API_BASE = 'https://api.stripe.com/v1';

    /**
     * Currencies whose amounts are not expressed in minor units.
     */
    private const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG',
        'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Read the credentials saved from Admin > Settings > Payment Methods.
     */
    public function credentials(): array
    {
        $stored = Setting::get('payment_method_stripe');

        if (! $stored) {
            return [];
        }

        try {
            $decoded = json_decode(Crypt::decryptString($stored), true);
        } catch (Throwable) {
            $decoded = json_decode($stored, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function isConfigured(): bool
    {
        $credentials = $this->credentials();

        return ! empty($credentials['STRIPE_KEY']) && ! empty($credentials['STRIPE_SECRET']);
    }

    public function publishableKey(): ?string
    {
        return $this->credentials()['STRIPE_KEY'] ?? null;
    }

    public function currency(): string
    {
        return strtoupper((string) Setting::get('system_default_currency', 'USD'));
    }

    public function toMinorUnits(float $amount): int
    {
        return in_array($this->currency(), self::ZERO_DECIMAL_CURRENCIES, true)
            ? (int) round($amount)
            : (int) round($amount * 100);
    }

    /**
     * Create a hosted Stripe Checkout session and return the Stripe response.
     *
     * @param  array<int, array{name:string, amount:float, quantity:int}>  $items
     * @param  array{order_id?:int|string, email?:string|null, success_url?:string, cancel_url?:string}  $options
     */
    public function createCheckoutSession(array $items, string $orderNumber, array $options = []): object
    {
        $secret = $this->credentials()['STRIPE_SECRET'] ?? null;

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }
        if ($items === []) {
            throw new RuntimeException('Stripe checkout requires at least one line item.');
        }

        $payload = [
            'mode' => 'payment',
            'success_url' => $options['success_url'] ?? route('checkout.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $options['cancel_url'] ?? route('checkout.stripe.cancel'),
            'client_reference_id' => $orderNumber,
            'metadata[order_number]' => $orderNumber,
        ];

        if (! empty($options['order_id'])) {
            $payload['metadata[order_id]'] = (string) $options['order_id'];
        }
        if (! empty($options['email'])) {
            $payload['customer_email'] = $options['email'];
        }

        foreach (array_values($items) as $index => $item) {
            $payload["line_items[{$index}][quantity]"] = (string) max(1, (int) $item['quantity']);
            $payload["line_items[{$index}][price_data][currency]"] = $this->currency();
            $payload["line_items[{$index}][price_data][unit_amount]"] = (string) max(0, $this->toMinorUnits((float) $item['amount']));
            $payload["line_items[{$index}][price_data][product_data][name]"] = $item['name'];
        }

        $response = Http::asForm()
            ->withToken($secret)
            ->timeout(20)
            ->post(self::API_BASE.'/checkout/sessions', $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->json('error.message') ?: 'Stripe checkout could not be started.');
        }

        return $response->object();
    }

    public function retrieveCheckoutSession(string $sessionId): ?object
    {
        $secret = $this->credentials()['STRIPE_SECRET'] ?? null;

        if (! $secret || $sessionId === '') {
            return null;
        }

        $response = Http::withToken($secret)
            ->timeout(20)
            ->get(self::API_BASE.'/checkout/sessions/'.rawurlencode($sessionId));

        return $response->successful() ? $response->object() : null;
    }
}
