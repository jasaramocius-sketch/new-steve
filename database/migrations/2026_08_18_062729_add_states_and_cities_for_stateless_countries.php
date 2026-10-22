<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $statelessCountries = [
        'Gibraltar' => ['Gibraltar'],
        'Macau S.A.R.' => ['Macau'],
        'Vatican City State (Holy See)' => ['Vatican City'],
        'Curaçao' => ['Willemstad', 'Barber', 'Sint Michiel', 'Lagun', 'Tera Kòrá'],
        'Falkland Islands' => ['Stanley', 'Goose Green', 'Darwin', 'Mount Pleasant', 'Port Howard'],
        'Cook Islands' => ['Avarua', 'Mangaia', 'Atiu', 'Mauke', 'Mitiaro', 'Penrhyn', 'Rarotonga'],
        'Sint Maarten (Dutch part)' => ['Philipsburg', 'Simpson Bay', 'Marigot', 'Lowlands', 'Cay Hill'],
        'Virgin Islands (British)' => ['Road Town', 'Tortola', 'Virgin Gorda', 'Anegada', 'Jost Van Dyke'],
        'Christmas Island' => ['Flying Fish Cove', 'Silver City', 'Poon Saan', 'Drumsite'],
        'Cocos (Keeling) Islands' => ['West Island', 'Home Island', 'Bantam', 'Direction Island'],
        'Norfolk Island' => ['Kingston', 'Burnt Pine', 'Cascade', 'Anson Bay'],
        'Northern Mariana Islands' => ['Saipan', 'Tinian', 'Rota', 'Garapan', 'Chalan Kanoa'],
        'Pitcairn Island' => ['Adamstown'],
        'Svalbard and Jan Mayen Islands' => ['Longyearbyen', 'Barentsburg', 'Ny-Ålesund', 'Hornsund'],
        'Tokelau' => ['Atafu', 'Nukunonu', 'Fakaofo'],
        'British Indian Ocean Territory' => ['Diego Garcia'],
        'Western Sahara' => ['Laayoune', 'Smara', 'Dakhla', 'Boujdour', 'Tifariti'],
        'Antarctica' => ['McMurdo Station', 'Amundsen-Scott South Pole Station', 'Villa Las Estrellas'],
        'Bouvet Island' => [],
        'Heard Island and McDonald Islands' => [],
        'South Georgia' => ['King Edward Point', 'Grytviken', 'Bird Island'],
    ];

    public function up(): void
    {
        $now = now();
        $stateInserts = [];
        $cityInserts = [];

        foreach ($this->statelessCountries as $countryName => $cities) {
            $country = DB::table('countries')->whereRaw('LOWER(name) = ?', [mb_strtolower($countryName)])->first();
            if (! $country) {
                continue;
            }

            $existingStates = DB::table('states')->where('country_id', $country->id)->count();
            if ($existingStates > 0) {
                continue;
            }

            $stateName = $countryName;
            DB::table('states')->insert([
                'country_id' => $country->id,
                'name' => $stateName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $stateId = DB::getPdo()->lastInsertId();

            foreach ($cities as $city) {
                $city = trim($city);
                if ($city === '') {
                    continue;
                }
                $cityInserts[] = [
                    'state_id' => $stateId,
                    'name' => $city,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($cityInserts, 500) as $chunk) {
            DB::table('cities')->insert($chunk);
        }
    }

    public function down(): void
    {
        $countryNames = array_keys($this->statelessCountries);
        foreach ($countryNames as $countryName) {
            $country = DB::table('countries')->whereRaw('LOWER(name) = ?', [mb_strtolower($countryName)])->first();
            if (! $country) {
                continue;
            }

            $stateIds = DB::table('states')->where('country_id', $country->id)->pluck('id');
            if ($stateIds->isEmpty()) {
                continue;
            }

            DB::table('cities')->whereIn('state_id', $stateIds)->delete();
            DB::table('states')->where('country_id', $country->id)->delete();
        }
    }
};
