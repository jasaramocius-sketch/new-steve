<?php

namespace App\Console\Commands;

use App\Services\VehicleCatalog;
use Illuminate\Console\Command;

class WarmVehicleCatalog extends Command
{
    protected $signature = 'vehicles:warm-catalog';

    protected $description = 'Build and cache the vehicle catalog used by the shop year/make/model filters';

    public function handle(VehicleCatalog $catalog)
    {
        $this->info('Caching years...');
        $years = $catalog->years();
        $this->info('  '.count($years).' years cached.');

        $this->info('Vehicle catalog warmed successfully.');

        return self::SUCCESS;
    }
}
