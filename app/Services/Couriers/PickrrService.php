<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\ApiIntegration;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PickrrService implements CourierInterface
{
    protected ApiIntegration $integration;

    public function __construct(ApiIntegration $integration)
    {
        $this->integration = $integration;
    }

    public function authenticate(): bool
    {
        $creds = $this->integration->credentials;
        return !empty($creds['api_key']);
    }

    public function createShipment(Order $order, array $options = []): array
    {
        if (!$this->authenticate()) {
            return ['success' => false, 'message' => 'API key not configured'];
        }

        $baseUrl = $this->integration->base_url ?: 'https://app.pickrr.com';
        try {
            $creds = $this->integration->credentials;
            $request = Http::timeout(60)->withHeaders(['token' => $creds['api_key']]);

            $payload = array_merge([
                'order_number' => $order->order_number ?? $order->id,
                'first_name' => $order->billing_name ?? optional($order->user)->name,
                'last_name' => '',
                'email' => $order->billing_email ?? optional($order->user)->email,
                'phone' => $order->billing_phone ?? optional($order->user)->phone,
                'address' => $order->billing_address ?? '',
                'city' => $order->billing_city ?? '',
                'state' => $order->billing_state ?? '',
                'pincode' => $order->billing_postcode ?? '',
                'country' => $order->billing_country ?? 'India',
                'payment_type' => $order->payment_method === 'cod' ? 'COD' : 'PREPAID',
                'cod_amount' => $order->payment_method === 'cod' ? $order->total_amount : 0,
                'products' => [],
                'weight' => 0.5,
                'quantity' => 1,
            ], $options);

            $response = $request->post($baseUrl . '/api/v1/create-order/', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $awb = $data['data']['awb'] ?? $data['awb_number'] ?? $data['tracking_id'] ?? null;
                $carrier = $data['data']['courier'] ?? 'Pickrr';
                if ($awb) {
                    $order->update([
                        'tracking_number' => $awb,
                        'tracking_carrier' => $carrier,
                        'status' => 'shipped',
                    ]);
                    if ($order->status !== 'shipped') {
                        $order->recordStatusChange('shipped');
                    }
                }
                return ['success' => true, 'data' => $data, 'awb' => $awb, 'carrier' => $carrier];
            }

            return ['success' => false, 'message' => 'Pickrr API error', 'response' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTracking(string $awb): array
    {
        $baseUrl = $this->integration->base_url ?: 'https://app.pickrr.com';
        try {
            $creds = $this->integration->credentials;
            $request = Http::timeout(30);
            if (!empty($creds['api_key'])) {
                $request = $request->withHeaders(['token' => $creds['api_key']]);
            }
            $response = $request->get($baseUrl . '/api/v1/track-order/', ['tracking_id' => $awb]);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelShipment(string $awb): array
    {
        return ['success' => false, 'message' => 'Not implemented'];
    }

    public function getCouriers(): array
    {
        return ['success' => true, 'data' => ['couriers' => ['Pickrr']]];
    }
}
