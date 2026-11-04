<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\ApiIntegration;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class DelhiveryService implements CourierInterface
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

        $baseUrl = $this->integration->base_url ?: 'https://track.delhivery.com';
        try {
            $creds = $this->integration->credentials;
            $request = Http::timeout(60)->withHeaders(['Authorization' => 'Token ' . $creds['api_key']]);

            $payload = array_merge([
                'shipments' => [[
                    'name' => $order->billing_name ?? optional($order->user)->name,
                    'add' => $order->billing_address ?? '',
                    'pin' => $order->billing_postcode ?? '',
                    'city' => $order->billing_city ?? '',
                    'state' => $order->billing_state ?? '',
                    'country' => $order->billing_country ?? 'India',
                    'phone' => $order->billing_phone ?? optional($order->user)->phone,
                    'order' => $order->order_number ?? $order->id,
                    'payment_mode' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                    'return_pin' => '',
                    'return_city' => '',
                    'return_phone' => '',
                    'return_add' => '',
                    'return_state' => '',
                    'return_country' => 'India',
                    'products_desc' => 'Order Items',
                    'hsn_code' => '',
                    'cod_amount' => $order->payment_method === 'cod' ? $order->total_amount : 0,
                    'order_date' => $order->created_at->format('Y-m-d H:i:s'),
                    'total_amount' => $order->total_amount,
                    'seller_add' => '',
                    'seller_name' => config('app.name'),
                    'seller_inv' => '',
                    'quantity' => 1,
                    'waybill' => '',
                    'shipment_width' => 10,
                    'shipment_height' => 10,
                    'weight' => 0.5,
                    'seller_gst_tin' => '',
                    'shipping_mode' => 'Surface',
                    'address_type' => 'home'
                ]],
                'pickup_location' => ['name' => $creds['pickup_location'] ?? config('app.name')]
            ], $options);

            $response = $request->asForm()->post($baseUrl . '/api/cmu/create.json', ['format' => 'json', 'data' => json_encode($payload)]);

            if ($response->successful()) {
                $data = $response->json();
                $awb = $data['packages'][0]['waybill'] ?? $data['waybill'] ?? null;
                if ($awb) {
                    $order->update([
                        'tracking_number' => $awb,
                        'tracking_carrier' => 'Delhivery',
                        'status' => 'shipped',
                    ]);
                    if ($order->status !== 'shipped') {
                        $order->recordStatusChange('shipped');
                    }
                }
                return ['success' => true, 'data' => $data, 'awb' => $awb, 'carrier' => 'Delhivery'];
            }

            return ['success' => false, 'message' => 'Delhivery API error', 'response' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTracking(string $awb): array
    {
        $baseUrl = $this->integration->base_url ?: 'https://track.delhivery.com';
        try {
            $creds = $this->integration->credentials;
            $request = Http::timeout(30);
            if (!empty($creds['api_key'])) {
                $request = $request->withHeaders(['Authorization' => 'Token ' . $creds['api_key']]);
            }
            $response = $request->get($baseUrl . '/api/v1/packages/json/', ['waybill' => $awb]);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelShipment(string $awb): array
    {
        $baseUrl = $this->integration->base_url ?: 'https://track.delhivery.com';
        try {
            $creds = $this->integration->credentials;
            $request = Http::timeout(30);
            if (!empty($creds['api_key'])) {
                $request = $request->withHeaders(['Authorization' => 'Token ' . $creds['api_key']]);
            }
            $response = $request->post($baseUrl . '/api/p/edit', ['waybill' => $awb, 'cancellation' => 'true']);
            return ['success' => $response->successful(), 'data' => $response->json()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getCouriers(): array
    {
        return ['success' => true, 'data' => ['couriers' => ['Delhivery']]];
    }
}
