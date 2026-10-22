<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $knownCities = [
        'United Kingdom' => [
            'England' => [
                'London', 'Manchester', 'Birmingham', 'Leeds', 'Liverpool',
                'Sheffield', 'Bristol', 'Newcastle upon Tyne', 'Nottingham',
                'Leicester', 'Coventry', 'Bradford', 'Cardiff', 'Stoke-on-Trent',
                'Wolverhampton', 'Plymouth', 'Derby', 'Reading', 'Southampton',
                'Luton', 'Sunderland', 'Oxford', 'Cambridge', 'York', 'Brighton',
                'Bath', 'Canterbury', 'Winchester', 'Exeter', 'Norwich',
            ],
            'Scotland' => [
                'Edinburgh', 'Glasgow', 'Aberdeen', 'Dundee', 'Inverness',
                'Stirling', 'Perth', 'Paisley', 'Falkirk', 'Cumbernauld',
                'Livingston', 'Hamilton', 'Ayr', 'Kilmarnock', 'Coatbridge',
                'Greenock', 'Perth', 'Dumfries', 'Oban', 'Fort William',
            ],
            'Wales' => [
                'Cardiff', 'Swansea', 'Newport', 'Wrexham', 'Barry',
                'Bridgend', 'Neath', 'Port Talbot', 'Cwmbran', 'Llanelli',
                'Merthyr Tydfil', 'Pontypridd', 'Rhyl', 'Bangor', 'Aberystwyth',
                ' Carmarthen', 'Llandudno', 'Conwy', 'Haverfordwest', 'Holyhead',
            ],
            'Northern Ireland' => [
                'Belfast', 'Derry', 'Lisburn', 'Newry', 'Armagh',
                'Newtownabbey', 'Craigavon', 'Bangor', 'Enniskillen',
                'Omagh', 'Coleraine', 'Limavady', 'Ballymena', 'Antrim',
                'Downpatrick', 'Dungannon', 'Strabane', 'Cookstown', 'Magherafelt', 'Enniskillen',
            ],
            'London' => [
                'City of London', 'Westminster', 'Camden', 'Islington', 'Hackney',
                'Tower Hamlets', 'Greenwich', 'Lewisham', 'Southwark', 'Lambeth',
                'Wandsworth', 'Hammersmith', 'Fulham', 'Kensington', 'Chelsea',
                'Westminster', 'Haringey', 'Enfield', 'Barnet', 'Harrow',
                'Brent', 'Ealing', 'Hounslow', 'Richmond', 'Kingston upon Thames',
                'Merton', 'Sutton', 'Croydon', 'Bromley', 'Bexley',
                'Havering', 'Hillingdon', 'Redbridge', 'Newham', 'Waltham Forest',
            ],
            'Hammersmith and Fulham' => [
                'Hammersmith', 'Fulham', 'Shepherd\'s Bush', 'West Kensington',
                'Barons Court', 'East Acton', 'North End', 'Normand Park',
                'Munster', 'Sulhamstead', 'Parsons Green', 'Wandsworth Bridge',
            ],
            'Newham' => [
                'Stratford', 'West Ham', 'East Ham', 'Canning Town', 'Plaistow',
                'Forest Gate', 'Upton Park', 'Beckton', 'Custom House', 'North Woolwich',
                'Manor Park', 'Little Ilford', 'Maryland', 'Leytonstone', 'Walthamstow',
            ],
            'Newport' => [
                'Newport', 'Caerleon', 'Duffryn', 'Langstone', 'Bassaleg',
                'Rogerstone', 'Risca', 'Cwmbran', 'Pontypool', 'Abertillery',
                'Blackwood', 'Ebbw Vale', 'Bargoed', 'Tredegar', 'Abergavenny',
            ],
            'Westminster' => [
                'Westminster', 'Soho', 'Mayfair', 'St James\'s', 'Marylebone',
                'Fitzrovia', 'Pimlico', 'Belgravia', 'Covent Garden', 'Chinatown',
                'Paddington', 'Bayswater', 'Kensington', 'Chelsea', 'Knightsbridge',
            ],
        ],
        'Ireland' => [
            'Dublin' => ['Dublin', 'Dún Laoghaire', 'Swords', 'Drogheda', 'Navan', 'Bray', 'Wicklow', 'Dundalk', 'Kilkenny'],
            'Cork' => ['Cork', 'Cobh', 'Macroom', 'Mallow', 'Fermoy', 'Clonakilty', 'Skibbereen', 'Bandon'],
            'Galway' => ['Galway', 'Ballinasloe', 'Tuam', 'Loughrea', 'Athenry', 'Oranmore', 'Clifden'],
            'Limerick' => ['Limerick', 'Ennis', 'Shannon', 'Nenagh', 'Tipperary', 'Rathkeale', 'Newcastle West'],
            'Kerry' => ['Tralee', 'Killarney', 'Listowel', 'Dingle', 'Kenmare', 'Cahersiveen', 'Waterville'],
            'Donegal' => ['Letterkenny', 'Buncrana', 'Donegal', 'Ballybofey', 'Milford', 'Dunfanaghy'],
            'Mayo' => ['Castlebar', 'Westport', 'Ballina', 'Claremorris', 'Swinford', 'Belmullet'],
            'Meath' => ['Navan', 'Trim', 'Dunshaughlin', 'Laytown', 'Duleek', 'Kells'],
            'Wicklow' => ['Bray', 'Wicklow', 'Greystones', 'Arklow', 'Enniskerry', 'Rathdrum'],
            'Kildare' => ['Naas', 'Newbridge', 'Kildare', 'Athgarvan', 'Maynooth', 'Leixlip', 'Celbridge'],
        ],
    ];

    public function up(): void
    {
        $states = DB::table('states')
            ->select('states.id', 'states.name as state_name', 'countries.name as country_name')
            ->join('countries', 'countries.id', '=', 'states.country_id')
            ->get();

        $now = now();
        $inserts = [];

        foreach ($states as $state) {
            $cityCount = DB::table('cities')->where('state_id', $state->id)->count();
            if ($cityCount > 0) {
                continue;
            }

            $cities = $this->getCitiesForState($state->country_name, $state->state_name);

            foreach ($cities as $city) {
                $city = trim($city);
                if ($city === '') {
                    continue;
                }
                $inserts[] = [
                    'state_id' => $state->id,
                    'name' => $city,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('cities')->insert($chunk);
        }
    }

    public function down(): void
    {
        DB::table('cities')->where('created_at', '>=', now()->subMinutes(5))->delete();
    }

    private function getCitiesForState(string $country, string $state): array
    {
        if (isset($this->knownCities[$country][$state])) {
            return $this->knownCities[$country][$state];
        }

        return [$state];
    }
};
