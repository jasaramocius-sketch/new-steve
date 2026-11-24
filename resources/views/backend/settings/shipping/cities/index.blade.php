@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-cities-page', 'pageClass' => 'admin-shipping-cities-page'])
@section('page-title', 'Shipping Cities')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Shipping Cities'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="row">
        <div class="col-lg-9 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <form action="{{ route('admin.settings.shipping-cities.index') }}" method="GET">
                        <div class="d-flex flex-wrap flex-row flex-wrap align-items-end gap-2">
                            <div class="w-auto">
                                <label class="form-label fs-12 text-muted" for="city-search">City name</label>
                                @include('backend.partials.search-form', [
                                    'embedded' => true,
                                    'placeholder' => 'Type city name',
                                    'inputId' => 'city-search',
                                ])
                            </div>
                            <div class="w-auto">
                                <label class="form-label fs-12 text-muted" for="city-country">Country</label>
                                <select id="city-country" name="country_id" class="form-select">
                                    <option value="">All countries</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" @selected($countryId === $country->id)>{{ $country->name }}{{ $country->status ? '' : ' (inactive)' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-auto">
                                <label class="form-label fs-12 text-muted" for="city-state">State</label>
                                <select id="city-state" name="state_id" class="form-select">
                                    <option value="">All states</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}" @selected($stateId === $state->id)>{{ $state->name }}{{ $state->country ? ', '.$state->country->name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                                
                            <div class="w-auto">
                                    <label class="form-label fs-12 text-muted" for="city-status">Status</label>
                                    <select id="city-status" name="status" class="form-select">
                                        <option value="">All</option>
                                        <option value="active" @selected($status === true)>Active</option>
                                        <option value="inactive" @selected($status === false)>Inactive</option>
                                    </select>
                            </div>
                            <div class="w-auto">
                                <button type="submit" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-filter"></i>Filter</button>
                            </div>
                            @if ($countryId > 0 || $stateId > 0 || $status !== null)
                                <div class="w-auto">
                                    <a href="{{ route('admin.settings.shipping-cities.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Clear all filters">
                                        <i class="fas fa-times"></i>Clear
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="8%">#</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Country</th>
                                <th>Area Cost</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cities as $city)
                                <tr>
                                    <td class="admin-row-number">{{ $cities->firstItem() + $loop->index }}</td>
                                    <td>{{ $city->name }}</td>
                                    <td>{{ $city->state?->name ?? '—' }}</td>
                                    <td>{{ $city->state?->country?->name ?? '—' }}</td>
                                    <td>{{ $city->cost !== null ? currency_format($city->cost) : '—' }}</td>
                                    <td>
                                        <form action="{{ route('admin.settings.shipping-cities.status', $city) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $city->status ? 0 : 1 }}">
                                            <button type="submit" class="badge btn-sm {{ $city->status ? 'bg-success' : 'btn-outline-secondary' }}">
                                                {{ $city->status ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="pe-3 table-action-col">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.settings.shipping-cities.edit', $city) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit {{ $city->name }}" aria-label="Edit {{ $city->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            <form action="{{ route('admin.settings.shipping-cities.destroy', $city) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this city?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete {{ $city->name }}" aria-label="Delete {{ $city->name }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No cities found for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-3">{{ $cities->links() }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">Add New City</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.shipping-cities.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="new-city-name" class="form-label mt-0">Name</label>
                            <input id="new-city-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="City name" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="new-city-state form-group">
                            <label for="new-city-state-label" class="form-label">State</label>
                            <select id="new-city-state-select" name="state_id" class="form-select @error('state_id') is-invalid @enderror" required>
                                <option value="">Select state</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}" @selected((int) old('state_id') === $state->id)>{{ $state->name }}{{ $state->country ? ', '.$state->country->name : '' }}</option>
                                @endforeach
                            </select>
                            @error('state_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="new-city-cost" class="form-label">Area wise shipping cost</label>
                            <input id="new-city-cost" type="number" min="0" step="0.01" name="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost') }}" placeholder="e.g. 25.00">
                            <small class="text-muted">Leave blank to use the matching shipping zone cost.</small>
                            @error('cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-save"></i>Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
