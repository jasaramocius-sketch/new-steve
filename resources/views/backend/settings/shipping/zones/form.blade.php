@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-zone-form-page', 'pageClass' => 'admin-shipping-zone-form-page'])
@section('page-title', $isEditing ? 'Edit Shipping Zone' : 'Add Shipping Zone')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => $isEditing ? 'Edit Shipping Zone' : 'Add Shipping Zone'])
@endsection
@section('content')
@php
    $selectedCountry = old('country', $zone->country ?? '*');
    $selectedState = old('state', $zone->state ?? '');
    $selectedCity = old('city', $zone->city ?? '');
@endphp
<div class="container-fluid px-15px px-lg-25px">
    <!-- <div class="ap-page-header mb-3"><div class="ap-page-header-row"><h1 class="ap-page-title">{{ $isEditing ? 'Edit Shipping Zone' : 'Add Shipping Zone' }}</h1></div></div> -->
    <div class="col-lg-8 px-0">
        <div class="card">
            <form action="{{ $isEditing ? route('admin.settings.shipping-zones.update', $zone) : route('admin.settings.shipping-zones.store') }}" method="POST">
                @csrf
                @if ($isEditing) @method('PUT') @endif
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <div class="form-group">
                        <label for="zone-name">Zone name</label>
                        <input id="zone-name" name="name" class="form-control" value="{{ old('name', $zone->name) }}" maxlength="150" required>
                    </div>
                    <p class="text-muted">Choose a country, then optionally narrow the zone by state and city. Set the country to <code>*</code> to match all countries; leave state or city blank to match any value.</p>
                    <div class="form-group">
                        <label for="zone-country">Country</label>
                        <select id="zone-country" name="country" class="form-control form-select" data-searchable="off" required>
                            <option value="*" @selected($selectedCountry === '*')>All countries (*)</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->name }}" @selected($selectedCountry === $country->name)>{{ $country->name }}{{ $country->status ? '' : ' (inactive)' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row d-flex gap-1 flex-wrap">
                        <div class="form-group w-auto w-md-50">
                            <label for="zone-state">State (optional)</label>
                            <select id="zone-state" class="form-control form-select" data-searchable="off" data-selected="{{ $selectedState }}" disabled>
                                <option value="">Any state</option>
                                @if ($selectedState !== '')
                                    <option value="{{ $selectedState }}" selected>{{ $selectedState }}</option>
                                @endif
                            </select>
                            <input type="hidden" id="zone-state-value" name="state" value="{{ $selectedState }}">
                        </div>
                        <div class="form-group w-auto w-md-50">
                            <label for="zone-city">City (optional)</label>
                            <select id="zone-city" class="form-control form-select" data-searchable="off" data-selected="{{ $selectedCity }}" disabled>
                                <option value="">Any city</option>
                                @if ($selectedCity !== '')
                                    <option value="{{ $selectedCity }}" selected>{{ $selectedCity }}</option>
                                @endif
                            </select>
                            <input type="hidden" id="zone-city-value" name="city" value="{{ $selectedCity }}">
                        </div>
                    </div>
                    <small id="zone-location-error" class="form-text text-danger" role="status" aria-live="polite"></small>
                    <div class="form-group">
                        <label for="zone-priority">Priority</label>
                        <input id="zone-priority" type="number" min="0" max="65535" name="priority" class="form-control" value="{{ old('priority', $zone->priority ?? 0) }}">
                        <small class="form-text text-muted">More specific zones are selected first. Priority breaks ties between equally specific matching zones.</small>
                    </div>
                    <div class="form-group">
                        <label for="zone-cost">Area wise shipping cost</label>
                        <input id="zone-cost" type="number" min="0" step="0.01" name="cost" class="form-control" value="{{ old('cost', $zone->cost ?? 0) }}">
                        <small class="form-text text-muted">Used when Area Wise Flat Shipping is selected on the Shipping Configuration page.</small>
                    </div>
                    <h5 class="mt-4">Carrier rates</h5>
                    @forelse ($carriers as $carrier)
                        @php($currentRate = $zone->rates->firstWhere('shipping_carrier_id', $carrier->id)?->fee)
                        <div class="form-group">
                            <label for="rate-{{ $carrier->id }}">{{ $carrier->name }}{{ $carrier->is_active ? '' : ' (inactive)' }}</label>
                            <div class="input-group">
                                <input id="rate-{{ $carrier->id }}" type="number" name="rates[{{ $carrier->id }}]" min="0" max="99999999.99" step="0.01" class="form-control" value="{{ old('rates.'.$carrier->id, $currentRate) }}" placeholder="Leave blank to remove this carrier's rate">
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning">Create a carrier before adding rates to this zone.</div>
                    @endforelse
                    <div class="custom-control custom-checkbox mb-3">
                        <input id="zone-active" type="checkbox" name="is_active" value="1" class="custom-control-input" @checked(old('is_active', $isEditing ? $zone->is_active : true))>
                        <label class="custom-control-label" for="zone-active">Active and available at checkout</label>
                    </div>
                    <div class="form-group d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-save"></i>{{ $isEditing ? 'Update Zone' : 'Save Zone' }}</button>
                        <a href="{{ route('admin.settings.shipping-zones.index') }}" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-times"></i>Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const country = document.getElementById('zone-country');
    const state = document.getElementById('zone-state');
    const city = document.getElementById('zone-city');
    const stateValue = document.getElementById('zone-state-value');
    const cityValue = document.getElementById('zone-city-value');
    const error = document.getElementById('zone-location-error');
    const statesUrl = @json(route('location.states'));
    const citiesUrl = @json(route('location.cities'));
    let stateRequest = 0;
    let cityRequest = 0;

    function setOptions(select, values, placeholder, selectedValue, enabled) {
        select.replaceChildren(new Option(placeholder, ''));
        if (selectedValue && !values.includes(selectedValue)) {
            values = [selectedValue].concat(values);
        }
        values.forEach(function (value) {
            const option = new Option(value, value);
            option.selected = value === selectedValue;
            select.add(option);
        });
        select.disabled = !enabled;
    }

    async function fetchLocations(url, key) {
        const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!response.ok) {
            throw new Error('Location request failed with status ' + response.status);
        }
        const data = await response.json();
        if (!Array.isArray(data[key])) {
            throw new Error('Location response did not contain ' + key);
        }
        return data[key];
    }

    async function loadCities(selectedCity) {
        const requestId = ++cityRequest;
        const selectedCountry = country.value;
        const selectedState = state.value;
        if (!selectedCountry || selectedCountry === '*' || !selectedState) {
            setOptions(city, [], 'Any city', selectedCity || '', true);
            cityValue.value = city.value;
            return;
        }

        setOptions(city, [], 'Loading cities…', '', false);
        try {
            const url = citiesUrl + '?country=' + encodeURIComponent(selectedCountry) + '&state=' + encodeURIComponent(selectedState);
            const cities = await fetchLocations(url, 'cities');
            if (requestId !== cityRequest || country.value !== selectedCountry || state.value !== selectedState) return;
            setOptions(city, cities, 'Any city', selectedCity || '', true);
            cityValue.value = city.value;
            error.textContent = '';
        } catch (exception) {
            if (requestId !== cityRequest) return;
            console.error(exception);
            setOptions(city, [], 'Could not load cities', selectedCity || '', true);
            cityValue.value = city.value;
            error.textContent = 'Could not load locations. Refresh the page and try again.';
        }
    }

    async function loadStates(selectedState, selectedCity) {
        const requestId = ++stateRequest;
        cityRequest++;
        const selectedCountry = country.value;
        if (!selectedCountry || selectedCountry === '*') {
            setOptions(state, [], 'Any state', selectedState || '', true);
            setOptions(city, [], 'Any city', selectedCity || '', true);
            stateValue.value = state.value;
            cityValue.value = city.value;
            return;
        }

        setOptions(state, [], 'Loading states…', '', false);
        setOptions(city, [], 'Choose a state first', '', false);
        try {
            const url = statesUrl + '?country=' + encodeURIComponent(selectedCountry);
            const states = await fetchLocations(url, 'states');
            if (requestId !== stateRequest || country.value !== selectedCountry) return;
            setOptions(state, states, 'Any state', selectedState || '', true);
            stateValue.value = state.value;
            error.textContent = '';
            if (selectedState && state.value === selectedState) {
                await loadCities(selectedCity);
            } else {
                setOptions(city, [], 'Any city', '', true);
                cityValue.value = '';
            }
        } catch (exception) {
            if (requestId !== stateRequest) return;
            console.error(exception);
            setOptions(state, [], 'Could not load states', selectedState || '', true);
            setOptions(city, [], 'Any city', selectedCity || '', true);
            stateValue.value = state.value;
            cityValue.value = city.value;
            error.textContent = 'Could not load locations. Refresh the page and try again.';
        }
    }

    country.addEventListener('change', function () {
        state.dataset.selected = '';
        city.dataset.selected = '';
        stateValue.value = '';
        cityValue.value = '';
        error.textContent = '';
        loadStates('', '');
    });

    state.addEventListener('change', function () {
        state.dataset.selected = state.value;
        stateValue.value = state.value;
        city.dataset.selected = '';
        cityValue.value = '';
        error.textContent = '';
        loadCities('');
    });

    city.addEventListener('change', function () {
        city.dataset.selected = city.value;
        cityValue.value = city.value;
    });

    loadStates(state.dataset.selected, city.dataset.selected);
});
</script>
@endpush
