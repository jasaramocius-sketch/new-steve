<?php

namespace App\Contracts;

use App\Models\Order;

interface CourierInterface
{
    public function authenticate(): bool;
    public function createShipment(Order $order, array $options = []): array;
    public function getTracking(string $awb): array;
    public function cancelShipment(string $awb): array;
    public function getCouriers(): array;
}
