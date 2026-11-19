<?php

namespace App\Services;

use App\Models\City;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\ShippingZone;

class ShippingChargeService
{
    public const TYPES = [
        'product_wise_shipping' => 'Product Wise Shipping',
        'flat_rate' => 'Flat Rate Shipping',
        'seller_wise_shipping' => 'Seller Wise Flat Shipping',
        'area_wise_shipping' => 'Area Wise Flat Shipping',
        'carrier_wise_shipping' => 'Carrier Wise Shipping',
    ];

    public function type(): string
    {
        $type = (string) Setting::get('shipping_type', 'carrier_wise_shipping');

        return array_key_exists($type, self::TYPES) ? $type : 'carrier_wise_shipping';
    }

    public function isCarrierBased(): bool
    {
        return $this->type() === 'carrier_wise_shipping';
    }

    public function label(?string $type = null): string
    {
        return self::TYPES[$type ?? $this->type()] ?? 'Shipping';
    }

    /**
     * Calculate the single delivery charge for non-carrier shipping methods.
     * Carrier based shipping is resolved from shipping zone rates instead.
     */
    public function quote(array $cart, array $address): ?array
    {
        $type = $this->type();

        if ($type === 'carrier_wise_shipping') {
            return null;
        }

        $zone = null;
        $city = null;

        switch ($type) {
            case 'flat_rate':
                $cost = (float) Setting::get('flat_rate_shipping_cost', 0);
                break;

            case 'product_wise_shipping':
                $cost = $this->productWiseCost($cart);
                break;

            case 'seller_wise_shipping':
                $cost = $this->sellerWiseCost($cart);
                break;

            case 'area_wise_shipping':
                $city = $this->matchingCity($address);

                if ($city) {
                    $cost = (float) $city->cost;
                    break;
                }

                $zone = $this->matchingZone($address);
                if (! $zone) {
                    return null;
                }
                $cost = (float) $zone->cost;
                break;

            default:
                return null;
        }

        $areaName = $city?->name ?? $zone?->name;

        return [
            'cost' => round(max(0, $cost), 2),
            'carrier_name' => $this->label($type).($areaName ? ' - '.$areaName : ''),
            'delivery_estimate' => null,
            'zone_name' => $areaName,
        ];
    }

    private function productWiseCost(array $cart): float
    {
        $products = $this->cartProducts($cart, ['id', 'shipping_cost']);
        $total = 0.0;

        foreach ($cart as $id => $item) {
            $product = $products->get((int) $id);
            $total += (float) ($product->shipping_cost ?? 0) * (int) ($item['qty'] ?? 0);
        }

        return $total;
    }

    private function sellerWiseCost(array $cart): float
    {
        $products = $this->cartProducts($cart, ['id', 'seller_id']);
        $sellerIds = $products->pluck('seller_id')->filter()->unique()->values();
        $sellerCosts = Seller::whereIn('id', $sellerIds)->pluck('shipping_cost', 'id');
        $adminCost = (float) Setting::get('shipping_cost_admin', 0);

        $groups = [];
        foreach ($cart as $id => $item) {
            $sellerId = $products->get((int) $id)?->seller_id;
            $groups[$sellerId ?: 'admin'] = true;
        }

        $total = 0.0;
        foreach (array_keys($groups) as $groupId) {
            $total += $groupId === 'admin'
                ? $adminCost
                : (float) ($sellerCosts[$groupId] ?? 0);
        }

        return $total;
    }

    private function cartProducts(array $cart, array $columns)
    {
        $ids = array_map('intval', array_keys($cart));

        return Product::whereIn('id', $ids)->get($columns)->keyBy('id');
    }

    private function matchingCity(array $address): ?City
    {
        $country = trim((string) ($address['country'] ?? ''));
        $state = trim((string) ($address['state'] ?? ''));
        $city = trim((string) ($address['city'] ?? ''));

        if ($country === '' || $city === '') {
            return null;
        }

        return City::query()
            ->where('status', true)
            ->whereNotNull('cost')
            ->where('name', $city)
            ->whereHas('state', function ($query) use ($country, $state): void {
                $query->where('status', true);

                if ($state !== '') {
                    $query->where('name', $state);
                }

                $query->whereHas('country', fn ($countryQuery) => $countryQuery
                    ->where('status', true)
                    ->where('name', $country));
            })
            ->orderBy('id')
            ->first();
    }

    private function matchingZone(array $address): ?ShippingZone
    {
        $country = $this->normalize($address['country'] ?? '');
        $state = $this->normalize($address['state'] ?? '');
        $city = $this->normalize($address['city'] ?? '');

        return ShippingZone::query()
            ->where('is_active', true)
            ->get()
            ->filter(function (ShippingZone $zone) use ($country, $state, $city): bool {
                return $this->matches($zone->country, $country)
                    && $this->matchesOptional($zone->state, $state)
                    && $this->matchesOptional($zone->city, $city);
            })
            ->sort(function (ShippingZone $left, ShippingZone $right): int {
                $specificity = fn (ShippingZone $zone): int =>
                    (int) ($this->normalize($zone->country) !== '*')
                    + (int) ($this->normalize($zone->state ?? '') !== '')
                    + (int) ($this->normalize($zone->city ?? '') !== '');

                return ($specificity($right) <=> $specificity($left))
                    ?: ($right->priority <=> $left->priority)
                    ?: ($right->id <=> $left->id);
            })
            ->first();
    }

    private function matches(string $zoneValue, string $addressValue): bool
    {
        $zoneValue = $this->normalize($zoneValue);

        return $zoneValue === '*' || $zoneValue === $addressValue;
    }

    private function matchesOptional(?string $zoneValue, string $addressValue): bool
    {
        $zoneValue = $this->normalize($zoneValue ?? '');

        return $zoneValue === '' || $zoneValue === '*' || ($addressValue !== '' && $zoneValue === $addressValue);
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}
