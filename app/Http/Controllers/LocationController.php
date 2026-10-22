<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    public function states(Request $request)
    {
        $countryName = trim((string) $request->input('country'));
        if ($countryName === '') {
            return response()->json(['states' => []]);
        }

        $states = Cache::rememberForever('address_states_'.mb_strtolower($countryName), function () use ($countryName) {
            return State::query()
                ->whereHas('country', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower($countryName)]))
                ->orderBy('name')
                ->pluck('name')
                ->all();
        });

        return response()->json(['states' => $states]);
    }

    public function cities(Request $request)
    {
        $countryName = trim((string) $request->input('country'));
        $stateName = trim((string) $request->input('state'));
        if ($countryName === '' || $stateName === '') {
            return response()->json(['cities' => []]);
        }

        $cities = Cache::rememberForever(
            'address_cities_'.mb_strtolower($countryName).'_'.mb_strtolower($stateName),
            function () use ($countryName, $stateName) {
                return City::query()
                    ->whereHas('state', function ($q) use ($countryName, $stateName) {
                        $q->whereRaw('LOWER(name) = ?', [mb_strtolower($stateName)])
                            ->whereHas('country', fn ($cq) => $cq->whereRaw('LOWER(name) = ?', [mb_strtolower($countryName)]));
                    })
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();
            }
        );

        return response()->json(['cities' => $cities]);
    }
}
