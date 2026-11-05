@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-city-edit-page', 'pageClass' => 'admin-shipping-city-edit-page'])
@section('page-title', 'Edit Shipping City')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Edit Shipping City'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 h6">Edit City</h5>
                    <a href="{{ route('admin.settings.shipping-cities.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <form action="{{ route('admin.settings.shipping-cities.update', $city) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="city-name" class="form-label">Name</label>
                            <input id="city-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $city->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="city-state" class="form-label">State</label>
                            <select id="city-state" name="state_id" class="form-select @error('state_id') is-invalid @enderror" required>
                                @foreach ($states as $state)
                                    <option value="{{ $state->id }}" @selected((int) old('state_id', $city->state_id) === $state->id)>{{ $state->name }}{{ $state->country ? ', '.$state->country->name : '' }}</option>
                                @endforeach
                            </select>
                            @error('state_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="city-cost" class="form-label">Area wise shipping cost</label>
                            <input id="city-cost" type="number" min="0" step="0.01" name="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost', $city->cost) }}" placeholder="e.g. 25.00">
                            <small class="text-muted">Leave blank to use the matching shipping zone cost.</small>
                            @error('cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <input type="hidden" name="status" value="0">
                            <div class="form-check">
                                <input id="city-status" type="checkbox" name="status" value="1" class="form-check-input" @checked(old('status', $city->status))>
                                <label class="form-check-label" for="city-status">Active</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
