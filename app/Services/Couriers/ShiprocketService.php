<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\ApiIntegration;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class ShiprocketService implements CourierInterface
{
    protected ApiIntegration $integration;
    protected ?string $token = null;

    public function __construct(ApiIntegration $integration)
    {
        $this->integration = $integration;
    }

    public function authenticate(): bool
    {
        $creds = $this->integration->credentials;
        if (empty($creds['email']) || empty($creds['password'])) {
            return false;
        }

        try {
            $response = Http::timeout(30)->post('https://apiv2.shiprocket.in/v1/external/auth/login', [
                'email' => $creds['email'],
                'password' => $creds['password'],
            ]);

            if ($response->successful()) {
                $this->token = $response->json('token');
                return (bool) $this->token;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    public function createShipment(Order $order, array $options = []): array
    {
        if (!$this->token && !$this->authenticate()) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        try {
            // Minimal placeholder - can be extended with full payload
            $payload = [
                'order_id' => $order->order_number ?? $order->id,
                'order_date' => $order->created_at->format('Y-m-d H:i'),
                'pickup_location' => $this->integration->credentials['pickup_location'] ?? null,
                'billing_customer_name' => $order->billing_name ?? optional($order->user)->name,
                'billing_last_name' => '',
                'billing_address' => $order->billing_address ?? '',
                'billing_city' => $order->billing_city ?? '',
                'billing_pincode' => $order->billing_postcode ?? '',
                'billing_state' => $order->billing_state ?? '',
                'billing_country' => $order->billing_country ?? 'India',
                'billing_email' => $order->billing_email ?? optional($order->user)->email,
                'billing_phone' => $order->billing_phone ?? optional($order->user)->phone,
                'shipping_is_billing' => true,
                'order_items' => [],
                'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                'sub_total' => $order->total_amount,
                'length' => 10,
                'breadth' => 10,
                'height' => 10,
                'weight' => 0.5,
            ];

            $response = Http::timeout(60)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                ->post('https://apiv2.shiprocket.in/v1/external/orders/create/adhoc', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $awb = $data['shipment_id'] ?? $data['awb_code'] ?? null;
                $carrier = $data['courier_name'] ?? 'Shiprocket';
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

            return ['success' => false, 'message' => 'Shiprocket API error', 'response' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTracking(string $awb): array
    {
        if (!$this->token && !$this->authenticate()) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                ->get('https://apiv2.shiprocket.in/v1/external/courier/track/awb/' . $awb);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelShipment(string $awb): array
    {
        if (!$this->token && !$this->authenticate()) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                ->post('https://apiv2.shiprocket.in/v1/external/orders/cancel/shipment/awbs', ['awbs' => [$awb]]);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getCouriers(): array
    {
        if (!$this->token && !$this->authenticate()) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                ->get('https://apiv2.shiprocket.in/v1/external/courier/serviceability/');
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
