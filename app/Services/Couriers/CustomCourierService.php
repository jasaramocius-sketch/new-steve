<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\ApiIntegration;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class CustomCourierService implements CourierInterface
{
    protected ApiIntegration $integration;

    public function __construct(ApiIntegration $integration)
    {
        $this->integration = $integration;
    }

    public function authenticate(): bool
    {
        $creds = $this->integration->credentials;
        if ($this->integration->auth_type === 'none') {
            return true;
        }
        if ($this->integration->auth_type === 'bearer' && !empty($creds['api_key'])) {
            return true;
        }
        if ($this->integration->auth_type === 'api_key' && !empty($creds['api_key'])) {
            return true;
        }
        if ($this->integration->auth_type === 'basic' && !empty($creds['username']) && !empty($creds['password'])) {
            return true;
        }
        if (!empty($creds)) {
            return true;
        }
        return true;
    }

    public function createShipment(Order $order, array $options = []): array
    {
        $baseUrl = $this->integration->base_url;
        if (!$baseUrl) {
            return ['success' => false, 'message' => 'Base URL not configured.'];
        }

        $endpoints = $this->integration->endpoints;
        $url = rtrim($baseUrl, '/') . ($endpoints['create_shipment'] ?? '/shipments');

        try {
            $request = Http::timeout(60);
            $creds = $this->integration->credentials;

            if ($this->integration->auth_type === 'bearer' && !empty($creds['api_key'])) {
                $request = $request->withToken($creds['api_key']);
            } elseif ($this->integration->auth_type === 'api_key' && !empty($creds['api_key'])) {
                $request = $request->withHeaders(['x-api-key' => $creds['api_key']]);
            } elseif ($this->integration->auth_type === 'basic' && !empty($creds['username'])) {
                $request = $request->withBasicAuth($creds['username'], $creds['password'] ?? '');
            }

            $payload = array_merge([
                'order_id' => $order->order_number ?? $order->id,
                'amount' => $order->total_amount,
                'customer_name' => $order->billing_name ?? optional($order->user)->name,
                'customer_phone' => $order->billing_phone ?? optional($order->user)->phone,
                'customer_email' => $order->billing_email ?? optional($order->user)->email,
                'address' => $order->billing_address ?? '',
                'city' => $order->billing_city ?? '',
                'state' => $order->billing_state ?? '',
                'pincode' => $order->billing_postcode ?? '',
                'payment_method' => $order->payment_method,
            ], $options);

            $response = $request->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $awb = $data['awb'] ?? $data['tracking_number'] ?? $data['waybill'] ?? null;
                $carrier = $data['carrier'] ?? $data['courier'] ?? $this->integration->name;
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

            return ['success' => false, 'message' => 'API error', 'status' => $response->status(), 'response' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTracking(string $awb): array
    {
        $baseUrl = $this->integration->base_url;
        if (!$baseUrl) {
            return ['success' => false, 'message' => 'Base URL not configured.'];
        }
        $endpoints = $this->integration->endpoints;
        $url = rtrim($baseUrl, '/') . str_replace('{awb}', $awb, ($endpoints['track'] ?? '/track/' . $awb));
        try {
            $request = Http::timeout(30);
            $creds = $this->integration->credentials;
            if ($this->integration->auth_type === 'bearer' && !empty($creds['api_key'])) {
                $request = $request->withToken($creds['api_key']);
            } elseif ($this->integration->auth_type === 'api_key' && !empty($creds['api_key'])) {
                $request = $request->withHeaders(['x-api-key' => $creds['api_key']]);
            }
            $response = $request->get($url);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelShipment(string $awb): array
    {
        $baseUrl = $this->integration->base_url;
        if (!$baseUrl) {
            return ['success' => false, 'message' => 'Base URL not configured.'];
        }
        $endpoints = $this->integration->endpoints;
        $url = rtrim($baseUrl, '/') . str_replace('{awb}', $awb, ($endpoints['cancel'] ?? '/shipments/' . $awb . '/cancel'));
        try {
            $request = Http::timeout(30);
            $creds = $this->integration->credentials;
            if ($this->integration->auth_type === 'bearer' && !empty($creds['api_key'])) {
                $request = $request->withToken($creds['api_key']);
            }
            $response = $request->post($url, ['awb' => $awb]);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getCouriers(): array
    {
        $baseUrl = $this->integration->base_url;
        if (!$baseUrl) {
            return ['success' => false, 'message' => 'Base URL not configured.'];
        }
        $endpoints = $this->integration->endpoints;
        $url = rtrim($baseUrl, '/') . ($endpoints['couriers'] ?? '/couriers');
        try {
            $request = Http::timeout(30);
            $creds = $this->integration->credentials;
            if ($this->integration->auth_type === 'bearer' && !empty($creds['api_key'])) {
                $request = $request->withToken($creds['api_key']);
            }
            $response = $request->get($url);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
