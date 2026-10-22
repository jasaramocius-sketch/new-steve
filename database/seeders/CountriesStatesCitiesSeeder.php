<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesStatesCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $dataDir = database_path('data');

        DB::table('cities')->delete();
        DB::table('states')->delete();
        DB::table('countries')->delete();
        DB::statement('ALTER TABLE countries AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE states AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE cities AUTO_INCREMENT = 1');

        // countries.csv — one name per line (order == country id)
        $fh = fopen($dataDir.'/countries.csv', 'r');
        $countries = [];
        $chunk = [];
        while (($row = fgetcsv($fh)) !== false) {
            if (! isset($row[0]) || trim($row[0]) === '') {
                continue;
            }
            $name = trim($row[0]);
            $countries[$name] = count($countries) + 1;
            $chunk[] = ['name' => $name, 'created_at' => now(), 'updated_at' => now()];
            if (count($chunk) >= 200) {
                DB::table('countries')->insert($chunk);
                $chunk = [];
            }
        }
        if ($chunk) {
            DB::table('countries')->insert($chunk);
        }
        fclose($fh);

        // states.csv — countryIndex,name (line order == state id)
        $fh = fopen($dataDir.'/states.csv', 'r');
        $stateKeyToId = [];
        $chunk = [];
        $stateId = 0;
        while (($row = fgetcsv($fh)) !== false) {
            if (! isset($row[1]) || trim($row[1]) === '') {
                continue;
            }
            $countryIdx = (int) $row[0];
            $name = trim($row[1]);
            $stateId++;
            $stateKeyToId[$countryIdx.'|'.$name] = $stateId;
            $chunk[] = ['country_id' => $countryIdx + 1, 'name' => $name, 'created_at' => now(), 'updated_at' => now()];
            if (count($chunk) >= 500) {
                DB::table('states')->insert($chunk);
                $chunk = [];
            }
        }
        if ($chunk) {
            DB::table('states')->insert($chunk);
        }
        fclose($fh);

        // cities.csv — stateIndex,name
        $fh = fopen($dataDir.'/cities.csv', 'r');
        $chunk = [];
        $inserted = 0;
        while (($row = fgetcsv($fh)) !== false) {
            if (! isset($row[1]) || trim($row[1]) === '') {
                continue;
            }
            $stateIdx = (int) $row[0];
            $name = trim($row[1]);
            $chunk[] = ['state_id' => $stateIdx + 1, 'name' => $name, 'created_at' => now(), 'updated_at' => now()];
            $inserted++;
            if (count($chunk) >= 1000) {
                DB::table('cities')->insert($chunk);
                $chunk = [];
            }
        }
        if ($chunk) {
            DB::table('cities')->insert($chunk);
        }
        fclose($fh);

        $this->command->info('Countries/States/Cities seeded: '.DB::table('countries')->count().'/'.
            DB::table('states')->count().'/'.DB::table('cities')->count().' ('.$inserted.' city rows read)');
    }
}
