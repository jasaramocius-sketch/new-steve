@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-zone-form-page', 'pageClass' => 'admin-shipping-zone-form-page'])
@section('page-title', $isEditing ? 'Edit Shipping Zone' : 'Add Shipping Zone')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => $isEditing ? 'Edit Shipping Zone' : 'Add Shipping Zone'])
@endsection
@section('content')
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
                    <p class="text-muted">Use the same country, state, and city names customers enter at checkout. Set a country to <code>*</code> to match all countries. Leave state or city blank to match any value.</p>
                    <div class="form-group">
                        <label for="zone-country">Country</label>
                        <input id="zone-country" name="country" class="form-control" value="{{ old('country', $zone->country ?? '*') }}" maxlength="150" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="zone-state">State (optional)</label>
                            <input id="zone-state" name="state" class="form-control" value="{{ old('state', $zone->state) }}" maxlength="150">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="zone-city">City (optional)</label>
                            <input id="zone-city" name="city" class="form-control" value="{{ old('city', $zone->city) }}" maxlength="150">
                        </div>
                    </div>
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
                    <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update Zone' : 'Save Zone' }}</button>
                    <a href="{{ route('admin.settings.shipping-zones.index') }}" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
