<?php

namespace App\Services;

use App\Models\ProductVehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VehicleCatalog
{
    public const TTL = 2592000; // 30 days

    public function years(): Collection
    {
        return collect(Cache::remember('vehicle_catalog.years', self::TTL, function () {
            return ProductVehicle::query()
                ->whereNotNull('year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year')
                ->map(fn ($year) => (int) $year)
                ->all();
        }));
    }

    public function makes(?int $year = null): Collection
    {
        $key = 'vehicle_catalog.makes.'.($year ?: 'all');

        return collect(Cache::remember($key, self::TTL, function () use ($year) {
            $query = ProductVehicle::query()
                ->whereNotNull('make')
                ->where('make', '<>', '');

            if ($year) {
                $query->where('year', $year);
            }

            return $query->distinct()->orderBy('make')->pluck('make')->all();
        }));
    }

    public function models(?int $year = null, ?string $make = null): Collection
    {
        if (! $year && ! $make) {
            return collect();
        }

        $key = 'vehicle_catalog.models.'.($year ?: 'all').'.'.md5((string) $make);

        return collect(Cache::remember($key, self::TTL, function () use ($year, $make) {
            $query = ProductVehicle::query()
                ->whereNotNull('model')
                ->where('model', '<>', '');

            if ($year) {
                $query->where('year', $year);
            }

            if ($make) {
                $query->where('make', $make);
            }

            return $query->distinct()->orderBy('model')->pluck('model')->all();
        }));
    }

    public function flush(): void
    {
        Cache::forget('vehicle_catalog.years');
        Cache::forget('vehicle_catalog.makes.all');
    }
}
