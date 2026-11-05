@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-state-edit-page', 'pageClass' => 'admin-shipping-state-edit-page'])
@section('page-title', 'Edit Shipping State')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Edit Shipping State'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 h6">Edit State</h5>
                    <a href="{{ route('admin.settings.shipping-states.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
                </div>
                <div class="card-body">
                    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
                    <form action="{{ route('admin.settings.shipping-states.update', $state) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="state-name" class="form-label">Name</label>
                            <input id="state-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $state->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="state-country" class="form-label">Country</label>
                            <select id="state-country" name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" @selected((int) old('country_id', $state->country_id) === $country->id)>{{ $country->name }}{{ $country->status ? '' : ' (inactive)' }}</option>
                                @endforeach
                            </select>
                            @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <input type="hidden" name="status" value="0">
                            <div class="form-check">
                                <input id="state-status" type="checkbox" name="status" value="1" class="form-check-input" @checked(old('status', $state->status))>
                                <label class="form-check-label" for="state-status">Active</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
