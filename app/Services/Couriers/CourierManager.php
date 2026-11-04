<?php

namespace App\Services\Couriers;

use App\Contracts\CourierInterface;
use App\Models\ApiIntegration;

class CourierManager
{
    protected array $map = [
        'shiprocket' => ShiprocketService::class,
        'delhivery' => DelhiveryService::class,
        'pickrr' => PickrrService::class,
        'custom' => CustomCourierService::class,
    ];

    public function driver(?string $provider = null): ?CourierInterface
    {
        $integration = null;

        if ($provider) {
            $integration = ApiIntegration::where('type', 'courier')
                ->where('provider', $provider)
                ->where('is_active', true)
                ->first();
        }

        if (!$integration) {
            $integration = ApiIntegration::getActiveCourier();
        }

        if (!$integration) {
            return null;
        }

        $class = $this->map[$integration->provider] ?? CustomCourierService::class;
        return app()->make($class, ['integration' => $integration]);
    }

    public function ship(Order $order, array $options = []): array
    {
        $driver = $this->driver();
        if (!$driver) {
            return ['success' => false, 'message' => 'No active courier API configured.'];
        }

        $auth = $driver->authenticate();
        if (!$auth) {
            return ['success' => false, 'message' => 'Courier authentication failed.'];
        }

        return $driver->createShipment($order, $options);
    }
}
