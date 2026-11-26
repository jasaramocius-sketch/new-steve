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
        return collect(Cache::remember($this->cacheKey('years'), self::TTL, function () {
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
        $key = $this->cacheKey('makes.'.($year ?: 'all'));

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

        $key = $this->cacheKey('models.'.($year ?: 'all').'.'.md5((string) $make));

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

    public function allModels(): Collection
    {
        return collect(Cache::remember($this->cacheKey('models.all'), self::TTL, function () {
            return ProductVehicle::query()
                ->whereNotNull('model')
                ->where('model', '<>', '')
                ->distinct()
                ->orderBy('model')
                ->pluck('model')
                ->all();
        }));
    }

    public function allEngines(): Collection
    {
        return collect(Cache::remember($this->cacheKey('engines.all'), self::TTL, function () {
            return ProductVehicle::query()
                ->whereNotNull('engine')
                ->where('engine', '<>', '')
                ->distinct()
                ->orderBy('engine')
                ->pluck('engine')
                ->all();
        }));
    }

    public function flush(): void
    {
        Cache::forever('vehicle_catalog.version', (int) Cache::get('vehicle_catalog.version', 1) + 1);
    }

    private function cacheKey(string $name): string
    {
        return 'vehicle_catalog.v'.(int) Cache::get('vehicle_catalog.version', 1).'.'.$name;
    }
}
