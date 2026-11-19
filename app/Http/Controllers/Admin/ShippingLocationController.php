<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ShippingLocationController extends Controller
{
    public function countries(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $this->statusFilter($request);

        $countries = Country::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderByDesc('status')
            ->orderBy('name')
            ->paginate(50)
            ->appends($request->query());

        return view('admin.settings.shipping.countries.index', compact('countries', 'search', 'status'));
    }

    public function updateCountryStatus(Request $request, Country $country)
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
            'search' => 'nullable|string|max:100',
            'status_filter' => 'nullable|in:active,inactive',
        ]);

        $country->status = (bool) $validated['status'];
        $country->saveOrFail();
        $country->refresh();
        $this->forgetCountryCaches($country);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $country->status]);
        }

        return redirect()->route('admin.settings.shipping-countries.index', array_filter([
            'search' => $validated['search'] ?? null,
            'status' => $validated['status_filter'] ?? null,
        ]))->with('success', 'Country status updated.');
    }

    public function states(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $countryId = (int) $request->query('country_id', 0);
        $status = $this->statusFilter($request);

        $states = State::query()
            ->with('country')
            ->withCount('cities')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($countryId > 0, fn ($query) => $query->where('country_id', $countryId))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderByDesc('status')
            ->orderBy('name')
            ->paginate(50)
            ->appends($request->query());

        $countries = Country::query()
            ->orderByDesc('status')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        return view('admin.settings.shipping.states.index', compact(
            'states',
            'countries',
            'search',
            'countryId',
            'status'
        ));
    }

    public function storeState(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('states', 'name')
                    ->where('country_id', $request->input('country_id'))
                    ->whereNull('deleted_at'),
            ],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->whereNull('deleted_at')],
            'status' => 'nullable|boolean',
        ]);

        $stateAttributes = [
            'name' => $validated['name'],
            'country_id' => $validated['country_id'],
            'status' => $request->boolean('status', true),
        ];
        $state = State::withTrashed()
            ->where('country_id', $validated['country_id'])
            ->where('name', $validated['name'])
            ->first();
        if ($state?->trashed()) {
            $state->restore();
            $state->update($stateAttributes);
        } else {
            $state = State::create($stateAttributes);
        }

        $this->forgetStateCaches($state);

        return redirect()->route('admin.settings.shipping-states.index')
            ->with('success', 'State created.');
    }

    public function editState(State $state)
    {
        $countries = Country::query()
            ->orderByDesc('status')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        return view('admin.settings.shipping.states.edit', compact('state', 'countries'));
    }

    public function updateState(Request $request, State $state)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('states', 'name')
                    ->where('country_id', $request->input('country_id'))
                    ->ignore($state->id),
            ],
            'country_id' => [
                'required',
                'integer',
                Rule::exists('countries', 'id')->whereNull('deleted_at'),
            ],
            'status' => 'nullable|boolean',
        ]);

        $oldState = $state->replicate();
        $oldState->setRelation('country', $state->country);

        $state->update([
            'name' => $validated['name'],
            'country_id' => $validated['country_id'],
            'status' => $request->boolean('status'),
        ]);

        $this->forgetStateCaches($oldState);
        $this->forgetStateCaches($state->fresh('country'));

        return redirect()->route('admin.settings.shipping-states.index')
            ->with('success', 'State updated.');
    }

    public function destroyState(State $state)
    {
        if ($state->cities()->exists()) {
            return back()->with('error', 'Delete the cities under this state before deleting the state.');
        }

        $this->forgetStateCaches($state);
        $state->delete();

        return redirect()->route('admin.settings.shipping-states.index')
            ->with('success', 'State deleted.');
    }

    public function updateStateStatus(Request $request, State $state)
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        $status = (bool) $validated['status'];
        $state->update(['status' => $status]);

        if ($status) {
            $state->cities()->update(['status' => true]);
        }

        $this->forgetStateCaches($state->fresh('country'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $state->status]);
        }

        return back()->with('success', 'State status updated.');
    }

    public function cities(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $stateId = (int) $request->query('state_id', 0);
        $countryId = (int) $request->query('country_id', 0);
        $status = $this->statusFilter($request);

        $cities = City::query()
            ->with('state.country')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($stateId > 0, fn ($query) => $query->where('state_id', $stateId))
            ->when($countryId > 0, fn ($query) => $query->where('country_id', $countryId))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderByDesc('status')
            ->orderBy('name')
            ->paginate(50)
            ->appends($request->query());

        $countries = Country::query()
            ->orderByDesc('status')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        $states = State::query()
            ->with('country:id,name')
            ->orderBy('name')
            ->get(['id', 'country_id', 'name']);

        return view('admin.settings.shipping.cities.index', compact(
            'cities',
            'countries',
            'states',
            'search',
            'stateId',
            'countryId',
            'status'
        ));
    }

    public function storeCity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'cost' => 'nullable|numeric|min:0|max:99999999.99',
            'status' => 'nullable|boolean',
        ]);

        $state = State::findOrFail($validated['state_id']);
        $city = City::create([
            'name' => $validated['name'],
            'state_id' => $state->id,
            'country_id' => $state->country_id,
            'cost' => $validated['cost'] ?? null,
            'status' => $request->boolean('status', true),
        ]);

        $this->forgetCityCaches($city->fresh('state.country'));

        return redirect()->route('admin.settings.shipping-cities.index')
            ->with('success', 'City created.');
    }

    public function editCity(City $city)
    {
        $countries = Country::query()
            ->orderByDesc('status')
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        $states = State::query()
            ->with('country:id,name')
            ->orderBy('name')
            ->get(['id', 'country_id', 'name']);

        return view('admin.settings.shipping.cities.edit', compact('city', 'countries', 'states'));
    }

    public function updateCity(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'state_id' => ['required', 'integer', Rule::exists('states', 'id')->whereNull('deleted_at')],
            'cost' => 'nullable|numeric|min:0|max:99999999.99',
            'status' => 'nullable|boolean',
        ]);

        $oldCity = $city->replicate();
        $oldCity->setRelation('state', $city->state);

        $state = State::findOrFail($validated['state_id']);
        $city->update([
            'name' => $validated['name'],
            'state_id' => $state->id,
            'country_id' => $state->country_id,
            'cost' => $validated['cost'] ?? null,
            'status' => $request->boolean('status'),
        ]);

        $this->forgetCityCaches($city->fresh('state.country'));
        $this->forgetCityCaches($oldCity);

        return redirect()->route('admin.settings.shipping-cities.index')
            ->with('success', 'City updated.');
    }

    public function destroyCity(City $city)
    {
        $this->forgetCityCaches($city->fresh('state.country'));
        $city->delete();

        return redirect()->route('admin.settings.shipping-cities.index')
            ->with('success', 'City deleted.');
    }

    public function updateCityStatus(Request $request, City $city)
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        $city->update(['status' => (bool) $validated['status']]);
        $this->forgetCityCaches($city->fresh('state.country'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $city->status]);
        }

        return back()->with('success', 'City status updated.');
    }

    private function statusFilter(Request $request): ?bool
    {
        return match ($request->query('status')) {
            'active' => true,
            'inactive' => false,
            default => null,
        };
    }

    private function forgetCountryCaches(Country $country): void
    {
        Cache::forget('address_countries_v2');

        $countryName = mb_strtolower($country->name);
        Cache::forget('address_states_v2_'.$countryName);

        $country->states()->pluck('name')->each(function (string $stateName) use ($countryName): void {
            Cache::forget('address_cities_v2_'.$countryName.'_'.mb_strtolower($stateName));
        });
    }

    private function forgetStateCaches(State $state): void
    {
        $state->loadMissing('country');

        $countryName = mb_strtolower((string) $state->country?->name);
        if ($countryName === '') {
            return;
        }

        Cache::forget('address_states_v2_'.$countryName);
        Cache::forget('address_cities_v2_'.$countryName.'_'.mb_strtolower($state->name));
    }

    private function forgetCityCaches(City $city): void
    {
        $city->loadMissing('state.country');

        $stateName = mb_strtolower((string) $city->state?->name);
        $countryName = mb_strtolower((string) $city->state?->country?->name);

        if ($stateName !== '' && $countryName !== '') {
            Cache::forget('address_cities_v2_'.$countryName.'_'.$stateName);
        }
    }
}
