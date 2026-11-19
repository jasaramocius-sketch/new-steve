<?php

namespace App\Services;

use App\Models\ShippingZone;
use App\Models\ShippingZoneRate;
use Illuminate\Database\Eloquent\Collection;

class ShippingRateService
{
    public function availableRatesFor(array $address): Collection
    {
        $country = $this->normalize($address['country'] ?? '');
        $state = $this->normalize($address['state'] ?? '');
        $city = $this->normalize($address['city'] ?? '');

        if ($country === '') {
            return new Collection();
        }

        $matchingZones = ShippingZone::query()
            ->where('is_active', true)
            ->with(['rates.carrier'])
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
            });

        foreach ($matchingZones as $zone) {
            $rates = $zone->rates
                ->filter(fn (ShippingZoneRate $rate): bool => $rate->carrier?->is_active === true)
                ->sort(fn (ShippingZoneRate $left, ShippingZoneRate $right): int =>
                    ($left->carrier->sort_order <=> $right->carrier->sort_order)
                    ?: strcasecmp($left->carrier->name, $right->carrier->name)
                )
                ->values();

            if ($rates->isNotEmpty()) {
                return $rates;
            }
        }

        return new Collection();
    }

    public function resolveAvailableRate(array $address, int $rateId): ?ShippingZoneRate
    {
        return $this->availableRatesFor($address)->first(
            fn (ShippingZoneRate $rate): bool => $rate->id === $rateId
        );
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
